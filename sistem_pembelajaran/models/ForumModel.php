<?php
/**
 * Forum Model
 * Menangani forum diskusi dengan reply dan like system
 */

require_once 'Model.php';

class ForumModel extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'forum_posts';
    }
    
    /**
     * Get posts by kelas
     */
    public function getPostsByKelas($kelasId) {
        $sql = "SELECT fp.*, u.nama, u.foto, u.peran,
                (SELECT COUNT(*) FROM forum_posts WHERE parent_id = fp.id) as reply_count,
                (SELECT COUNT(*) FROM forum_likes WHERE id_post = fp.id) as like_count
                FROM forum_posts fp
                JOIN users u ON fp.id_user = u.id
                WHERE fp.id_kelas = :kelasId AND fp.parent_id IS NULL
                ORDER BY fp.created_at DESC";
        
        return $this->query($sql, [':kelasId' => $kelasId]);
    }
    
    /**
     * Get post detail
     */
    public function getPostDetail($postId) {
        $sql = "SELECT fp.*, u.nama, u.foto, u.peran, k.nama_kelas,
                (SELECT COUNT(*) FROM forum_posts WHERE parent_id = fp.id) as reply_count,
                (SELECT COUNT(*) FROM forum_likes WHERE id_post = fp.id) as like_count
                FROM forum_posts fp
                JOIN users u ON fp.id_user = u.id
                JOIN kelas k ON fp.id_kelas = k.id
                WHERE fp.id = :postId";
        
        $result = $this->query($sql, [':postId' => $postId]);
        return $result[0] ?? null;
    }
    
    /**
     * Get replies for post
     */
    public function getReplies($postId) {
        $sql = "SELECT fp.*, u.nama, u.foto, u.peran,
                (SELECT COUNT(*) FROM forum_likes WHERE id_post = fp.id) as like_count
                FROM forum_posts fp
                JOIN users u ON fp.id_user = u.id
                WHERE fp.parent_id = :postId
                ORDER BY fp.created_at ASC";
        
        return $this->query($sql, [':postId' => $postId]);
    }
    
    /**
     * Create post
     */
    public function createPost($data) {
        $id = $this->create($data);
        
        if ($id) {
            // Award gamifikasi poin
            if (!isset($data['parent_id']) || $data['parent_id'] === null) {
                // Main post
                require_once 'GamifikasiModel.php';
                $gamifikasiModel = new GamifikasiModel();
                $gamifikasiModel->awardPoinForum($data['id_user'], 'post');
            } else {
                // Reply
                require_once 'GamifikasiModel.php';
                $gamifikasiModel = new GamifikasiModel();
                $gamifikasiModel->awardPoinForum($data['id_user'], 'reply');
            }
            
            // Create notification untuk kelas members
            $this->notifyKelasMembers($data['id_kelas'], $data['id_user'], 'New forum post');
        }
        
        return $id;
    }
    
    /**
     * Update post
     */
    public function updatePost($id, $konten, $userId) {
        $post = $this->getById($id);
        
        if (!$post) {
            return ['success' => false, 'message' => 'Post tidak ditemukan'];
        }
        
        // Check ownership
        if ($post['id_user'] != $userId) {
            return ['success' => false, 'message' => 'Anda tidak memiliki akses'];
        }
        
        if ($this->update($id, ['konten' => $konten])) {
            return ['success' => true, 'message' => 'Post berhasil diupdate'];
        }
        
        return ['success' => false, 'message' => 'Gagal update post'];
    }
    
    /**
     * Delete post
     */
    public function deletePost($id, $userId, $userRole) {
        $post = $this->getById($id);
        
        if (!$post) {
            return ['success' => false, 'message' => 'Post tidak ditemukan'];
        }
        
        // Check permission: owner or admin/guru
        if ($post['id_user'] != $userId && !in_array($userRole, ['admin', 'guru'])) {
            return ['success' => false, 'message' => 'Anda tidak memiliki akses'];
        }
        
        if ($this->delete($id)) {
            return ['success' => true, 'message' => 'Post berhasil dihapus'];
        }
        
        return ['success' => false, 'message' => 'Gagal menghapus post'];
    }
    
    /**
     * Like/Unlike post
     */
    public function toggleLike($postId, $userId) {
        // Check if already liked
        $sql = "SELECT id FROM forum_likes WHERE id_post = :postId AND id_user = :userId";
        $existing = $this->query($sql, [':postId' => $postId, ':userId' => $userId]);
        
        if (!empty($existing)) {
            // Unlike
            $this->execute(
                "DELETE FROM forum_likes WHERE id_post = :postId AND id_user = :userId",
                [':postId' => $postId, ':userId' => $userId]
            );
            
            $this->execute(
                "UPDATE forum_posts SET likes = likes - 1 WHERE id = :postId",
                [':postId' => $postId]
            );
            
            return ['success' => true, 'liked' => false, 'message' => 'Unlike'];
        } else {
            // Like
            $this->execute(
                "INSERT INTO forum_likes (id_post, id_user) VALUES (:postId, :userId)",
                [':postId' => $postId, ':userId' => $userId]
            );
            
            $this->execute(
                "UPDATE forum_posts SET likes = likes + 1 WHERE id = :postId",
                [':postId' => $postId]
            );
            
            return ['success' => true, 'liked' => true, 'message' => 'Liked'];
        }
    }
    
    /**
     * Check if user liked post
     */
    public function hasUserLiked($postId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM forum_likes WHERE id_post = :postId AND id_user = :userId";
        $result = $this->query($sql, [':postId' => $postId, ':userId' => $userId]);
        
        return $result[0]['count'] > 0;
    }
    
    /**
     * Get recent activity
     */
    public function getRecentActivity($kelasId = null, $limit = 10) {
        $sql = "SELECT fp.*, u.nama, u.foto, k.nama_kelas,
                CASE WHEN fp.parent_id IS NULL THEN 'post' ELSE 'reply' END as type
                FROM forum_posts fp
                JOIN users u ON fp.id_user = u.id
                JOIN kelas k ON fp.id_kelas = k.id";
        
        $params = [];
        
        if ($kelasId) {
            $sql .= " WHERE fp.id_kelas = :kelasId";
            $params[':kelasId'] = $kelasId;
        }
        
        $sql .= " ORDER BY fp.created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Search posts
     */
    public function searchPosts($keyword, $kelasId = null) {
        $sql = "SELECT fp.*, u.nama, u.foto, k.nama_kelas
                FROM forum_posts fp
                JOIN users u ON fp.id_user = u.id
                JOIN kelas k ON fp.id_kelas = k.id
                WHERE fp.konten LIKE :keyword";
        
        $params = [':keyword' => "%{$keyword}%"];
        
        if ($kelasId) {
            $sql .= " AND fp.id_kelas = :kelasId";
            $params[':kelasId'] = $kelasId;
        }
        
        $sql .= " ORDER BY fp.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get statistik forum
     */
    public function getStatistik($kelasId) {
        $sql = "SELECT 
                COUNT(CASE WHEN parent_id IS NULL THEN 1 END) as total_posts,
                COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as total_replies,
                COUNT(DISTINCT id_user) as active_users,
                SUM(likes) as total_likes
                FROM forum_posts
                WHERE id_kelas = :kelasId";
        
        $result = $this->query($sql, [':kelasId' => $kelasId]);
        return $result[0] ?? null;
    }
    
    /**
     * Notify kelas members about new post
     */
    private function notifyKelasMembers($kelasId, $authorId, $message) {
        require_once 'NotifikasiModel.php';
        $notifModel = new NotifikasiModel();
        
        // Get all members except author
        $sql = "SELECT id_siswa FROM siswa_kelas WHERE id_kelas = :kelasId AND id_siswa != :authorId";
        $members = $this->query($sql, [':kelasId' => $kelasId, ':authorId' => $authorId]);
        
        foreach ($members as $member) {
            $notifModel->create([
                'id_user' => $member['id_siswa'],
                'pesan' => $message,
                'type' => 'forum',
                'link' => 'forum.php?kelas_id=' . $kelasId,
                'read_status' => 0
            ]);
        }
    }
}
?>