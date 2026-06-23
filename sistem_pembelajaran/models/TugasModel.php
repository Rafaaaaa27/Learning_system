<?php
/**
 * Tugas Model
 * Menangani operasi CRUD untuk tabel tugas dan jawaban_tugas
 */

require_once 'Model.php';

class TugasModel extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'tugas';
    }
    
    /**
     * Get tugas dengan informasi kelas
     */
    public function getTugasWithKelas($id) {
        $sql = "SELECT t.*, k.nama_kelas, u.nama as nama_guru
                FROM tugas t
                LEFT JOIN kelas k ON t.id_kelas = k.id
                LEFT JOIN users u ON k.id_guru = u.id
                WHERE t.id = :id";
        
        $result = $this->query($sql, [':id' => $id]);
        return $result[0] ?? null;
    }
    
    /**
     * Get semua tugas by kelas
     */
    public function getTugasByKelas($kelasId, $includeExpired = true) {
        $sql = "SELECT t.*, 
                (SELECT COUNT(*) FROM jawaban_tugas WHERE id_tugas = t.id) as total_jawaban
                FROM tugas t
                WHERE t.id_kelas = :kelasId";
        
        if (!$includeExpired) {
            $sql .= " AND t.deadline >= NOW()";
        }
        
        $sql .= " ORDER BY t.deadline ASC";
        
        return $this->query($sql, [':kelasId' => $kelasId]);
    }
    
    /**
     * Get tugas untuk siswa (dengan status pengerjaan)
     */
    public function getTugasForSiswa($siswaId, $kelasId = null) {
        $sql = "SELECT t.*, k.nama_kelas,
                jt.id as jawaban_id,
                jt.jawaban,
                jt.nilai,
                jt.submitted_at,
                CASE 
                    WHEN jt.id IS NULL THEN 'belum'
                    WHEN jt.nilai IS NULL THEN 'submitted'
                    ELSE 'dinilai'
                END as status
                FROM tugas t
                JOIN kelas k ON t.id_kelas = k.id
                JOIN siswa_kelas sk ON k.id = sk.id_kelas
                LEFT JOIN jawaban_tugas jt ON t.id = jt.id_tugas AND jt.id_siswa = :siswaId
                WHERE sk.id_siswa = :siswaId";
        
        if ($kelasId) {
            $sql .= " AND t.id_kelas = :kelasId";
        }
        
        $sql .= " ORDER BY t.deadline ASC";
        
        $params = [':siswaId' => $siswaId];
        if ($kelasId) {
            $params[':kelasId'] = $kelasId;
        }
        
        return $this->query($sql, $params);
    }
    
    /**
     * Submit jawaban tugas
     */
    public function submitJawaban($data) {
        // Check if already submitted
        $existing = $this->query(
            "SELECT id FROM jawaban_tugas WHERE id_tugas = :tugasId AND id_siswa = :siswaId",
            [':tugasId' => $data['id_tugas'], ':siswaId' => $data['id_siswa']]
        );
        
        if (!empty($existing)) {
            // Update existing answer
            return $this->updateJawaban($existing[0]['id'], $data);
        }
        
        // Insert new answer
        $sql = "INSERT INTO jawaban_tugas (id_tugas, id_siswa, jawaban, file_jawaban) 
                VALUES (:id_tugas, :id_siswa, :jawaban, :file_jawaban)";
        
        return $this->execute($sql, [
            ':id_tugas' => $data['id_tugas'],
            ':id_siswa' => $data['id_siswa'],
            ':jawaban' => $data['jawaban'],
            ':file_jawaban' => $data['file_jawaban'] ?? null
        ]);
    }
    
    /**
     * Update jawaban tugas
     */
    public function updateJawaban($jawabanId, $data) {
        $sql = "UPDATE jawaban_tugas SET jawaban = :jawaban";
        
        $params = [':jawaban' => $data['jawaban']];
        
        if (isset($data['file_jawaban'])) {
            $sql .= ", file_jawaban = :file_jawaban";
            $params[':file_jawaban'] = $data['file_jawaban'];
        }
        
        $sql .= " WHERE id = :id";
        $params[':id'] = $jawabanId;
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Nilai jawaban tugas
     */
    public function nilaiJawaban($jawabanId, $nilai, $feedback = null) {
        $sql = "UPDATE jawaban_tugas SET nilai = :nilai, feedback = :feedback WHERE id = :id";
        
        return $this->execute($sql, [
            ':nilai' => $nilai,
            ':feedback' => $feedback,
            ':id' => $jawabanId
        ]);
    }
    
    /**
     * Get semua jawaban untuk tugas tertentu
     */
    public function getJawabanByTugas($tugasId) {
        $sql = "SELECT jt.*, u.nama, u.email, u.foto
                FROM jawaban_tugas jt
                JOIN users u ON jt.id_siswa = u.id
                WHERE jt.id_tugas = :tugasId
                ORDER BY jt.submitted_at DESC";
        
        return $this->query($sql, [':tugasId' => $tugasId]);
    }
    
    /**
     * Get jawaban siswa untuk tugas tertentu
     */
    public function getJawabanSiswa($tugasId, $siswaId) {
        $sql = "SELECT jt.* FROM jawaban_tugas jt
                WHERE jt.id_tugas = :tugasId AND jt.id_siswa = :siswaId";
        
        $result = $this->query($sql, [
            ':tugasId' => $tugasId,
            ':siswaId' => $siswaId
        ]);
        
        return $result[0] ?? null;
    }
    
    /**
     * Delete tugas dan semua jawaban terkait
     */
    public function deleteTugas($id) {
        // Get file paths untuk delete
        $tugas = $this->getById($id);
        $jawaban = $this->getJawabanByTugas($id);
        
        // Delete physical files
        if ($tugas && $tugas['file_lampiran']) {
            deleteFile('tugas/' . $tugas['file_lampiran']);
        }
        
        foreach ($jawaban as $j) {
            if ($j['file_jawaban']) {
                deleteFile('jawaban/' . $j['file_jawaban']);
            }
        }
        
        // Delete from database (cascade akan handle jawaban)
        return $this->delete($id);
    }
    
    /**
     * Get statistik tugas
     */
    public function getStatistikTugas($tugasId) {
        $sql = "SELECT 
                COUNT(*) as total_siswa,
                COUNT(jt.id) as total_submit,
                COUNT(CASE WHEN jt.nilai IS NOT NULL THEN 1 END) as total_dinilai,
                AVG(jt.nilai) as rata_rata,
                MAX(jt.nilai) as nilai_tertinggi,
                MIN(jt.nilai) as nilai_terendah
                FROM (
                    SELECT sk.id_siswa 
                    FROM siswa_kelas sk
                    WHERE sk.id_kelas = (SELECT id_kelas FROM tugas WHERE id = :tugasId)
                ) siswa
                LEFT JOIN jawaban_tugas jt ON siswa.id_siswa = jt.id_siswa AND jt.id_tugas = :tugasId";
        
        $result = $this->query($sql, [':tugasId' => $tugasId]);
        return $result[0] ?? null;
    }
    
    /**
     * Check if tugas is expired
     */
    public function isExpired($tugasId) {
        $tugas = $this->getById($tugasId);
        
        if (!$tugas || !$tugas['deadline']) {
            return false;
        }
        
        return strtotime($tugas['deadline']) < time();
    }
    
    /**
     * Get upcoming deadlines untuk siswa
     */
    public function getUpcomingDeadlines($siswaId, $days = 7) {
        $sql = "SELECT t.*, k.nama_kelas
                FROM tugas t
                JOIN kelas k ON t.id_kelas = k.id
                JOIN siswa_kelas sk ON k.id = sk.id_kelas
                LEFT JOIN jawaban_tugas jt ON t.id = jt.id_tugas AND jt.id_siswa = :siswaId
                WHERE sk.id_siswa = :siswaId
                AND t.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY)
                AND jt.id IS NULL
                ORDER BY t.deadline ASC";
        
        return $this->query($sql, [
            ':siswaId' => $siswaId,
            ':days' => $days
        ]);
    }
    
    /**
     * Auto-grade multiple choice
     */
    public function autoGradeMultipleChoice($jawabanId) {
        $jawaban = $this->query(
            "SELECT jt.*, t.opsi_jawaban FROM jawaban_tugas jt
             JOIN tugas t ON jt.id_tugas = t.id
             WHERE jt.id = :id",
            [':id' => $jawabanId]
        );
        
        if (empty($jawaban) || !$jawaban[0]['opsi_jawaban']) {
            return false;
        }
        
        $data = $jawaban[0];
        $opsiJawaban = json_decode($data['opsi_jawaban'], true);
        $jawabanSiswa = json_decode($data['jawaban'], true);
        
        $benar = 0;
        $total = count($opsiJawaban);
        
        foreach ($opsiJawaban as $index => $soal) {
            if (isset($jawabanSiswa[$index]) && $jawabanSiswa[$index] == $soal['jawaban_benar']) {
                $benar++;
            }
        }
        
        $nilai = ($benar / $total) * 100;
        
        return $this->nilaiJawaban($jawabanId, $nilai, "Auto-graded: {$benar}/{$total} benar");
    }
}
?>