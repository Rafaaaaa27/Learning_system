<?php
/**
 * Materi Page
 * Handler untuk operasi materi pembelajaran
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'models/MateriModel.php';

// Require login
requireLogin();

$materiModel = new MateriModel();
$user = getCurrentUser();
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? 0;
$kelasId = $_GET['kelas_id'] ?? $_POST['id_kelas'] ?? 0;

switch ($action) {
    case 'upload':
        // Only guru and admin can upload
        if (!in_array($user['peran'], ['admin', 'guru'])) {
            setMessage('error', 'Anda tidak memiliki akses');
            redirect(BASE_URL . 'kelas.php');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_kelas' => sanitize($_POST['id_kelas']),
                'judul' => sanitize($_POST['judul']),
                'deskripsi' => sanitize($_POST['deskripsi'] ?? '')
            ];
            
            $file = $_FILES['file'] ?? null;
            
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                setMessage('error', 'File harus diupload');
                redirect(BASE_URL . 'kelas.php?action=detail&id=' . $data['id_kelas']);
            }
            
            $result = $materiModel->uploadMateri($data, $file);
            
            if ($result['success']) {
                setMessage('success', 'Materi berhasil diupload');
            } else {
                setMessage('error', $result['message']);
            }
            
            redirect(BASE_URL . 'kelas.php?action=detail&id=' . $data['id_kelas']);
        }
        break;
    
    case 'delete':
        // Only guru and admin can delete
        if (!in_array($user['peran'], ['admin', 'guru'])) {
            setMessage('error', 'Anda tidak memiliki akses');
            redirect(BASE_URL . 'kelas.php');
        }
        
        if (!$id) {
            setMessage('error', 'ID materi tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        
        $materi = $materiModel->getById($id);
        
        if (!$materi) {
            setMessage('error', 'Materi tidak ditemukan');
            redirect(BASE_URL . 'kelas.php');
        }
        
        // Check permission for guru
        if ($user['peran'] === 'guru') {
            require_once 'models/KelasModel.php';
            $kelasModel = new KelasModel();
            $kelas = $kelasModel->getById($materi['id_kelas']);
            
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                redirect(BASE_URL . 'kelas.php');
            }
        }
        
        $result = $materiModel->deleteMateri($id);
        
        if ($result['success']) {
            setMessage('success', 'Materi berhasil dihapus');
        } else {
            setMessage('error', $result['message']);
        }
        
        redirect(BASE_URL . 'kelas.php?action=detail&id=' . ($kelasId ?: $materi['id_kelas']));
        break;
    
    case 'download':
        if (!$id) {
            setMessage('error', 'ID materi tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        
        $materi = $materiModel->getById($id);
        
        if (!$materi || !$materi['file_path']) {
            setMessage('error', 'File tidak ditemukan');
            redirect(BASE_URL . 'kelas.php');
        }
        
        $filePath = UPLOAD_PATH . $materi['file_path'];
        
        if (!file_exists($filePath)) {
            setMessage('error', 'File tidak ditemukan di server');
            redirect(BASE_URL . 'kelas.php');
        }
        
        // Download file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($materi['judul']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
        break;
    
    default:
        redirect(BASE_URL . 'kelas.php');
}
?>