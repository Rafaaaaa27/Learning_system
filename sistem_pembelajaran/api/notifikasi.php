<?php
/**
 * API Notifikasi
 * Endpoint untuk operasi notifikasi via AJAX
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/NotifikasiModel.php';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$notifModel = new NotifikasiModel();
$user = getCurrentUser();
$action = $_GET['action'] ?? '';

switch ($action) {
    
    /**
     * Get unread notification count
     */
    case 'unread_count':
        $count = $notifModel->getUnreadCount($user['id']);
        jsonResponse([
            'success' => true,
            'count' => $count
        ]);
        break;
    
    /**
     * Get notification list
     */
    case 'list':
        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;
        
        $notifications = $notifModel->getNotifications($user['id'], $limit, $offset);
        
        jsonResponse([
            'success' => true,
            'data' => $notifications,
            'total' => $notifModel->count(['id_user' => $user['id']])
        ]);
        break;
    
    /**
     * Get unread notifications only
     */
    case 'unread':
        $limit = $_GET['limit'] ?? 10;
        $notifications = $notifModel->getUnreadNotifications($user['id'], $limit);
        
        jsonResponse([
            'success' => true,
            'data' => $notifications
        ]);
        break;
    
    /**
     * Mark notification as read
     */
    case 'mark_read':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $id = $_POST['id'] ?? 0;
        
        // Verify ownership
        $notif = $notifModel->getById($id);
        if (!$notif || $notif['id_user'] != $user['id']) {
            jsonResponse(['success' => false, 'message' => 'Invalid notification'], 403);
        }
        
        $result = $notifModel->markAsRead($id);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'Marked as read' : 'Failed to mark as read'
        ]);
        break;
    
    /**
     * Mark all as read
     */
    case 'mark_all_read':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $result = $notifModel->markAllAsRead($user['id']);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'All notifications marked as read' : 'Failed'
        ]);
        break;
    
    /**
     * Delete notification
     */
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $id = $_POST['id'] ?? 0;
        $result = $notifModel->deleteNotification($id, $user['id']);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'Notification deleted' : 'Failed to delete'
        ]);
        break;
    
    /**
     * Get notification statistics
     */
    case 'stats':
        $stats = $notifModel->getStatistik($user['id']);
        
        jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
        break;
    
    /**
     * Create test notification (for development)
     */
    case 'test':
        if (!in_array($user['peran'], ['admin', 'guru'])) {
            jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        $result = $notifModel->create([
            'id_user' => $user['id'],
            'pesan' => 'Test notification at ' . date('Y-m-d H:i:s'),
            'type' => 'pengumuman',
            'read_status' => 0
        ]);
        
        jsonResponse([
            'success' => $result !== false,
            'message' => 'Test notification created'
        ]);
        break;
    
    default:
        jsonResponse([
            'success' => false,
            'message' => 'Invalid action'
        ], 400);
}
?>