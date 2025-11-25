<?php
/**
 * Notifications API
 * Handle notification operations via AJAX
 */

require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// GET: Fetch notifications
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Get all notifications
        $stmt = $pdo->prepare("
            SELECT * FROM notifikasi
            WHERE id_user = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll();
        
        // Count unread
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM notifikasi
            WHERE id_user = ? AND read_status = 0
        ");
        $stmt->execute([$userId]);
        $unreadCount = $stmt->fetch()['count'];
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// POST: Mark as read
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_read') {
        $notifId = $_POST['id'] ?? 0;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE notifikasi
                SET read_status = 1
                WHERE id = ? AND id_user = ?
            ");
            $stmt->execute([$notifId, $userId]);
            
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    elseif ($action === 'mark_all_read') {
        try {
            $stmt = $pdo->prepare("
                UPDATE notifikasi
                SET read_status = 1
                WHERE id_user = ?
            ");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>