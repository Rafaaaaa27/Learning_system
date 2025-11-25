<?php
/**
 * Forum API
 * Handle forum operations via AJAX
 */

require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Like post
    if ($action === 'like') {
        $postId = $_POST['post_id'] ?? 0;
        
        try {
            // Check if already liked (prevent duplicate likes - optional)
            $stmt = $pdo->prepare("UPDATE forum_posts SET likes = likes + 1 WHERE id = ?");
            $stmt->execute([$postId]);
            
            // Get updated likes count
            $stmt = $pdo->prepare("SELECT likes FROM forum_posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'likes' => $post['likes']
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // Delete post (owner or admin only)
    elseif ($action === 'delete') {
        $postId = $_POST['post_id'] ?? 0;
        
        try {
            // Check ownership or admin
            $stmt = $pdo->prepare("SELECT id_user FROM forum_posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch();
            
            $user = getCurrentUser();
            if ($post && ($post['id_user'] == $userId || $user['peran'] === 'admin')) {
                $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE id = ? OR parent_id = ?");
                $stmt->execute([$postId, $postId]);
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            
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