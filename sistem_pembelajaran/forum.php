<?php
/**
 * Forum Page
 * Handler untuk forum diskusi
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/ForumController.php';

// Require login
requireLogin();

// Initialize controller
$controller = new ForumController();

// Get action and ID
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? 0;

switch ($action) {
    case 'index':
        $controller->index();
        break;
    
    case 'detail':
        if (!$id) {
            setMessage('error', 'ID post tidak valid');
            redirect(BASE_URL . 'forum.php');
        }
        $controller->detail($id);
        break;
    
    case 'create_post':
        $controller->createPost();
        break;
    
    case 'create_reply':
        $controller->createReply();
        break;
    
    case 'update':
        $controller->update();
        break;
    
    case 'delete':
        if (!$id) {
            setMessage('error', 'ID post tidak valid');
            redirect(BASE_URL . 'forum.php');
        }
        $controller->delete($id);
        break;
    
    default:
        $controller->index();
}
?>