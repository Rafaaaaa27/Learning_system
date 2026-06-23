<?php
/**
 * Materi Controller
 * Menangani CRUD materi pembelajaran
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/MateriModel.php';
require_once __DIR__ . '/../models/KelasModel.php';

class MateriController extends Controller {
    
    private $materiModel;
    private $kelasModel;
    
    public function __construct() {
        $this->materiModel = new MateriModel();
        $this->kelasModel = new KelasModel();
    }
    
    /**
     * Upload materi
     */
    public function upload() {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $user = getCurrentUser();
        $kelasId = $this->getPost('id_kelas');
        
        // Check permission
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($kelasId);
            if (!$kelas || $kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'kelas.php');
            }
        }
        
        $data = [
            'id_kelas' => $kelasId,
            'judul' => sanitize($this->getPost('judul')),
            'deskripsi' => sanitize($this->getPost('deskripsi'))
        ];
        
        // Validation
        if (empty($data['judul'])) {
            setMessage('error', 'Judul materi harus diisi');
            $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
        }
        
        $file = $this->getFile('file');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            setMessage('error', 'File harus diupload');
            $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
        }
        
        $result = $this->materiModel->uploadMateri($data, $file);
        
        if ($result['success']) {
            setMessage('success', $result['message']);
        } else {
            setMessage('error', $result['message']);
        }
        
        $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
    }
    
    /**
     * Edit materi
     */
    public function edit($id) {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        $materi = $this->materiModel->getMateriWithKelas($id);
        
        if (!$materi) {
            setMessage('error', 'Materi tidak ditemukan');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($materi['id_kelas']);
            if (!$kelas || $kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'kelas.php');
            }
        }
        
        $this->view('materi/edit', [
            'title' => 'Edit Materi',
            'materi' => $materi,
            'user' => $user
        ]);
    }
    
    /**
     * Update materi
     */
    public function update($id) {
        $this->requireRole(['admin', 'guru']);
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $user = getCurrentUser();
        $materi = $this->materiModel->getById($id);
        
        if (!$materi) {
            setMessage('error', 'Materi tidak ditemukan');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        // Check permission
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($materi['id_kelas']);
            if (!$kelas || $kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'kelas.php');
            }
        }
        
        $data = [
            'judul' => sanitize($this->getPost('judul')),
            'deskripsi' => sanitize($this->getPost('deskripsi'))
        ];
        
        $file = $this->getFile('file');
        
        $result = $this->materiModel->updateMateri($id, $data, $file);
        
        if ($result['success']) {
            setMessage('success', $result['message']);
        } else {
            setMessage('error', $result['message']);
        }
        
        $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $materi['id_kelas']);
    }
    
    /**
     * Delete materi
     */
    public function delete($id) {
        $this->requireRole(['admin', 'guru']);
        
        $user = getCurrentUser();
        $materi = $this->materiModel->getById($id);
        
        if (!$materi) {
            setMessage('error', 'Materi tidak ditemukan');
            $this->redirect(BASE_URL . 'kelas.php');
        }
        
        $kelasId = $materi['id_kelas'];
        
        // Check permission
        if ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($kelasId);
            if (!$kelas || $kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'kelas.php');
            }
        }
        
        $result = $this->materiModel->deleteMateri($id);
        
        if ($result['success']) {
            setMessage('success', $result['message']);
        } else {
            setMessage('error', $result['message']);
        }
        
        $this->redirect(BASE_URL . 'kelas.php?action=detail&id=' . $kelasId);
    }
}
?>