<?php
/**
 * User Model
 * Menangani operasi CRUD untuk tabel users
 */

require_once 'Model.php';

class UserModel extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'users';
    }
    
    /**
     * Register user baru
     */
    public function register($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default foto jika tidak ada
        if (!isset($data['foto'])) {
            $data['foto'] = 'default-avatar.jpg';
        }
        
        return $this->create($data);
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        $user = $this->getOne(['email' => $email]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Hapus password dari return data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        return $this->getOne(['email' => $email]);
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }
    
    /**
     * Update profil
     */
    public function updateProfile($id, $data) {
        // Jangan update password di sini
        unset($data['password']);
        return $this->update($id, $data);
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        return $this->getAll(['peran' => $role], 'nama ASC');
    }
    
    /**
     * Get all guru
     */
    public function getAllGuru() {
        return $this->getUsersByRole('guru');
    }
    
    /**
     * Get all siswa
     */
    public function getAllSiswa() {
        return $this->getUsersByRole('siswa');
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = :email";
        
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        
        if ($excludeId) {
            $stmt->bindValue(':excludeId', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Upload foto profil
     */
    public function uploadFoto($id, $file) {
        $uploadDir = UPLOAD_PATH . 'profiles/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = 'profile_' . $id . '_' . time() . '.' . $extension;
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $this->update($id, ['foto' => $fileName]);
        }
        
        return false;
    }
    
    /**
     * Get siswa dengan gamifikasi
     */
    public function getSiswaWithGamifikasi($kelasId = null) {
        $sql = "SELECT u.*, g.poin, g.badges 
                FROM users u 
                LEFT JOIN gamifikasi g ON u.id = g.id_user 
                WHERE u.peran = 'siswa'";
        
        if ($kelasId) {
            $sql .= " AND u.id IN (SELECT id_siswa FROM siswa_kelas WHERE id_kelas = :kelasId)";
        }
        
        $sql .= " ORDER BY g.poin DESC";
        
        $params = [];
        if ($kelasId) {
            $params[':kelasId'] = $kelasId;
        }
        
        return $this->query($sql, $params);
    }
}
?>