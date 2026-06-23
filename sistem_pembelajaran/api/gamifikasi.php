<?php
/**
 * API Gamifikasi
 * Endpoint untuk sistem gamifikasi via AJAX
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/GamifikasiModel.php';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$gamifikasiModel = new GamifikasiModel();
$user = getCurrentUser();
$action = $_GET['action'] ?? '';

switch ($action) {
    
    /**
     * Get user poin
     */
    case 'get_poin':
        $userId = $_GET['user_id'] ?? $user['id'];
        
        // Check permission
        if ($userId != $user['id'] && !in_array($user['peran'], ['admin', 'guru'])) {
            jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        $gamifikasi = $gamifikasiModel->getByUserId($userId);
        
        jsonResponse([
            'success' => true,
            'poin' => $gamifikasi['poin'],
            'badges' => json_decode($gamifikasi['badges'], true)
        ]);
        break;
    
    /**
     * Get leaderboard
     */
    case 'leaderboard':
        $kelasId = $_GET['kelas_id'] ?? null;
        $limit = $_GET['limit'] ?? 10;
        
        $leaderboard = $gamifikasiModel->getLeaderboard($kelasId, $limit);
        
        jsonResponse([
            'success' => true,
            'data' => $leaderboard
        ]);
        break;
    
    /**
     * Get user rank
     */
    case 'get_rank':
        $userId = $_GET['user_id'] ?? $user['id'];
        $kelasId = $_GET['kelas_id'] ?? null;
        
        $rank = $gamifikasiModel->getUserRank($userId, $kelasId);
        
        jsonResponse([
            'success' => true,
            'rank' => $rank
        ]);
        break;
    
    /**
     * Get all badges info
     */
    case 'badges':
        $badges = $gamifikasiModel->getAllBadges();
        
        jsonResponse([
            'success' => true,
            'data' => $badges
        ]);
        break;
    
    /**
     * Get user statistics
     */
    case 'stats':
        $userId = $_GET['user_id'] ?? $user['id'];
        
        // Check permission
        if ($userId != $user['id'] && !in_array($user['peran'], ['admin', 'guru'])) {
            jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        $stats = $gamifikasiModel->getStatistik($userId);
        
        jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
        break;
    
    /**
     * Add poin (admin/guru only)
     */
    case 'add_poin':
        if (!in_array($user['peran'], ['admin', 'guru'])) {
            jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $userId = $_POST['user_id'] ?? 0;
        $poin = $_POST['poin'] ?? 0;
        $keterangan = $_POST['keterangan'] ?? '';
        
        if (!$userId || !$poin) {
            jsonResponse(['success' => false, 'message' => 'User ID and poin required'], 400);
        }
        
        $result = $gamifikasiModel->addPoin($userId, $poin, $keterangan);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'Poin added successfully' : 'Failed to add poin'
        ]);
        break;
    
    /**
     * Subtract poin (admin only)
     */
    case 'subtract_poin':
        if ($user['peran'] !== 'admin') {
            jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $userId = $_POST['user_id'] ?? 0;
        $poin = $_POST['poin'] ?? 0;
        
        if (!$userId || !$poin) {
            jsonResponse(['success' => false, 'message' => 'User ID and poin required'], 400);
        }
        
        $result = $gamifikasiModel->subtractPoin($userId, $poin);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'Poin subtracted successfully' : 'Failed to subtract poin'
        ]);
        break;
    
    /**
     * Reset poin (admin only)
     */
    case 'reset_poin':
        if ($user['peran'] !== 'admin') {
            jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $userId = $_POST['user_id'] ?? 0;
        
        if (!$userId) {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        
        $result = $gamifikasiModel->resetPoin($userId);
        
        jsonResponse([
            'success' => $result,
            'message' => $result ? 'Poin reset successfully' : 'Failed to reset poin'
        ]);
        break;
    
    /**
     * Get leaderboard with filters
     */
    case 'leaderboard_advanced':
        $kelasId = $_GET['kelas_id'] ?? null;
        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;
        
        $leaderboard = $gamifikasiModel->getLeaderboard($kelasId, $limit);
        
        // Add additional info
        foreach ($leaderboard as &$item) {
            $item['badges'] = json_decode($item['badges'], true);
            $item['rank'] = $gamifikasiModel->getUserRank($item['id_user'], $kelasId);
        }
        
        jsonResponse([
            'success' => true,
            'data' => $leaderboard,
            'filters' => [
                'kelas_id' => $kelasId,
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);
        break;
    
    /**
     * Get badge progress
     */
    case 'badge_progress':
        $userId = $_GET['user_id'] ?? $user['id'];
        
        $gamifikasi = $gamifikasiModel->getByUserId($userId);
        $allBadges = $gamifikasiModel->getAllBadges();
        $currentBadges = json_decode($gamifikasi['badges'], true) ?? [];
        
        $progress = [];
        foreach ($allBadges as $name => $info) {
            $progress[] = [
                'name' => $name,
                'icon' => $info['icon'],
                'color' => $info['color'],
                'poin_required' => $info['poin'],
                'unlocked' => in_array($name, $currentBadges),
                'progress' => min(100, ($gamifikasi['poin'] / $info['poin']) * 100)
            ];
        }
        
        jsonResponse([
            'success' => true,
            'data' => $progress,
            'current_poin' => $gamifikasi['poin']
        ]);
        break;
    
    /**
     * Get top performers
     */
    case 'top_performers':
        $kelasId = $_GET['kelas_id'] ?? null;
        $period = $_GET['period'] ?? 'all'; // all, week, month
        
        $leaderboard = $gamifikasiModel->getLeaderboard($kelasId, 5);
        
        jsonResponse([
            'success' => true,
            'data' => $leaderboard,
            'period' => $period
        ]);
        break;
    
    default:
        jsonResponse([
            'success' => false,
            'message' => 'Invalid action'
        ], 400);
}
?>