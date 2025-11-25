<?php
/**
 * Helper Functions
 * Fungsi-fungsi pembantu untuk autentikasi, upload, dll
 */

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Check user role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['peran'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Redirect if not specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: dashboard.php');
        exit;
    }
}

// Sanitize input
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Upload file handler
function uploadFile($file, $subfolder = 'materi') {
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'ppt', 'pptx'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File terlalu besar (max 5MB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $destination = UPLOAD_DIR . $subfolder . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

// Create notification
function createNotification($userId, $message, $type, $link = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifikasi (id_user, pesan, type, link) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$userId, $message, $type, $link]);
}

// Update gamifikasi points
function addPoints($userId, $points) {
    global $pdo;
    
    // Check if user exists in gamifikasi table
    $stmt = $pdo->prepare("SELECT * FROM gamifikasi WHERE id_user = ?");
    $stmt->execute([$userId]);
    $gamifikasi = $stmt->fetch();
    
    if ($gamifikasi) {
        $stmt = $pdo->prepare("UPDATE gamifikasi SET poin = poin + ? WHERE id_user = ?");
        $stmt->execute([$points, $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO gamifikasi (id_user, poin, badges) VALUES (?, ?, '[]')");
        $stmt->execute([$userId, $points]);
    }
    
    // Check for badges
    checkBadges($userId);
}

// Check and award badges
function checkBadges($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM gamifikasi WHERE id_user = ?");
    $stmt->execute([$userId]);
    $gamifikasi = $stmt->fetch();
    
    if (!$gamifikasi) return;
    
    $badges = json_decode($gamifikasi['badges'], true) ?: [];
    $newBadges = [];
    
    // Badge logic
    if ($gamifikasi['poin'] >= 100 && !in_array('Pemula', $badges)) {
        $newBadges[] = 'Pemula';
    }
    if ($gamifikasi['poin'] >= 500 && !in_array('Berpengalaman', $badges)) {
        $newBadges[] = 'Berpengalaman';
    }
    if ($gamifikasi['poin'] >= 1000 && !in_array('Ahli', $badges)) {
        $newBadges[] = 'Ahli';
    }
    
    if (!empty($newBadges)) {
        $badges = array_merge($badges, $newBadges);
        $stmt = $pdo->prepare("UPDATE gamifikasi SET badges = ? WHERE id_user = ?");
        $stmt->execute([json_encode($badges), $userId]);
        
        // Notify user
        foreach ($newBadges as $badge) {
            createNotification($userId, "Selamat! Anda mendapatkan badge: $badge", 'pengumuman');
        }
    }
}

// Format date Indonesia
function formatDate($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    return date('d', $timestamp) . ' ' . $bulan[date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

// Time ago function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit yang lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam yang lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari yang lalu';
    
    return formatDate($datetime);
}

// Get user's classes
function getUserClasses($userId, $role) {
    global $pdo;
    
    if ($role === 'guru') {
        $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id_guru = ? ORDER BY nama_kelas");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT k.* FROM kelas k
            JOIN siswa_kelas sk ON k.id = sk.id_kelas
            WHERE sk.id_siswa = ?
            ORDER BY k.nama_kelas
        ");
        $stmt->execute([$userId]);
    }
    
    return $stmt->fetchAll();
}
?>