<?php
/**
 * Tugas Controller
 * Menangani CRUD dan operasi tugas
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/TugasModel.php';
require_once __DIR__ . '/../models/KelasModel.php';
require_once __DIR__ . '/../models/NotifikasiModel.php';
require_once __DIR__ . '/../models/GamifikasiModel.php';

class TugasController extends Controller {
    
    private $tugasModel;
    private $kelasModel;
    private $notifikasiModel;
    private $gamifikasiModel;
    
    public function __construct() {
        $this->tugasModel = new TugasModel();
        $this->kelasModel = new KelasModel();
        $this->notifikasiModel = new NotifikasiModel();
        $this->gamifikasiModel = new GamifikasiModel();
    }
    
    /**
     * Show all tugas
     */
    public function index() {
        $this->requireLogin();
        
        $user = getCurrentUser();
        
        if ($user['peran'] === 'siswa') {
            $tugas = $this->tugasModel->getTugasForSiswa($user['id']);
        } elseif ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getKelasByGuru($user['id']);
            $tugas = [];
            foreach ($kelas as $k) {
                $kelTugas = $this->tugasModel->getTugasByKelas($k['id']);
                $tugas = array_merge($tugas, $kelTugas);
            }
        } else {
            // Admin
            $tugas = $this->tugasModel->getAll([], 'created_at DESC');
        }
        
        $this->view('tugas/index', [
            'title' => 'Daftar Tugas',
            'tugas' => $tugas,
            'user' => $user
        ]);
    }
    
    /**
     * Show tugas detail
     */
    public function detail($id) {
        $this->requireLogin();
        
        $user = getCurrentUser();
        $tugas = $this->tugasModel->getTugasWithKelas($id);
        
        if (!$tugas) {
            setMessage('error', 'Tugas tidak ditemukan');
            $this->redirect(BASE_URL . 'tugas.php');
        }
        
        // Check access
        if ($user['peran'] === 'siswa') {
            if (!$this->kelasModel->isSiswaInKelas($tugas['id_kelas'], $user['id'])) {
                setMessage('error', 'Anda tidak memiliki akses ke tugas ini');
                $this->redirect(BASE_URL . 'tugas.php');
            }
            
            // Get jawaban siswa
            $jawaban = $this->tugasModel->getJawabanSiswa($id, $user['id']);
        } elseif ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($tugas['id_kelas']);
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses ke tugas ini');
                $this->redirect(BASE_URL . 'tugas.php');
            }
            
            // Get all jawaban untuk guru
            $jawaban = $this->tugasModel->getJawabanByTugas($id);
            $statistik = $this->tugasModel->getStatistikTugas($id);
        }
        
        $this->view('tugas/detail', [
            'title' => $tugas['judul'],
            'tugas' => $tugas,
            'jawaban' => $jawaban ?? null,
            'statistik' => $statistik ?? null,
            'user' => $user
        ]);
    }
    
    /**
     * Show create form (guru only)
     */
    public function create() {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getKelasByGuru($user['id']);
        } else {
            $kelas = $this->kelasModel->getAllKelasWithGuru();
        }
        
        $this->view('tugas/create', [
            'title' => 'Buat Tugas Baru',
            'kelas' => $kelas,
            'user' => $user
        ]);
    }
    
    /**
     * Process create tugas
     */
    public function store() {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'tugas.php');
        }
        
        $user = getCurrentUser();
        
        $data = [
            'id_kelas' => $this->getPost('id_kelas'),
            'judul' => sanitize($this->getPost('judul')),
            'deskripsi' => sanitize($this->getPost('deskripsi')),
            'tipe' => $this->getPost('tipe', 'essay'),
            'deadline' => $this->getPost('deadline')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['judul'])) {
            $errors[] = 'Judul tugas harus diisi';
        }
        
        if (empty($data['id_kelas'])) {
            $errors[] = 'Kelas harus dipilih';
        }
        
        // Check kelas ownership untuk guru
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($data['id_kelas']);
            if (!$kelas || $kelas['id_guru'] != $user['id']) {
                $errors[] = 'Anda tidak memiliki akses ke kelas ini';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['tugas_errors'] = $errors;
            $_SESSION['old_tugas_input'] = $data;
            $this->redirect(BASE_URL . 'tugas.php?action=create');
        }
        
        // Handle file upload
        $file = $this->getFile('file_lampiran');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($file, 'tugas', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
            if ($upload['success']) {
                $data['file_lampiran'] = $upload['filename'];
            }
        }
        
        // Handle multiple choice options
        if ($data['tipe'] === 'multiple_choice') {
            $opsi = [];
            $soalCount = count($_POST['soal'] ?? []);
            
            for ($i = 0; $i < $soalCount; $i++) {
                if (!empty($_POST['soal'][$i])) {
                    $opsi[] = [
                        'soal' => sanitize($_POST['soal'][$i]),
                        'pilihan' => [
                            sanitize($_POST['pilihan_a'][$i] ?? ''),
                            sanitize($_POST['pilihan_b'][$i] ?? ''),
                            sanitize($_POST['pilihan_c'][$i] ?? ''),
                            sanitize($_POST['pilihan_d'][$i] ?? '')
                        ],
                        'jawaban_benar' => $_POST['jawaban_benar'][$i] ?? 0
                    ];
                }
            }
            
            $data['opsi_jawaban'] = json_encode($opsi);
        }
        
        $tugasId = $this->tugasModel->create($data);
        
        if ($tugasId) {
            // Create notifications for all siswa in kelas
            $this->notifikasiModel->notifyTugasBaru($data['id_kelas'], $data['judul'], $tugasId);
            
            setMessage('success', 'Tugas berhasil dibuat');
            $this->redirect(BASE_URL . 'tugas.php?action=detail&id=' . $tugasId);
        } else {
            setMessage('error', 'Gagal membuat tugas');
            $this->redirect(BASE_URL . 'tugas.php?action=create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        $tugas = $this->tugasModel->getTugasWithKelas($id);
        
        if (!$tugas) {
            setMessage('error', 'Tugas tidak ditemukan');
            $this->redirect(BASE_URL . 'tugas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($tugas['id_kelas']);
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'tugas.php');
            }
        }
        
        if ($user['peran'] === 'guru') {
            $kelasList = $this->kelasModel->getKelasByGuru($user['id']);
        } else {
            $kelasList = $this->kelasModel->getAllKelasWithGuru();
        }
        
        // Decode opsi jawaban jika multiple choice
        if ($tugas['tipe'] === 'multiple_choice' && $tugas['opsi_jawaban']) {
            $tugas['opsi_jawaban'] = json_decode($tugas['opsi_jawaban'], true);
        }
        
        $this->view('tugas/edit', [
            'title' => 'Edit Tugas',
            'tugas' => $tugas,
            'kelas' => $kelasList,
            'user' => $user
        ]);
    }
    
    /**
     * Process update tugas
     */
    public function update($id) {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'tugas.php');
        }
        
        $user = getCurrentUser();
        $tugas = $this->tugasModel->getById($id);
        
        if (!$tugas) {
            setMessage('error', 'Tugas tidak ditemukan');
            $this->redirect(BASE_URL . 'tugas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($tugas['id_kelas']);
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'tugas.php');
            }
        }
        
        $data = [
            'judul' => sanitize($this->getPost('judul')),
            'deskripsi' => sanitize($this->getPost('deskripsi')),
            'deadline' => $this->getPost('deadline')
        ];
        
        // Handle file upload
        $file = $this->getFile('file_lampiran');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            // Delete old file
            if ($tugas['file_lampiran']) {
                deleteFile('tugas/' . $tugas['file_lampiran']);
            }
            
            $upload = uploadFile($file, 'tugas');
            if ($upload['success']) {
                $data['file_lampiran'] = $upload['filename'];
            }
        }
        
        if ($this->tugasModel->update($id, $data)) {
            setMessage('success', 'Tugas berhasil diupdate');
        } else {
            setMessage('error', 'Gagal update tugas');
        }
        
        $this->redirect(BASE_URL . 'tugas.php?action=detail&id=' . $id);
    }
    
    /**
     * Delete tugas
     */
    public function delete($id) {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        $tugas = $this->tugasModel->getById($id);
        
        if (!$tugas) {
            setMessage('error', 'Tugas tidak ditemukan');
            $this->redirect(BASE_URL . 'tugas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($tugas['id_kelas']);
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'tugas.php');
            }
        }
        
        if ($this->tugasModel->deleteTugas($id)) {
            setMessage('success', 'Tugas berhasil dihapus');
        } else {
            setMessage('error', 'Gagal menghapus tugas');
        }
        
        $this->redirect(BASE_URL . 'tugas.php');
    }
    
    /**
     * Submit jawaban (siswa)
     */
    public function submit($id) {
        $this->requireRole('siswa');
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'tugas.php?action=detail&id=' . $id);
        }
        
        $user = getCurrentUser();
        $tugas = $this->tugasModel->getById($id);
        
        if (!$tugas) {
            setMessage('error', 'Tugas tidak ditemukan');
            $this->redirect(BASE_URL . 'tugas.php');
        }
        
        // Check if deadline passed
        if ($this->tugasModel->isExpired($id)) {
            setMessage('error', 'Deadline tugas sudah lewat');
            $this->redirect(BASE_URL . 'tugas.php?action=detail&id=' . $id);
        }
        
        $data = [
            'id_tugas' => $id,
            'id_siswa' => $user['id'],
            'jawaban' => $this->getPost('jawaban')
        ];
        
        // Handle file upload
        $file = $this->getFile('file_jawaban');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($file, 'jawaban');
            if ($upload['success']) {
                $data['file_jawaban'] = $upload['filename'];
            }
        }
        
        // Handle multiple choice
        if ($tugas['tipe'] === 'multiple_choice') {
            $jawaban = $_POST['jawaban_mc'] ?? [];
            $data['jawaban'] = json_encode($jawaban);
        }
        
        if ($this->tugasModel->submitJawaban($data)) {
            // Auto-grade if multiple choice
            if ($tugas['tipe'] === 'multiple_choice') {
                $jawabanRecord = $this->tugasModel->getJawabanSiswa($id, $user['id']);
                $this->tugasModel->autoGradeMultipleChoice($jawabanRecord['id']);
            }
            
            setMessage('success', 'Jawaban berhasil dikirim');
        } else {
            setMessage('error', 'Gagal mengirim jawaban');
        }
        
        $this->redirect(BASE_URL . 'tugas.php?action=detail&id=' . $id);
    }
    
    /**
     * Nilai jawaban (guru)
     */
    public function grade() {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $jawabanId = $this->getPost('jawaban_id');
        $nilai = $this->getPost('nilai');
        $feedback = sanitize($this->getPost('feedback'));
        
        if (!$jawabanId || !is_numeric($nilai)) {
            $this->json(['success' => false, 'message' => 'Data tidak valid'], 400);
        }
        
        if ($this->tugasModel->nilaiJawaban($jawabanId, $nilai, $feedback)) {
            // Get jawaban info
            $jawaban = $this->tugasModel->query(
                "SELECT jt.*, t.judul, t.id as tugas_id 
                 FROM jawaban_tugas jt 
                 JOIN tugas t ON jt.id_tugas = t.id 
                 WHERE jt.id = :id",
                [':id' => $jawabanId]
            );
            
            if (!empty($jawaban)) {
                $info = $jawaban[0];
                
                // Award gamifikasi poin
                $this->gamifikasiModel->awardPoinTugas($info['id_siswa'], $nilai);
                
                // Create notification
                $this->notifikasiModel->notifyNilaiBaru(
                    $info['id_siswa'],
                    $info['judul'],
                    $nilai,
                    $info['tugas_id']
                );
            }
            
            $this->json(['success' => true, 'message' => 'Nilai berhasil disimpan']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan nilai'], 500);
        }
    }
}
?>