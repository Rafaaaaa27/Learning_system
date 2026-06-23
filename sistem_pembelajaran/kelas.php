<?php
/**
 * Kelas Page
 * Handler untuk semua operasi kelas
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/KelasController.php';

// Require login
requireLogin();

// Initialize controller
$controller = new KelasController();

// Get action and ID
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? 0;

switch ($action) {
    case 'index':
        $controller->index();
        break;
    
    case 'detail':
        if (!$id) {
            setMessage('error', 'ID kelas tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        $controller->detail($id);
        break;
    
    case 'create':
        $controller->create();
        break;
    
    case 'store':
        $controller->store();
        break;
    
    case 'edit':
        if (!$id) {
            setMessage('error', 'ID kelas tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        $controller->edit($id);
        break;
    
    case 'update':
        if (!$id) {
            setMessage('error', 'ID kelas tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        $controller->update($id);
        break;
    
    case 'delete':
        if (!$id) {
            setMessage('error', 'ID kelas tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        $controller->delete($id);
        break;
    
    case 'join':
        $controller->join();
        break;
    
    case 'add_siswa':
        if (!$id) {
            setMessage('error', 'ID kelas tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        $controller->addSiswa($id);
        break;
    
    case 'remove_siswa':
        $siswaId = $_GET['siswa_id'] ?? 0;
        if (!$id || !$siswaId) {
            setMessage('error', 'Parameter tidak valid');
            redirect(BASE_URL . 'kelas.php');
        }
        $controller->removeSiswa($id, $siswaId);
        break;
    
    default:
        $controller->index();
}
?>