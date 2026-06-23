<?php
/**
 * Materi Model
 * Menangani operasi CRUD untuk materi pembelajaran
 */

require_once 'Model.php';

class MateriModel extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'materi';
    }
    
    /**
     * Get materi by kelas
     */
    public function getMateriByKelas($kelasId) {
        $sql = "SELECT m.*, u.nama as uploaded_by
                FROM materi m
                JOIN kelas k ON m.id_kelas = k.id
                JOIN users u ON k.id_guru = u.id
                WHERE m.id_kelas = :kelasId
                ORDER BY m.uploaded_at DESC";
        
        return $this->query($sql, [':kelasId' => $kelasId]);
    }
    
    /**
     * Get materi dengan informasi kelas
     */
    public function getMateriWithKelas($id) {
        $sql = "SELECT m.*, k.nama_kelas, u.nama as nama_guru
                FROM materi m
                JOIN kelas k ON m.id_kelas = k.id
                JOIN users u ON k.id_guru = u.id
                WHERE m.id = :id";
        
        $result = $this->query($sql, [':id' => $id]);
        return $result[0] ?? null;
    }
    
    /**
     * Upload materi
     */
    public function uploadMateri($data, $file = null) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . 'materi/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($extension, $allowedTypes)) {
                return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
            }
            
            if ($file['size'] > MAX_FILE_SIZE) {
                return ['success' => false, 'message' => 'File terlalu besar (max 10MB)'];
            }
            
            $fileName = 'materi_' . uniqid() . '_' . time() . '.' . $extension;
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $data['file_path'] = 'materi/' . $fileName;
            } else {
                return ['success' => false, 'message' => 'Gagal upload file'];
            }
        }
        
        $id = $this->create($data);
        
        if ($id) {
            return ['success' => true, 'message' => 'Materi berhasil diupload', 'id' => $id];
        }
        
        return ['success' => false, 'message' => 'Gagal menyimpan materi'];
    }
    
    /**
     * Update materi
     */
    public function updateMateri($id, $data, $file = null) {
        $materi = $this->getById($id);
        
        if (!$materi) {
            return ['success' => false, 'message' => 'Materi tidak ditemukan'];
        }
        
        // Handle file upload if provided
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            // Delete old file
            if ($materi['file_path']) {
                $oldFile = UPLOAD_PATH . $materi['file_path'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            $uploadDir = UPLOAD_PATH . 'materi/';
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName = 'materi_' . uniqid() . '_' . time() . '.' . $extension;
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $data['file_path'] = 'materi/' . $fileName;
            }
        }
        
        if ($this->update($id, $data)) {
            return ['success' => true, 'message' => 'Materi berhasil diupdate'];
        }
        
        return ['success' => false, 'message' => 'Gagal update materi'];
    }
    
    /**
     * Delete materi
     */
    public function deleteMateri($id) {
        $materi = $this->getById($id);
        
        if (!$materi) {
            return ['success' => false, 'message' => 'Materi tidak ditemukan'];
        }
        
        // Delete physical file
        if ($materi['file_path']) {
            $filePath = UPLOAD_PATH . $materi['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        if ($this->delete($id)) {
            return ['success' => true, 'message' => 'Materi berhasil dihapus'];
        }
        
        return ['success' => false, 'message' => 'Gagal menghapus materi'];
    }
    
    /**
     * Get recent materi for siswa
     */
    public function getRecentMateriForSiswa($siswaId, $limit = 5) {
        $sql = "SELECT m.*, k.nama_kelas
                FROM materi m
                JOIN kelas k ON m.id_kelas = k.id
                JOIN siswa_kelas sk ON k.id = sk.id_kelas
                WHERE sk.id_siswa = :siswaId
                ORDER BY m.uploaded_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':siswaId', $siswaId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Search materi
     */
    public function searchMateri($keyword, $kelasId = null) {
        $sql = "SELECT m.*, k.nama_kelas
                FROM materi m
                JOIN kelas k ON m.id_kelas = k.id
                WHERE (m.judul LIKE :keyword OR m.deskripsi LIKE :keyword)";
        
        $params = [':keyword' => "%{$keyword}%"];
        
        if ($kelasId) {
            $sql .= " AND m.id_kelas = :kelasId";
            $params[':kelasId'] = $kelasId;
        }
        
        $sql .= " ORDER BY m.uploaded_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get statistik materi
     */
    public function getStatistik($kelasId) {
        $sql = "SELECT 
                COUNT(*) as total_materi,
                COUNT(CASE WHEN file_path LIKE '%.pdf' THEN 1 END) as total_pdf,
                COUNT(CASE WHEN file_path LIKE '%.doc%' THEN 1 END) as total_doc,
                COUNT(CASE WHEN file_path LIKE '%.ppt%' THEN 1 END) as total_ppt
                FROM materi
                WHERE id_kelas = :kelasId";
        
        $result = $this->query($sql, [':kelasId' => $kelasId]);
        return $result[0] ?? null;
    }
}
?>