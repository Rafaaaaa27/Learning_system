<?php
/**
 * Logout Handler
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();
?>