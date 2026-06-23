<?php

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'nama' => $_SESSION['user_nama'],
        'email' => $_SESSION['user_email'],
        'peran' => $_SESSION['user_peran'],
        'foto' => $_SESSION['user_foto'] ?? 'default-avatar.jpg'
    ];
}

// Check user role
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($role)) {
        return in_array($_SESSION['user_peran'], $role);
    }
    
    return $_SESSION['user_peran'] === $role;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit;
    }
}

// Login user
function login($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nama'] = $user['nama'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_peran'] = $user['peran'];
    $_SESSION['user_foto'] = $user['foto'];
}

// Logout user
function logout() {
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

/**
 * Validation Functions
 */

// Sanitize input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    if ($data === null) {
        return null;
    }
    
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validate email
function validateEmail($email) {
    if (empty($email)) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate required fields
function validateRequired($data, $fields) {
    $errors = [];
    
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[$field] = ucfirst($field) . ' harus diisi';
        }
    }
    
    return $errors;
}

/**
 * File Upload Functions
 */

// Upload file
function uploadFile($file, $folder = 'general', $allowedTypes = null) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'File tidak valid'];
    }
    
    // Check error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error uploading file'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File terlalu besar (max 10MB)'];
    }
    
    // Get extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check allowed extensions
    $allowed = $allowedTypes ?? ALLOWED_EXTENSIONS;
    if (!in_array($extension, $allowed)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    // Create upload directory
    $uploadDir = UPLOAD_PATH . $folder . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $targetFile = $uploadDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return [
            'success' => true,
            'filename' => $fileName,
            'path' => $folder . '/' . $fileName,
            'url' => UPLOAD_URL . $folder . '/' . $fileName
        ];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

// Delete file
function deleteFile($path) {
    $fullPath = UPLOAD_PATH . $path;
    
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

/**
 * Date & Time Functions
 */

// Format date
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = 'd M Y H:i') {
    return date($format, strtotime($datetime));
}

// Time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' detik yang lalu';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' menit yang lalu';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' jam yang lalu';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' hari yang lalu';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Message Functions
 */

// Set flash message
function setMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear flash message
function getMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Redirect Functions
 */

// Redirect
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Redirect with message
function redirectWithMessage($url, $type, $message) {
    setMessage($type, $message);
    redirect($url);
}

/**
 * JSON Response
 */

// Send JSON response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Pagination
 */

// Calculate pagination
function paginate($totalItems, $perPage = 10, $currentPage = 1) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_items' => $totalItems,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * String Functions
 */

// Truncate text
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

// Generate random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

/**
 * Debug Functions
 */

// Debug dump
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

// Debug print
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?>