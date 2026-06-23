<?php

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/DashboardController.php';

// Require login
requireLogin();

// Initialize controller
$controller = new DashboardController();

// Handle action
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $controller->index();
        break;
    
    case 'stats':
        $controller->getStats();
        break;
    
    default:
        $controller->index();
}
?>