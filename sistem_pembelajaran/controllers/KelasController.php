<?php
/**
 * Kelas Controller
 * Menangani CRUD dan operasi kelas
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/KelasModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/MateriModel.php';

class KelasController extends Controller {
    
    private $kelasModel;
    private $userModel;
    private $materiModel;
    
    public function __construct() {
        $this->kelasModel = new KelasModel();
        $this->userModel = new UserModel();
        $this->materiModel = new MateriModel();
    }
    
    /**
     * Show all kelas
     */
    public function index() {
        $this->requireLogin();
        
        $user = getCurrentUser();
        
        if ($user['peran'] === 'admin') {
            $kelas = $this->kelasModel->getAllKelasWithGuru();
        } elseif ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getKelasByGuru($user['id']);
        } else {
            $kelas = $this->kelasModel->getKelasBySiswa($user['id']);
        }
        
        $this->view('kelas/index', [
            'title' => 'Daftar Kelas',
            'kelas' => $kelas,
            'user' => $user
        ]);
    }
    
    /**
     * Show kelas detail
     */
    public function detail($id) {
        $this->requireLogin();
        
        $user = getCurrentUser();
        $kelas = $this->kelasModel->getKelasWithGuru($id);
        
        if (!$kelas) {
            setMessage('error', 'Kelas tidak ditemukan');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        // Check access
        if ($user['peran'] === 'siswa') {
            if (!$this->kelasModel->isSiswaInKelas($id, $user['id'])) {
                setMessage('error', 'Anda tidak memiliki akses ke kelas ini');
                $this->redirect(BASE_URL . 'kelas.php');
            }
        } elseif ($user['peran'] === 'guru') {
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses ke kelas ini');
                $this->redirect(BASE_URL . 'kelas.php');
            }
        }
        
        // Get siswa in kelas
        $siswa = $this->kelasModel->getSiswaInKelas($id);
        
        // Get materi
        $materi = $this->materiModel->getMateriByKelas($id);
        
        $this->view('kelas/detail', [
            'title' => $kelas['nama_kelas'],
            'kelas' => $kelas,
            'siswa' => $siswa,
            'materi' => $materi,
            'user' => $user
        ]);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        $guruList = [];
        
        if ($user['peran'] === 'admin') {
            $guruList = $this->userModel->getAllGuru();
        }
        
        $this->view('kelas/create', [
            'title' => 'Buat Kelas Baru',
            'guruList' => $guruList,
            'user' => $user
        ]);
    }
    
    /**
     * Process create kelas
     */
    public function store() {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $user = getCurrentUser();
        
        $data = [
            'nama_kelas' => sanitize($this->getPost('nama_kelas')),
            'deskripsi' => sanitize($this->getPost('deskripsi')),
            'id_guru' => $user['peran'] === 'admin' ? 
                        $this->getPost('id_guru') : $user['id']
        ];
        
        // Validation
        if (empty($data['nama_kelas'])) {
            setMessage('error', 'Nama kelas harus diisi');
            $this->redirect(BASE_URL . 'kelas.php?action=create');
        }
        
        $kelasId = $this->kelasModel->createKelas($data);
        
        if ($kelasId) {
            setMessage('success', 'Kelas berhasil dibuat');
            $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
        } else {
            setMessage('error', 'Gagal membuat kelas');
            $this->redirect(BASE_URL . 'kelas.php?action=create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        $kelas = $this->kelasModel->getKelasWithGuru($id);
        
        if (!$kelas) {
            setMessage('error', 'Kelas tidak ditemukan');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru' && $kelas['id_guru'] != $user['id']) {
            setMessage('error', 'Anda tidak memiliki akses');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $guruList = [];
        if ($user['peran'] === 'admin') {
            $guruList = $this->userModel->getAllGuru();
        }
        
        $this->view('kelas/edit', [
            'title' => 'Edit Kelas',
            'kelas' => $kelas,
            'guruList' => $guruList,
            'user' => $user
        ]);
    }
    
    /**
     * Process update kelas
     */
    public function update($id) {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $user = getCurrentUser();
        $kelas = $this->kelasModel->getById($id);
        
        if (!$kelas) {
            setMessage('error', 'Kelas tidak ditemukan');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru' && $kelas['id_guru'] != $user['id']) {
            setMessage('error', 'Anda tidak memiliki akses');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $data = [
            'nama_kelas' => sanitize($this->getPost('nama_kelas')),
            'deskripsi' => sanitize($this->getPost('deskripsi'))
        ];
        
        if ($user['peran'] === 'admin') {
            $data['id_guru'] = $this->getPost('id_guru');
        }
        
        if ($this->kelasModel->update($id, $data)) {
            setMessage('success', 'Kelas berhasil diupdate');
        } else {
            setMessage('error', 'Gagal update kelas');
        }
        
        $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $id);
    }
    
    /**
     * Delete kelas
     */
    public function delete($id) {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        $kelas = $this->kelasModel->getById($id);
        
        if (!$kelas) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Kelas tidak ditemukan']);
            }
            setMessage('error', 'Kelas tidak ditemukan');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru' && $kelas['id_guru'] != $user['id']) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Anda tidak memiliki akses']);
            }
            setMessage('error', 'Anda tidak memiliki akses');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        if ($this->kelasModel->delete($id)) {
            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Kelas berhasil dihapus']);
            }
            setMessage('success', 'Kelas berhasil dihapus');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Gagal menghapus kelas']);
            }
            setMessage('error', 'Gagal menghapus kelas');
        }
        
        $this->redirect(BASE_URL . 'kelas.php');
    }
    
    /**
     * Join kelas dengan kode
     */
    public function join() {
        $this->requireRole('siswa');
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $user = getCurrentUser();
        $kodeKelas = sanitize($this->getPost('kode_kelas'));
        
        $result = $this->kelasModel->joinByKode($kodeKelas, $user['id']);
        
        if ($result['success']) {
            setMessage('success', $result['message']);
            $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $result['kelas']['id']);
        } else {
            setMessage('error', $result['message']);
            $this->redirect(BASE_URL . 'kelas.php');
        }
    }
    
    /**
     * Add siswa to kelas
     */
    public function addSiswa($kelasId) {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
        }
        
        $siswaId = $this->getPost('siswa_id');
        
        if ($this->kelasModel->addSiswa($kelasId, $siswaId)) {
            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Siswa berhasil ditambahkan']);
            }
            setMessage('success', 'Siswa berhasil ditambahkan');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Gagal menambahkan siswa']);
            }
            setMessage('error', 'Gagal menambahkan siswa');
        }
        
        $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
    }
    
    /**
     * Remove siswa from kelas
     */
    public function removeSiswa($kelasId, $siswaId) {
        $this->requireRole(['admin', 'guru']);
        
        if ($this->kelasModel->removeSiswa($kelasId, $siswaId)) {
            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Siswa berhasil dihapus dari kelas']);
            }
            setMessage('success', 'Siswa berhasil dihapus dari kelas');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Gagal menghapus siswa']);
            }
            setMessage('error', 'Gagal menghapus siswa');
        }
        
        $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
    }
}
?>