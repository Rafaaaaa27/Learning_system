<?php
/**
 * Admin Panel
 * Handler untuk semua operasi admin
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/AdminController.php';

// Require admin role
requireRole('admin');

// Initialize controller
$controller = new AdminController();

// Get page and action
$page = $_GET['page'] ?? 'index';
$id = $_GET['id'] ?? 0;

switch ($page) {
    case 'index':
        $controller->index();
        break;
    
    // User Management
    case 'users':
        $controller->users();
        break;
    
    case 'create_user':
        $controller->createUser();
        break;
    
    case 'store_user':
        $controller->storeUser();
        break;
    
    case 'edit_user':
        if (!$id) {
            setMessage('error', 'ID user tidak valid');
            redirect(BASE_URL . 'admin.php?page=users');
        }
        $controller->editUser($id);
        break;
    
    case 'update_user':
        if (!$id) {
            setMessage('error', 'ID user tidak valid');
            redirect(BASE_URL . 'admin.php?page=users');
        }
        $controller->updateUser($id);
        break;
    
    case 'delete_user':
        if (!$id) {
            setMessage('error', 'ID user tidak valid');
            redirect(BASE_URL . 'admin.php?page=users');
        }
        $controller->deleteUser($id);
        break;
    
    // Kelas Management
    case 'kelas':
        $controller->kelas();
        break;
    
    // Reports
    case 'reports':
        $controller->reports();
        break;
    
    // Settings
    case 'settings':
        $controller->settings();
        break;
    
    // Logs
    case 'logs':
        $controller->logs();
        break;
    
    default:
        $controller->index();
}
?>