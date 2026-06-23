<?php
/**
 * Base Controller Class
 * Semua controller extends dari class ini
 */

abstract class Controller {
    
    /**
     * Load view
     */
    protected function view($viewName, $data = []) {
        // Extract data untuk digunakan sebagai variable di view
        extract($data);
        
        // Get current user
        $currentUser = getCurrentUser();
        
        // Load view file
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View tidak ditemukan: {$viewName}");
        }
    }
    
    /**
     * Load model
     */
    protected function model($modelName) {
        $modelFile = __DIR__ . '/../models/' . $modelName . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $modelName();
        } else {
            die("Model tidak ditemukan: {$modelName}");
        }
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request is GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get POST data
     */
    protected function getPost($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     */
    protected function getGet($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get uploaded file
     */
    protected function getFile($key) {
        return $_FILES[$key] ?? null;
    }
    
    /**
     * Redirect
     */
    protected function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    /**
     * JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Require login
     */
    protected function requireLogin() {
        if (!isLoggedIn()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            } else {
                $this->redirect(BASE_URL . 'login.php');
            }
        }
    }
    
    /**
     * Require role
     */
    protected function requireRole($role) {
        $this->requireLogin();
        
        if (!hasRole($role)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            } else {
                $this->redirect(BASE_URL . 'dashboard.php');
            }
        }
    }
}
?>