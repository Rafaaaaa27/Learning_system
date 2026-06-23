<?php
/**
 * Gamifikasi Model
 * Menangani sistem poin, badge, dan leaderboard
 */

require_once 'Model.php';

class GamifikasiModel extends Model {
    
    // Badge definitions
    private $badges = [
        'Pemula' => ['poin' => 0, 'icon' => 'fa-star', 'color' => 'secondary'],
        'Rajin Belajar' => ['poin' => 100, 'icon' => 'fa-book', 'color' => 'primary'],
        'Juara Kuis' => ['poin' => 250, 'icon' => 'fa-trophy', 'color' => 'warning'],
        'Ahli Diskusi' => ['poin' => 500, 'icon' => 'fa-comments', 'color' => 'info'],
        'Master' => ['poin' => 1000, 'icon' => 'fa-crown', 'color' => 'success'],
        'Legend' => ['poin' => 2000, 'icon' => 'fa-gem', 'color' => 'danger']
    ];
    
    public function __construct() {
        parent::__construct();
        $this->table = 'gamifikasi';
    }
    
    /**
     * Get gamifikasi by user
     */
    public function getByUserId($userId) {
        $data = $this->getOne(['id_user' => $userId]);
        
        // Create if not exists
        if (!$data) {
            $this->create([
                'id_user' => $userId,
                'poin' => 0,
                'badges' => json_encode(['Pemula'])
            ]);
            
            $data = $this->getOne(['id_user' => $userId]);
        }
        
        return $data;
    }
    
    /**
     * Tambah poin
     */
    public function addPoin($userId, $poin, $keterangan = '') {
        $current = $this->getByUserId($userId);
        $newPoin = $current['poin'] + $poin;
        
        // Update poin
        $updated = $this->update($current['id'], ['poin' => $newPoin]);
        
        // Check for new badges
        if ($updated) {
            $this->checkBadges($userId, $newPoin);
            
            // Create notification
            require_once 'NotifikasiModel.php';
            $notifModel = new NotifikasiModel();
            $notifModel->create([
                'id_user' => $userId,
                'pesan' => "Selamat! Kamu mendapatkan {$poin} poin. {$keterangan}",
                'type' => 'pengumuman',
                'read_status' => 0
            ]);
        }
        
        return $updated;
    }
    
    /**
     * Kurangi poin
     */
    public function subtractPoin($userId, $poin) {
        $current = $this->getByUserId($userId);
        $newPoin = max(0, $current['poin'] - $poin);
        
        return $this->update($current['id'], ['poin' => $newPoin]);
    }
    
    /**
     * Check dan unlock badges baru
     */
    private function checkBadges($userId, $currentPoin) {
        $gamifikasi = $this->getByUserId($userId);
        $currentBadges = json_decode($gamifikasi['badges'], true) ?? [];
        $newBadges = [];
        
        foreach ($this->badges as $badgeName => $badgeInfo) {
            if ($currentPoin >= $badgeInfo['poin'] && !in_array($badgeName, $currentBadges)) {
                $newBadges[] = $badgeName;
            }
        }
        
        if (!empty($newBadges)) {
            $allBadges = array_unique(array_merge($currentBadges, $newBadges));
            $this->update($gamifikasi['id'], ['badges' => json_encode($allBadges)]);
            
            // Notification for new badges
            require_once 'NotifikasiModel.php';
            $notifModel = new NotifikasiModel();
            
            foreach ($newBadges as $badge) {
                $notifModel->create([
                    'id_user' => $userId,
                    'pesan' => "🎉 Badge baru terbuka: {$badge}!",
                    'type' => 'pengumuman',
                    'read_status' => 0
                ]);
            }
        }
    }
    
    /**
     * Get leaderboard
     */
    public function getLeaderboard($kelasId = null, $limit = 10) {
        $sql = "SELECT g.*, u.nama, u.foto, u.sekolah";
        
        if ($kelasId) {
            $sql .= " FROM gamifikasi g
                     JOIN users u ON g.id_user = u.id
                     JOIN siswa_kelas sk ON u.id = sk.id_siswa
                     WHERE sk.id_kelas = :kelasId
                     AND u.peran = 'siswa'";
        } else {
            $sql .= " FROM gamifikasi g
                     JOIN users u ON g.id_user = u.id
                     WHERE u.peran = 'siswa'";
        }
        
        $sql .= " ORDER BY g.poin DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        
        if ($kelasId) {
            $stmt->bindValue(':kelasId', $kelasId, PDO::PARAM_INT);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get user rank
     */
    public function getUserRank($userId, $kelasId = null) {
        if ($kelasId) {
            $sql = "SELECT COUNT(*) + 1 as ranking
                    FROM gamifikasi g
                    JOIN users u ON g.id_user = u.id
                    JOIN siswa_kelas sk ON u.id = sk.id_siswa
                    WHERE g.poin > (SELECT poin FROM gamifikasi WHERE id_user = :userId)
                    AND sk.id_kelas = :kelasId
                    AND u.peran = 'siswa'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userId', $userId);
            $stmt->bindValue(':kelasId', $kelasId);
        } else {
            $sql = "SELECT COUNT(*) + 1 as ranking
                    FROM gamifikasi g
                    JOIN users u ON g.id_user = u.id
                    WHERE g.poin > (SELECT poin FROM gamifikasi WHERE id_user = :userId)
                    AND u.peran = 'siswa'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userId', $userId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['ranking'] ?? 0;
    }
    
    /**
     * Get badge info
     */
    public function getBadgeInfo($badgeName) {
        return $this->badges[$badgeName] ?? null;
    }
    
    /**
     * Get all available badges
     */
    public function getAllBadges() {
        return $this->badges;
    }
    
    /**
     * Calculate poin from tugas
     */
    public function calculatePoinFromNilai($nilai) {
        if ($nilai >= 90) return 50;
        if ($nilai >= 80) return 40;
        if ($nilai >= 70) return 30;
        if ($nilai >= 60) return 20;
        return 10; // Participation point
    }
    
    /**
     * Award poin untuk tugas selesai
     */
    public function awardPoinTugas($userId, $nilai) {
        $poin = $this->calculatePoinFromNilai($nilai);
        return $this->addPoin($userId, $poin, "Menyelesaikan tugas dengan nilai {$nilai}");
    }
    
    /**
     * Award poin untuk forum activity
     */
    public function awardPoinForum($userId, $type = 'post') {
        $poin = ($type === 'post') ? 5 : 2; // Post = 5, Reply = 2
        $keterangan = ($type === 'post') ? 'Membuat post baru' : 'Memberikan reply';
        
        return $this->addPoin($userId, $poin, $keterangan);
    }
    
    /**
     * Get statistik gamifikasi
     */
    public function getStatistik($userId) {
        $gamifikasi = $this->getByUserId($userId);
        $badges = json_decode($gamifikasi['badges'], true) ?? [];
        
        return [
            'total_poin' => $gamifikasi['poin'],
            'total_badges' => count($badges),
            'current_badges' => $badges,
            'rank' => $this->getUserRank($userId),
            'next_badge' => $this->getNextBadge($gamifikasi['poin']),
            'progress_to_next' => $this->getProgressToNextBadge($gamifikasi['poin'])
        ];
    }
    
    /**
     * Get next badge to unlock
     */
    private function getNextBadge($currentPoin) {
        foreach ($this->badges as $name => $info) {
            if ($currentPoin < $info['poin']) {
                return [
                    'name' => $name,
                    'poin_required' => $info['poin'],
                    'poin_needed' => $info['poin'] - $currentPoin
                ];
            }
        }
        
        return null; // All badges unlocked
    }
    
    /**
     * Get progress percentage to next badge
     */
    private function getProgressToNextBadge($currentPoin) {
        $nextBadge = $this->getNextBadge($currentPoin);
        
        if (!$nextBadge) {
            return 100; // All badges unlocked
        }
        
        // Find previous badge threshold
        $prevThreshold = 0;
        foreach ($this->badges as $info) {
            if ($info['poin'] < $nextBadge['poin_required'] && $info['poin'] > $prevThreshold) {
                $prevThreshold = $info['poin'];
            }
        }
        
        $range = $nextBadge['poin_required'] - $prevThreshold;
        $progress = $currentPoin - $prevThreshold;
        
        return ($progress / $range) * 100;
    }
    
    /**
     * Reset poin (untuk testing atau periode baru)
     */
    public function resetPoin($userId) {
        $gamifikasi = $this->getByUserId($userId);
        
        return $this->update($gamifikasi['id'], [
            'poin' => 0,
            'badges' => json_encode(['Pemula'])
        ]);
    }
}
?>