<?php
/**
 * Admin Controller
 * Menangani semua operasi admin panel
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/KelasModel.php';
require_once __DIR__ . '/../models/TugasModel.php';
require_once __DIR__ . '/../models/GamifikasiModel.php';

class AdminController extends Controller {
    
    private $userModel;
    private $kelasModel;
    private $tugasModel;
    private $gamifikasiModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->kelasModel = new KelasModel();
        $this->tugasModel = new TugasModel();
        $this->gamifikasiModel = new GamifikasiModel();
    }
    
    /**
     * Admin Dashboard
     */
    public function index() {
        $this->requireRole('admin');
        
        // Get statistics
        $stats = [
            'total_users' => $this->userModel->count(),
            'total_admin' => $this->userModel->count(['peran' => 'admin']),
            'total_guru' => $this->userModel->count(['peran' => 'guru']),
            'total_siswa' => $this->userModel->count(['peran' => 'siswa']),
            'total_kelas' => $this->kelasModel->count(),
            'total_tugas' => $this->tugasModel->count()
        ];
        
        // Recent users
        $recentUsers = $this->userModel->getAll([], 'created_at DESC', 5);
        
        // Recent kelas
        $recentKelas = $this->kelasModel->getAllKelasWithGuru();
        $recentKelas = array_slice($recentKelas, 0, 5);
        
        // User growth chart data
        $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as bulan,
                COUNT(CASE WHEN peran = 'siswa' THEN 1 END) as siswa,
                COUNT(CASE WHEN peran = 'guru' THEN 1 END) as guru,
                COUNT(CASE WHEN peran = 'admin' THEN 1 END) as admin
                FROM users 
                GROUP BY bulan
                ORDER BY bulan DESC
                LIMIT 6";
        
        $chartData = $this->userModel->query($sql);
        
        $this->view('admin/index', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'recent_kelas' => $recentKelas,
            'chart_data' => array_reverse($chartData)
        ]);
    }
    
    /**
     * Manage Users
     */
    public function users() {
        $this->requireRole('admin');
        
        $role = $_GET['role'] ?? 'all';
        $search = $_GET['search'] ?? '';
        
        // Get users
        if ($role !== 'all') {
            $users = $this->userModel->getAll(['peran' => $role], 'created_at DESC');
        } else {
            $users = $this->userModel->getAll([], 'created_at DESC');
        }
        
        // Search filter
        if (!empty($search)) {
            $users = array_filter($users, function($user) use ($search) {
                return stripos($user['nama'], $search) !== false || 
                       stripos($user['email'], $search) !== false;
            });
        }
        
        $this->view('admin/users', [
            'title' => 'Manajemen User',
            'users' => $users,
            'current_role' => $role,
            'search' => $search
        ]);
    }
    
    /**
     * Show create user form
     */
    public function createUser() {
        $this->requireRole('admin');
        
        $this->view('admin/user_form', [
            'title' => 'Tambah User Baru',
            'user_data' => null,
            'is_edit' => false
        ]);
    }
    
    /**
     * Process create user
     */
    public function storeUser() {
        $this->requireRole('admin');
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'admin.php?page=users');
        }
        
        $data = [
            'nama' => sanitize($this->getPost('nama')),
            'email' => sanitize($this->getPost('email')),
            'password' => $this->getPost('password'),
            'peran' => $this->getPost('peran'),
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
        }
        
        if (!in_array($data['peran'], ['admin', 'guru', 'siswa'])) {
            $errors[] = 'Peran tidak valid';
        }
        
        if (!empty($errors)) {
            $_SESSION['admin_errors'] = $errors;
            $_SESSION['old_admin_input'] = $data;
            $this->redirect(BASE_URL . 'admin.php?page=create_user');
        }
        
        // Create user
        $userId = $this->userModel->register($data);
        
        if ($userId) {
            // Create gamifikasi for siswa
            if ($data['peran'] === 'siswa') {
                require_once __DIR__ . '/../models/GamifikasiModel.php';
                $gamifikasiModel = new GamifikasiModel();
                $gamifikasiModel->create([
                    'id_user' => $userId,
                    'poin' => 0,
                    'badges' => json_encode(['Pemula'])
                ]);
            }
            
            setMessage('success', 'User berhasil ditambahkan');
            $this->redirect(BASE_URL . 'admin.php?page=users');
        } else {
            setMessage('error', 'Gagal menambahkan user');
            $this->redirect(BASE_URL . 'admin.php?page=create_user');
        }
    }
    
    /**
     * Show edit user form
     */
    public function editUser($id) {
        $this->requireRole('admin');
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            setMessage('error', 'User tidak ditemukan');
            $this->redirect(BASE_URL . 'admin.php?page=users');
        }
        
        $this->view('admin/user_form', [
            'title' => 'Edit User',
            'user_data' => $user,
            'is_edit' => true
        ]);
    }
    
    /**
     * Process update user
     */
    public function updateUser($id) {
        $this->requireRole('admin');
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'admin.php?page=users');
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            setMessage('error', 'User tidak ditemukan');
            $this->redirect(BASE_URL . 'admin.php?page=users');
        }
        
        $data = [
            'nama' => sanitize($this->getPost('nama')),
            'email' => sanitize($this->getPost('email')),
            'peran' => $this->getPost('peran'),
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
        } elseif ($this->userModel->emailExists($data['email'], $id)) {
            $errors[] = 'Email sudah digunakan';
        }
        
        if (!in_array($data['peran'], ['admin', 'guru', 'siswa'])) {
            $errors[] = 'Peran tidak valid';
        }
        
        if (!empty($errors)) {
            $_SESSION['admin_errors'] = $errors;
            $this->redirect(BASE_URL . 'admin.php?page=edit_user&id=' . $id);
        }
        
        // Update password if provided
        $newPassword = $this->getPost('password');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                setMessage('error', 'Password minimal 6 karakter');
                $this->redirect(BASE_URL . 'admin.php?page=edit_user&id=' . $id);
            }
            $this->userModel->updatePassword($id, $newPassword);
        }
        
        if ($this->userModel->update($id, $data)) {
            setMessage('success', 'User berhasil diupdate');
        } else {
            setMessage('error', 'Gagal update user');
        }
        
        $this->redirect(BASE_URL . 'admin.php?page=users');
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id) {
        $this->requireRole('admin');
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'User tidak ditemukan']);
            }
            setMessage('error', 'User tidak ditemukan');
            $this->redirect(BASE_URL . 'admin.php?page=users');
        }
        
        // Prevent deleting yourself
        $currentUser = getCurrentUser();
        if ($user['id'] == $currentUser['id']) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri']);
            }
            setMessage('error', 'Tidak dapat menghapus akun sendiri');
            $this->redirect(BASE_URL . 'admin.php?page=users');
        }
        
        // Delete photo
        if ($user['foto'] && $user['foto'] !== 'default-avatar.jpg') {
            deleteFile('profiles/' . $user['foto']);
        }
        
        if ($this->userModel->delete($id)) {
            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'User berhasil dihapus']);
            }
            setMessage('success', 'User berhasil dihapus');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Gagal menghapus user']);
            }
            setMessage('error', 'Gagal menghapus user');
        }
        
        $this->redirect(BASE_URL . 'admin.php?page=users');
    }
    
    /**
     * Manage Kelas
     */
    public function kelas() {
        $this->requireRole('admin');
        
        $search = $_GET['search'] ?? '';
        
        $kelas = $this->kelasModel->getAllKelasWithGuru();
        
        // Search filter
        if (!empty($search)) {
            $kelas = array_filter($kelas, function($k) use ($search) {
                return stripos($k['nama_kelas'], $search) !== false || 
                       stripos($k['nama_guru'], $search) !== false;
            });
        }
        
        $this->view('admin/kelas', [
            'title' => 'Manajemen Kelas',
            'kelas' => $kelas,
            'search' => $search
        ]);
    }
    
    /**
     * Settings
     */
    public function settings() {
        $this->requireRole('admin');
        
        $this->view('admin/settings', [
            'title' => 'Pengaturan Sistem'
        ]);
    }
    
    /**
     * Reports
     */
    public function reports() {
        $this->requireRole('admin');
        
        // Get comprehensive statistics
        $stats = [
            'users' => [
                'total' => $this->userModel->count(),
                'admin' => $this->userModel->count(['peran' => 'admin']),
                'guru' => $this->userModel->count(['peran' => 'guru']),
                'siswa' => $this->userModel->count(['peran' => 'siswa'])
            ],
            'kelas' => [
                'total' => $this->kelasModel->count(),
                'active' => $this->kelasModel->count()
            ],
            'tugas' => [
                'total' => $this->tugasModel->count(),
                'essay' => $this->tugasModel->count(['tipe' => 'essay']),
                'multiple_choice' => $this->tugasModel->count(['tipe' => 'multiple_choice'])
            ]
        ];
        
        // Top students
        $topStudents = $this->gamifikasiModel->getLeaderboard(null, 10);
        
        // Active classes
        $activeClasses = $this->kelasModel->getAllKelasWithGuru();
        
        $this->view('admin/reports', [
            'title' => 'Laporan Sistem',
            'stats' => $stats,
            'top_students' => $topStudents,
            'active_classes' => $activeClasses
        ]);
    }
    
    /**
     * Logs
     */
    public function logs() {
        $this->requireRole('admin');
        
        // Get recent activities
        $activities = [
            [
                'user' => 'System',
                'action' => 'Database Backup',
                'time' => date('Y-m-d H:i:s'),
                'status' => 'success'
            ],
            // Add more log entries as needed
        ];
        
        $this->view('admin/logs', [
            'title' => 'System Logs',
            'activities' => $activities
        ]);
    }
}
?>