<?php
/**
 * Kelas Model
 * Menangani operasi CRUD untuk tabel kelas dan siswa_kelas
 */

require_once 'Model.php';

class KelasModel extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'kelas';
    }
    
    /**
     * Buat kelas baru dengan kode unik
     */
    public function createKelas($data) {
        // Generate kode kelas unik
        if (!isset($data['kode_kelas'])) {
            $data['kode_kelas'] = $this->generateKodeKelas();
        }
        
        return $this->create($data);
    }
    
    /**
     * Generate kode kelas unik
     */
    private function generateKodeKelas() {
        do {
            $kode = 'KLS-' . strtoupper(substr(md5(time() . rand()), 0, 8));
            $exists = $this->getOne(['kode_kelas' => $kode]);
        } while ($exists);
        
        return $kode;
    }
    
    /**
     * Get kelas dengan informasi guru
     */
    public function getKelasWithGuru($id) {
        $sql = "SELECT k.*, u.nama as nama_guru, u.email as email_guru 
                FROM kelas k 
                LEFT JOIN users u ON k.id_guru = u.id 
                WHERE k.id = :id";
        
        $result = $this->query($sql, [':id' => $id]);
        return $result[0] ?? null;
    }
    
    /**
     * Get semua kelas dengan informasi guru
     */
    public function getAllKelasWithGuru() {
        $sql = "SELECT k.*, u.nama as nama_guru, 
                (SELECT COUNT(*) FROM siswa_kelas WHERE id_kelas = k.id) as jumlah_siswa
                FROM kelas k 
                LEFT JOIN users u ON k.id_guru = u.id 
                ORDER BY k.created_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Get kelas by guru
     */
    public function getKelasByGuru($guruId) {
        $sql = "SELECT k.*, 
                (SELECT COUNT(*) FROM siswa_kelas WHERE id_kelas = k.id) as jumlah_siswa
                FROM kelas k 
                WHERE k.id_guru = :guruId 
                ORDER BY k.created_at DESC";
        
        return $this->query($sql, [':guruId' => $guruId]);
    }
    
    /**
     * Get kelas by siswa
     */
    public function getKelasBySiswa($siswaId) {
        $sql = "SELECT k.*, u.nama as nama_guru, sk.tanggal_gabung
                FROM kelas k 
                JOIN siswa_kelas sk ON k.id = sk.id_kelas 
                JOIN users u ON k.id_guru = u.id 
                WHERE sk.id_siswa = :siswaId 
                ORDER BY sk.tanggal_gabung DESC";
        
        return $this->query($sql, [':siswaId' => $siswaId]);
    }
    
    /**
     * Tambah siswa ke kelas
     */
    public function addSiswa($kelasId, $siswaId) {
        $sql = "INSERT INTO siswa_kelas (id_kelas, id_siswa) VALUES (:kelasId, :siswaId)";
        
        try {
            return $this->execute($sql, [
                ':kelasId' => $kelasId,
                ':siswaId' => $siswaId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Hapus siswa dari kelas
     */
    public function removeSiswa($kelasId, $siswaId) {
        $sql = "DELETE FROM siswa_kelas WHERE id_kelas = :kelasId AND id_siswa = :siswaId";
        
        return $this->execute($sql, [
            ':kelasId' => $kelasId,
            ':siswaId' => $siswaId
        ]);
    }
    
    /**
     * Get siswa dalam kelas
     */
    public function getSiswaInKelas($kelasId) {
        $sql = "SELECT u.*, sk.tanggal_gabung, g.poin, g.badges
                FROM users u 
                JOIN siswa_kelas sk ON u.id = sk.id_siswa 
                LEFT JOIN gamifikasi g ON u.id = g.id_user
                WHERE sk.id_kelas = :kelasId 
                ORDER BY u.nama ASC";
        
        return $this->query($sql, [':kelasId' => $kelasId]);
    }
    
    /**
     * Check apakah siswa terdaftar di kelas
     */
    public function isSiswaInKelas($kelasId, $siswaId) {
        $sql = "SELECT COUNT(*) as total FROM siswa_kelas WHERE id_kelas = :kelasId AND id_siswa = :siswaId";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':kelasId', $kelasId);
        $stmt->bindValue(':siswaId', $siswaId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Gabung kelas dengan kode
     */
    public function joinByKode($kodeKelas, $siswaId) {
        $kelas = $this->getOne(['kode_kelas' => $kodeKelas]);
        
        if (!$kelas) {
            return ['success' => false, 'message' => 'Kode kelas tidak ditemukan'];
        }
        
        // Check jika sudah terdaftar
        if ($this->isSiswaInKelas($kelas['id'], $siswaId)) {
            return ['success' => false, 'message' => 'Anda sudah terdaftar di kelas ini'];
        }
        
        // Tambah siswa
        if ($this->addSiswa($kelas['id'], $siswaId)) {
            return ['success' => true, 'message' => 'Berhasil bergabung ke kelas', 'kelas' => $kelas];
        }
        
        return ['success' => false, 'message' => 'Gagal bergabung ke kelas'];
    }
}
?>