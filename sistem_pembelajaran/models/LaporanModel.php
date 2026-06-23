<?php
/**
 * Laporan Model
 * Menangani generate dan manage laporan progress siswa
 */

require_once 'Model.php';

class LaporanModel extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'laporan';
    }
    
    /**
     * Generate laporan lengkap untuk siswa
     */
    public function generateLaporan($siswaId, $kelasId, $periode = 'semester') {
        // Get tugas data
        $tugasData = $this->getTugasData($siswaId, $kelasId);
        
        // Get gamifikasi data
        $gamifikasiData = $this->getGamifikasiData($siswaId);
        
        // Get forum activity
        $forumData = $this->getForumActivity($siswaId, $kelasId);
        
        // Calculate statistics
        $stats = $this->calculateStatistics($tugasData);
        
        // Compile data
        $data = [
            'periode' => $periode,
            'tanggal_generate' => date('Y-m-d H:i:s'),
            'tugas' => $tugasData,
            'statistik' => $stats,
            'gamifikasi' => $gamifikasiData,
            'forum' => $forumData,
            'chart_data' => $this->prepareChartData($tugasData)
        ];
        
        // Save to database
        $existing = $this->query(
            "SELECT id FROM laporan 
             WHERE id_siswa = :siswaId AND id_kelas = :kelasId AND periode = :periode",
            [':siswaId' => $siswaId, ':kelasId' => $kelasId, ':periode' => $periode]
        );
        
        if (!empty($existing)) {
            $this->update($existing[0]['id'], ['data_json' => json_encode($data)]);
            $laporanId = $existing[0]['id'];
        } else {
            $laporanId = $this->create([
                'id_siswa' => $siswaId,
                'id_kelas' => $kelasId,
                'data_json' => json_encode($data),
                'periode' => $periode
            ]);
        }
        
        return ['success' => true, 'data' => $data, 'id' => $laporanId];
    }
    
    /**
     * Get tugas data untuk siswa
     */
    private function getTugasData($siswaId, $kelasId) {
        $sql = "SELECT t.judul, t.tipe, t.deadline, 
                jt.nilai, jt.submitted_at, jt.feedback,
                CASE 
                    WHEN jt.id IS NULL THEN 'Belum Dikerjakan'
                    WHEN jt.nilai IS NULL THEN 'Menunggu Penilaian'
                    ELSE 'Selesai'
                END as status
                FROM tugas t
                LEFT JOIN jawaban_tugas jt ON t.id = jt.id_tugas AND jt.id_siswa = :siswaId
                WHERE t.id_kelas = :kelasId
                ORDER BY t.created_at DESC";
        
        return $this->query($sql, [':siswaId' => $siswaId, ':kelasId' => $kelasId]);
    }
    
    /**
     * Get gamifikasi data
     */
    private function getGamifikasiData($siswaId) {
        $sql = "SELECT g.poin, g.badges FROM gamifikasi g WHERE g.id_user = :siswaId";
        $result = $this->query($sql, [':siswaId' => $siswaId]);
        
        if (!empty($result)) {
            $data = $result[0];
            $data['badges'] = json_decode($data['badges'], true);
            return $data;
        }
        
        return ['poin' => 0, 'badges' => []];
    }
    
    /**
     * Get forum activity
     */
    private function getForumActivity($siswaId, $kelasId) {
        $sql = "SELECT 
                COUNT(CASE WHEN parent_id IS NULL THEN 1 END) as total_posts,
                COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as total_replies,
                SUM(likes) as total_likes
                FROM forum_posts
                WHERE id_user = :siswaId AND id_kelas = :kelasId";
        
        $result = $this->query($sql, [':siswaId' => $siswaId, ':kelasId' => $kelasId]);
        return $result[0] ?? ['total_posts' => 0, 'total_replies' => 0, 'total_likes' => 0];
    }
    
    /**
     * Calculate statistics
     */
    private function calculateStatistics($tugasData) {
        $nilaiList = array_filter(array_column($tugasData, 'nilai'), function($n) {
            return $n !== null;
        });
        
        $totalTugas = count($tugasData);
        $tugasSelesai = count($nilaiList);
        $rataRata = !empty($nilaiList) ? round(array_sum($nilaiList) / count($nilaiList), 2) : 0;
        $nilaiTertinggi = !empty($nilaiList) ? max($nilaiList) : 0;
        $nilaiTerendah = !empty($nilaiList) ? min($nilaiList) : 0;
        
        return [
            'total_tugas' => $totalTugas,
            'tugas_selesai' => $tugasSelesai,
            'tugas_pending' => $totalTugas - $tugasSelesai,
            'persentase_selesai' => $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 2) : 0,
            'rata_rata' => $rataRata,
            'nilai_tertinggi' => $nilaiTertinggi,
            'nilai_terendah' => $nilaiTerendah,
            'grade' => $this->calculateGrade($rataRata)
        ];
    }
    
    /**
     * Calculate grade letter
     */
    private function calculateGrade($nilai) {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        if ($nilai >= 60) return 'D';
        return 'E';
    }
    
    /**
     * Prepare chart data
     */
    private function prepareChartData($tugasData) {
        $labels = [];
        $values = [];
        
        foreach ($tugasData as $tugas) {
            if ($tugas['nilai'] !== null) {
                $labels[] = substr($tugas['judul'], 0, 20) . '...';
                $values[] = $tugas['nilai'];
            }
        }
        
        return [
            'labels' => array_reverse($labels),
            'values' => array_reverse($values)
        ];
    }
    
    /**
     * Get laporan by siswa
     */
    public function getLaporanBySiswa($siswaId, $kelasId = null, $periode = null) {
        $sql = "SELECT l.*, k.nama_kelas, u.nama
                FROM laporan l
                JOIN kelas k ON l.id_kelas = k.id
                JOIN users u ON l.id_siswa = u.id
                WHERE l.id_siswa = :siswaId";
        
        $params = [':siswaId' => $siswaId];
        
        if ($kelasId) {
            $sql .= " AND l.id_kelas = :kelasId";
            $params[':kelasId'] = $kelasId;
        }
        
        if ($periode) {
            $sql .= " AND l.periode = :periode";
            $params[':periode'] = $periode;
        }
        
        $sql .= " ORDER BY l.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get laporan detail
     */
    public function getLaporanDetail($id) {
        $laporan = $this->getById($id);
        
        if (!$laporan) {
            return null;
        }
        
        $laporan['data_json'] = json_decode($laporan['data_json'], true);
        
        // Get additional info
        $sql = "SELECT k.nama_kelas, u.nama, u.email, u.foto
                FROM kelas k, users u
                WHERE k.id = :kelasId AND u.id = :siswaId";
        
        $info = $this->query($sql, [
            ':kelasId' => $laporan['id_kelas'],
            ':siswaId' => $laporan['id_siswa']
        ]);
        
        if (!empty($info)) {
            $laporan['info'] = $info[0];
        }
        
        return $laporan;
    }
    
    /**
     * Generate laporan untuk semua siswa di kelas
     */
    public function generateLaporanKelas($kelasId, $periode = 'semester') {
        // Get all siswa in kelas
        $sql = "SELECT id_siswa FROM siswa_kelas WHERE id_kelas = :kelasId";
        $siswaList = $this->query($sql, [':kelasId' => $kelasId]);
        
        $results = [];
        
        foreach ($siswaList as $siswa) {
            $result = $this->generateLaporan($siswa['id_siswa'], $kelasId, $periode);
            $results[] = $result;
        }
        
        return [
            'success' => true,
            'message' => count($results) . ' laporan berhasil digenerate',
            'results' => $results
        ];
    }
    
    /**
     * Compare siswa performance
     */
    public function compareSiswa($siswaIds, $kelasId) {
        $comparisons = [];
        
        foreach ($siswaIds as $siswaId) {
            $laporan = $this->generateLaporan($siswaId, $kelasId);
            $comparisons[] = [
                'siswa_id' => $siswaId,
                'stats' => $laporan['data']['statistik'],
                'gamifikasi' => $laporan['data']['gamifikasi']
            ];
        }
        
        return $comparisons;
    }
    
    /**
     * Get class summary
     */
    public function getClassSummary($kelasId) {
        $sql = "SELECT 
                COUNT(DISTINCT sk.id_siswa) as total_siswa,
                AVG(jt.nilai) as rata_rata_kelas,
                COUNT(DISTINCT t.id) as total_tugas,
                COUNT(jt.id) as total_submission,
                SUM(g.poin) as total_poin
                FROM siswa_kelas sk
                LEFT JOIN jawaban_tugas jt ON sk.id_siswa = jt.id_siswa
                LEFT JOIN tugas t ON jt.id_tugas = t.id AND t.id_kelas = :kelasId
                LEFT JOIN gamifikasi g ON sk.id_siswa = g.id_user
                WHERE sk.id_kelas = :kelasId";
        
        $result = $this->query($sql, [':kelasId' => $kelasId]);
        return $result[0] ?? null;
    }
    
    /**
     * Export laporan to array format (for PDF/Excel)
     */
    public function exportLaporan($laporanId) {
        $laporan = $this->getLaporanDetail($laporanId);
        
        if (!$laporan) {
            return null;
        }
        
        return [
            'siswa' => $laporan['info'],
            'kelas' => $laporan['info']['nama_kelas'],
            'periode' => $laporan['periode'],
            'tanggal' => date('d-m-Y', strtotime($laporan['created_at'])),
            'data' => $laporan['data_json']
        ];
    }
}
?>