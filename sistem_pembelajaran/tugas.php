<?php
/**
 * Tugas Page
 * Handler untuk semua operasi tugas
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/TugasController.php';

// Require login
requireLogin();

// Initialize controller
$controller = new TugasController();

// Get action and ID
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? 0;

switch ($action) {
    case 'index':
        $controller->index();
        break;
    
    case 'detail':
        if (!$id) {
            setMessage('error', 'ID tugas tidak valid');
            redirect(BASE_URL . 'tugas.php');
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
            setMessage('error', 'ID tugas tidak valid');
            redirect(BASE_URL . 'tugas.php');
        }
        $controller->edit($id);
        break;
    
    case 'update':
        if (!$id) {
            setMessage('error', 'ID tugas tidak valid');
            redirect(BASE_URL . 'tugas.php');
        }
        $controller->update($id);
        break;
    
    case 'delete':
        if (!$id) {
            setMessage('error', 'ID tugas tidak valid');
            redirect(BASE_URL . 'tugas.php');
        }
        $controller->delete($id);
        break;
    
    case 'submit':
        if (!$id) {
            setMessage('error', 'ID tugas tidak valid');
            redirect(BASE_URL . 'tugas.php');
        }
        $controller->submit($id);
        break;
    
    case 'grade':
        $controller->grade();
        break;
    
    default:
        $controller->index();
}
?>