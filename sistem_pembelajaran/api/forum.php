<?php
/**
 * API Forum
 * Endpoint untuk operasi forum via AJAX
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/ForumModel.php';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$forumModel = new ForumModel();
$user = getCurrentUser();
$action = $_GET['action'] ?? '';

switch ($action) {
    
    /**
     * Get posts by kelas
     */
    case 'get_posts':
        $kelasId = $_GET['kelas_id'] ?? 0;
        
        if (!$kelasId) {
            jsonResponse(['success' => false, 'message' => 'Kelas ID required'], 400);
        }
        
        $posts = $forumModel->getPostsByKelas($kelasId);
        
        jsonResponse([
            'success' => true,
            'data' => $posts
        ]);
        break;
    
    /**
     * Get post detail
     */
    case 'get_post':
        $postId = $_GET['post_id'] ?? 0;
        
        if (!$postId) {
            jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }
        
        $post = $forumModel->getPostDetail($postId);
        
        if (!$post) {
            jsonResponse(['success' => false, 'message' => 'Post not found'], 404);
        }
        
        jsonResponse([
            'success' => true,
            'data' => $post
        ]);
        break;
    
    /**
     * Get replies for post
     */
    case 'get_replies':
        $postId = $_GET['post_id'] ?? 0;
        
        if (!$postId) {
            jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }
        
        $replies = $forumModel->getReplies($postId);
        
        jsonResponse([
            'success' => true,
            'data' => $replies
        ]);
        break;
    
    /**
     * Create new post
     */
    case 'create_post':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $kelasId = $_POST['kelas_id'] ?? 0;
        $konten = sanitize($_POST['konten'] ?? '');
        
        if (!$kelasId || empty($konten)) {
            jsonResponse(['success' => false, 'message' => 'Kelas ID and content required'], 400);
        }
        
        $data = [
            'id_kelas' => $kelasId,
            'id_user' => $user['id'],
            'konten' => $konten
        ];
        
        $postId = $forumModel->createPost($data);
        
        if ($postId) {
            $post = $forumModel->getPostDetail($postId);
            jsonResponse([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create post'], 500);
        }
        break;
    
    /**
     * Create reply
     */
    case 'reply':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $postId = $_POST['post_id'] ?? 0;
        $konten = sanitize($_POST['konten'] ?? '');
        
        if (!$postId || empty($konten)) {
            jsonResponse(['success' => false, 'message' => 'Post ID and content required'], 400);
        }
        
        // Get parent post to get kelas_id
        $parentPost = $forumModel->getById($postId);
        
        if (!$parentPost) {
            jsonResponse(['success' => false, 'message' => 'Parent post not found'], 404);
        }
        
        $data = [
            'id_kelas' => $parentPost['id_kelas'],
            'id_user' => $user['id'],
            'konten' => $konten,
            'parent_id' => $postId
        ];
        
        $replyId = $forumModel->createPost($data);
        
        if ($replyId) {
            $reply = $forumModel->getById($replyId);
            jsonResponse([
                'success' => true,
                'message' => 'Reply posted successfully',
                'data' => $reply
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to post reply'], 500);
        }
        break;
    
    /**
     * Update post
     */
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $postId = $_POST['post_id'] ?? 0;
        $konten = sanitize($_POST['konten'] ?? '');
        
        if (!$postId || empty($konten)) {
            jsonResponse(['success' => false, 'message' => 'Post ID and content required'], 400);
        }
        
        $result = $forumModel->updatePost($postId, $konten, $user['id']);
        
        jsonResponse($result);
        break;
    
    /**
     * Delete post
     */
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $postId = $_POST['post_id'] ?? 0;
        
        if (!$postId) {
            jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }
        
        $result = $forumModel->deletePost($postId, $user['id'], $user['peran']);
        
        jsonResponse($result);
        break;
    
    /**
     * Toggle like on post
     */
    case 'like':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $postId = $_POST['post_id'] ?? 0;
        
        if (!$postId) {
            jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }
        
        $result = $forumModel->toggleLike($postId, $user['id']);
        
        // Get updated like count
        $post = $forumModel->getById($postId);
        $result['likes'] = $post['likes'];
        
        jsonResponse($result);
        break;
    
    /**
     * Check if user liked post
     */
    case 'check_like':
        $postId = $_GET['post_id'] ?? 0;
        
        if (!$postId) {
            jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }
        
        $liked = $forumModel->hasUserLiked($postId, $user['id']);
        
        jsonResponse([
            'success' => true,
            'liked' => $liked
        ]);
        break;
    
    /**
     * Get recent activity
     */
    case 'recent':
        $kelasId = $_GET['kelas_id'] ?? null;
        $limit = $_GET['limit'] ?? 10;
        
        $activity = $forumModel->getRecentActivity($kelasId, $limit);
        
        jsonResponse([
            'success' => true,
            'data' => $activity
        ]);
        break;
    
    /**
     * Search posts
     */
    case 'search':
        $keyword = $_GET['keyword'] ?? '';
        $kelasId = $_GET['kelas_id'] ?? null;
        
        if (empty($keyword)) {
            jsonResponse(['success' => false, 'message' => 'Keyword required'], 400);
        }
        
        $results = $forumModel->searchPosts($keyword, $kelasId);
        
        jsonResponse([
            'success' => true,
            'data' => $results,
            'count' => count($results)
        ]);
        break;
    
    /**
     * Get forum statistics
     */
    case 'stats':
        $kelasId = $_GET['kelas_id'] ?? 0;
        
        if (!$kelasId) {
            jsonResponse(['success' => false, 'message' => 'Kelas ID required'], 400);
        }
        
        $stats = $forumModel->getStatistik($kelasId);
        
        jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
        break;
    
    default:
        jsonResponse([
            'success' => false,
            'message' => 'Invalid action'
        ], 400);
}
?>