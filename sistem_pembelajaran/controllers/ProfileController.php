<?php
/**
 * Profile Controller
 * Menangani profil user dan update data
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/GamifikasiModel.php';
require_once __DIR__ . '/../models/KelasModel.php';

class ProfileController extends Controller {
    
    private $userModel;
    private $gamifikasiModel;
    private $kelasModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->gamifikasiModel = new GamifikasiModel();
        $this->kelasModel = new KelasModel();
    }
    
    /**
     * Show profile
     */
    public function index() {
        $this->requireLogin();
        
        $user = getCurrentUser();
        $userId = $_GET['user_id'] ?? $user['id'];
        
        // Check permission
        if ($userId != $user['id'] && !in_array($user['peran'], ['admin', 'guru'])) {
            setMessage('error', 'Anda tidak memiliki akses');
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        $profile = $this->userModel->getById($userId);
        
        if (!$profile) {
            setMessage('error', 'User tidak ditemukan');
            $this->redirect(BASE_URL . 'dashboard.php');
        }
        
        // Get additional data based on role
        $data = [
            'title' => 'Profil - ' . $profile['nama'],
            'profile' => $profile,
            'user' => $user
        ];
        
        if ($profile['peran'] === 'siswa') {
            $data['gamifikasi'] = $this->gamifikasiModel->getStatistik($userId);
            $data['kelas'] = $this->kelasModel->getKelasBySiswa($userId);
        } elseif ($profile['peran'] === 'guru') {
            $data['kelas'] = $this->kelasModel->getKelasByGuru($userId);
        }
        
        $this->view('profile/index', $data);
    }
    
    /**
     * Show edit form
     */
    public function edit() {
        $this->requireLogin();
        
        $user = getCurrentUser();
        $profile = $this->userModel->getById($user['id']);
        
        $this->view('profile/edit', [
            'title' => 'Edit Profil',
            'profile' => $profile,
            'user' => $user
        ]);
    }
    
    /**
     * Update profile
     */
    public function update() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        $user = getCurrentUser();
        
        $data = [
            'nama' => sanitize($this->getPost('nama')),
            'email' => sanitize($this->getPost('email')),
            'sekolah' => sanitize($this->getPost('sekolah'))
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['nama'])) {
            $errors[] = 'Nama harus diisi';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email harus diisi';
        } elseif (!validateEmail($data['email'])) {
            $errors[] = 'Email tidak valid';
        } elseif ($this->userModel->emailExists($data['email'], $user['id'])) {
            $errors[] = 'Email sudah digunakan';
        }
        
        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            $this->redirect(BASE_URL . 'profile.php?action=edit');
        }
        
        // Handle photo upload
        $file = $this->getFile('foto');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($file, 'profiles', ['jpg', 'jpeg', 'png', 'gif']);
            if ($upload['success']) {
                // Delete old photo
                $oldProfile = $this->userModel->getById($user['id']);
                if ($oldProfile['foto'] && $oldProfile['foto'] !== 'default-avatar.jpg') {
                    deleteFile('profiles/' . $oldProfile['foto']);
                }
                
                $data['foto'] = $upload['filename'];
            }
        }
        
        if ($this->userModel->updateProfile($user['id'], $data)) {
            // Update session
            $_SESSION['user_nama'] = $data['nama'];
            $_SESSION['user_email'] = $data['email'];
            if (isset($data['foto'])) {
                $_SESSION['user_foto'] = $data['foto'];
            }
            
            setMessage('success', 'Profil berhasil diupdate');
        } else {
            setMessage('error', 'Gagal update profil');
        }
        
        $this->redirect(BASE_URL . 'profile.php');
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        $user = getCurrentUser();
        
        $currentPassword = $this->getPost('current_password');
        $newPassword = $this->getPost('new_password');
        $confirmPassword = $this->getPost('confirm_password');
        
        $userDb = $this->userModel->getById($user['id']);
        
        // Validate current password
        if (!password_verify($currentPassword, $userDb['password'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Password lama salah']);
            }
            setMessage('error', 'Password lama salah');
            $this->redirect(BASE_URL . 'profile.php?action=edit');
        }
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Password baru minimal 6 karakter']);
            }
            setMessage('error', 'Password baru minimal 6 karakter');
            $this->redirect(BASE_URL . 'profile.php?action=edit');
        }
        
        if ($newPassword !== $confirmPassword) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Konfirmasi password tidak cocok']);
            }
            setMessage('error', 'Konfirmasi password tidak cocok');
            $this->redirect(BASE_URL . 'profile.php?action=edit');
        }
        
        // Update password
        if ($this->userModel->updatePassword($user['id'], $newPassword)) {
            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Password berhasil diubah']);
            }
            setMessage('success', 'Password berhasil diubah');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Gagal mengubah password']);
            }
            setMessage('error', 'Gagal mengubah password');
        }
        
        $this->redirect(BASE_URL . 'profile.php');
    }
    
    /**
     * Delete account
     */
    public function deleteAccount() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        $user = getCurrentUser();
        $password = $this->getPost('password');
        
        $userDb = $this->userModel->getById($user['id']);
        
        // Validate password
        if (!password_verify($password, $userDb['password'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Password salah']);
            }
            setMessage('error', 'Password salah');
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        // Delete photo
        if ($userDb['foto'] && $userDb['foto'] !== 'default-avatar.jpg') {
            deleteFile('profiles/' . $userDb['foto']);
        }
        
        // Delete account
        if ($this->userModel->delete($user['id'])) {
            session_destroy();
            setMessage('success', 'Akun berhasil dihapus');
            $this->redirect(BASE_URL . 'index.php');
        } else {
            setMessage('error', 'Gagal menghapus akun');
            $this->redirect(BASE_URL . 'profile.php');
        }
    }
}
?>