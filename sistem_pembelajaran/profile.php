<?php
/**
 * Profile Page
 * Handler untuk profil user
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/ProfileController.php';

// Require login
requireLogin();

// Initialize controller
$controller = new ProfileController();

// Get action
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $controller->index();
        break;
    
    case 'edit':
        $controller->edit();
        break;
    
    case 'update':
        $controller->update();
        break;
    
    case 'change_password':
        $controller->changePassword();
        break;
    
    case 'delete':
        $controller->deleteAccount();
        break;
    
    default:
        $controller->index();
}
?>