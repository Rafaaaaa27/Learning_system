<?php
/**
 * Auth Controller
 * Menangani proses authentication (login, register, logout)
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/GamifikasiModel.php';

class AuthController extends Controller {
    
    private $userModel;
    private $gamifikasiModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->gamifikasiModel = $this->model('GamifikasiModel');
    }
    
    /**
     * Show login form
     */
    public function showLogin() {
        if (isLoggedIn()) {
            $this->redirect(BASE_URL . 'dashboard.php');
        }
        
        $this->view('auth/login', [
            'title' => 'Login - ' . SITE_NAME
        ]);
    }
    
    /**
     * Process login
     */
    public function processLogin() {
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'login.php');
        }
        
        $email = sanitize($this->getPost('email'));
        $password = $this->getPost('password');
        $remember = $this->getPost('remember');
        
        // Validation
        if (empty($email) || empty($password)) {
            setMessage('error', 'Email dan password harus diisi');
            $this->redirect(BASE_URL . 'login.php');
        }
        
        // Attempt login
        $user = $this->userModel->login($email, $password);
        
        if ($user) {
            // Set session
            login($user);
            
            // Remember me
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/');
                // Store token in database (implementasi di UserModel jika diperlukan)
            }
            
            setMessage('success', 'Login berhasil! Selamat datang, ' . $user['nama']);
            $this->redirect(BASE_URL . 'dashboard.php');
        } else {
            setMessage('error', 'Email atau password salah');
            $this->redirect(BASE_URL . 'login.php');
        }
    }
    
    /**
     * Show register form
     */
    public function showRegister() {
        if (isLoggedIn()) {
            $this->redirect(BASE_URL . 'dashboard.php');
        }
        
        $this->view('auth/register', [
            'title' => 'Daftar - ' . SITE_NAME
        ]);
    }
    
    /**
     * Process register
     */
    public function processRegister() {
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'register.php');
        }
        
        $data = [
            'nama' => sanitize($this->getPost('nama')),
            'email' => sanitize($this->getPost('email')),
            'password' => $this->getPost('password'),
            'password_confirm' => $this->getPost('password_confirm'),
            'peran' => sanitize($this->getPost('peran', 'siswa')),
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
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Email sudah terdaftar';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Password harus diisi';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        } elseif ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
        
        if (!in_array($data['peran'], ['siswa', 'guru', 'admin'])) {
            $errors[] = 'Peran tidak valid';
        }
        
        if (!empty($errors)) {
            $_SESSION['register_errors'] = $errors;
            $_SESSION['old_input'] = $data;
            $this->redirect(BASE_URL . 'register.php');
        }
        
        // Remove password_confirm from data
        unset($data['password_confirm']);
        
        // Register user
        $userId = $this->userModel->register($data);
        
        if ($userId) {
            // Create gamifikasi record untuk siswa
            if ($data['peran'] === 'siswa') {
                $this->gamifikasiModel->create([
                    'id_user' => $userId,
                    'poin' => 0,
                    'badges' => json_encode(['Pemula'])
                ]);
            }
            
            setMessage('success', 'Registrasi berhasil! Silakan login');
            $this->redirect(BASE_URL . 'login.php');
        } else {
            setMessage('error', 'Registrasi gagal. Silakan coba lagi');
            $this->redirect(BASE_URL . 'register.php');
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        session_destroy();
        setMessage('success', 'Anda telah logout');
        $this->redirect(BASE_URL . 'login.php');
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'dashboard.php');
        }
        
        $currentPassword = $this->getPost('current_password');
        $newPassword = $this->getPost('new_password');
        $confirmPassword = $this->getPost('confirm_password');
        
        $user = getCurrentUser();
        $userDb = $this->userModel->getById($user['id']);
        
        // Validate current password
        if (!password_verify($currentPassword, $userDb['password'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Password lama salah']);
            }
            setMessage('error', 'Password lama salah');
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Password baru minimal 6 karakter']);
            }
            setMessage('error', 'Password baru minimal 6 karakter');
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        if ($newPassword !== $confirmPassword) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Konfirmasi password tidak cocok']);
            }
            setMessage('error', 'Konfirmasi password tidak cocok');
            $this->redirect(BASE_URL . 'profile.php');
        }
        
        // Update password
        if ($this->userModel->updatePassword($user['id'], $newPassword)) {
            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Password berhasil diubah']);
            }
            setMessage('success', 'Password berhasil diubah');
            $this->redirect(BASE_URL . 'profile.php');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Gagal mengubah password']);
            }
            setMessage('error', 'Gagal mengubah password');
            $this->redirect(BASE_URL . 'profile.php');
        }
    }
}
?>