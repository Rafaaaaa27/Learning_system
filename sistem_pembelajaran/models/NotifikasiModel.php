<?php
/**
 * Notifikasi Model
 * Menangani sistem notifikasi real-time
 */

require_once 'Model.php';

class NotifikasiModel extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'notifikasi';
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount($userId) {
        return $this->count(['id_user' => $userId, 'read_status' => 0]);
    }
    
    /**
     * Get notifications for user
     */
    public function getNotifications($userId, $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM notifikasi 
                WHERE id_user = :userId 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get unread notifications
     */
    public function getUnreadNotifications($userId, $limit = 10) {
        $sql = "SELECT * FROM notifikasi 
                WHERE id_user = :userId AND read_status = 0
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id) {
        return $this->update($id, ['read_status' => 1]);
    }
    
    /**
     * Mark all as read
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifikasi SET read_status = 1 
                WHERE id_user = :userId AND read_status = 0";
        
        return $this->execute($sql, [':userId' => $userId]);
    }
    
    /**
     * Delete notification
     */
    public function deleteNotification($id, $userId) {
        $notif = $this->getById($id);
        
        if (!$notif || $notif['id_user'] != $userId) {
            return false;
        }
        
        return $this->delete($id);
    }
    
    /**
     * Delete old notifications (older than 30 days)
     */
    public function deleteOldNotifications($userId, $days = 30) {
        $sql = "DELETE FROM notifikasi 
                WHERE id_user = :userId 
                AND created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        return $this->execute($sql, [
            ':userId' => $userId,
            ':days' => $days
        ]);
    }
    
    /**
     * Create notification untuk tugas baru
     */
    public function notifyTugasBaru($kelasId, $tugasJudul, $tugasId) {
        // Get all siswa in kelas
        $sql = "SELECT id_siswa FROM siswa_kelas WHERE id_kelas = :kelasId";
        $siswa = $this->query($sql, [':kelasId' => $kelasId]);
        
        $count = 0;
        foreach ($siswa as $s) {
            $result = $this->create([
                'id_user' => $s['id_siswa'],
                'pesan' => "Tugas baru tersedia: {$tugasJudul}",
                'type' => 'tugas',
                'link' => 'tugas.php?id=' . $tugasId,
                'read_status' => 0
            ]);
            
            if ($result) $count++;
        }
        
        return $count;
    }
    
    /**
     * Create notification untuk deadline
     */
    public function notifyDeadline($userId, $tugasJudul, $tugasId, $hoursLeft) {
        $pesan = "Deadline tugas '{$tugasJudul}' dalam {$hoursLeft} jam!";
        
        return $this->create([
            'id_user' => $userId,
            'pesan' => $pesan,
            'type' => 'deadline',
            'link' => 'tugas.php?id=' . $tugasId,
            'read_status' => 0
        ]);
    }
    
    /**
     * Create notification untuk nilai baru
     */
    public function notifyNilaiBaru($userId, $tugasJudul, $nilai, $tugasId) {
        $pesan = "Tugas '{$tugasJudul}' telah dinilai. Nilai: {$nilai}";
        
        return $this->create([
            'id_user' => $userId,
            'pesan' => $pesan,
            'type' => 'nilai',
            'link' => 'tugas.php?id=' . $tugasId,
            'read_status' => 0
        ]);
    }
    
    /**
     * Create notification untuk forum reply
     */
    public function notifyForumReply($userId, $postId, $replyAuthor) {
        $pesan = "{$replyAuthor} membalas post Anda";
        
        return $this->create([
            'id_user' => $userId,
            'pesan' => $pesan,
            'type' => 'forum',
            'link' => 'forum.php?post_id=' . $postId,
            'read_status' => 0
        ]);
    }
    
    /**
     * Create notification untuk badge baru
     */
    public function notifyBadgeBaru($userId, $badgeName) {
        $pesan = "🎉 Selamat! Anda mendapatkan badge '{$badgeName}'!";
        
        return $this->create([
            'id_user' => $userId,
            'pesan' => $pesan,
            'type' => 'pengumuman',
            'read_status' => 0
        ]);
    }
    
    /**
     * Create bulk notification
     */
    public function createBulkNotification($userIds, $pesan, $type = 'pengumuman', $link = null) {
        $count = 0;
        
        foreach ($userIds as $userId) {
            $result = $this->create([
                'id_user' => $userId,
                'pesan' => $pesan,
                'type' => $type,
                'link' => $link,
                'read_status' => 0
            ]);
            
            if ($result) $count++;
        }
        
        return $count;
    }
    
    /**
     * Get notification statistics
     */
    public function getStatistik($userId) {
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN read_status = 0 THEN 1 END) as unread,
                COUNT(CASE WHEN type = 'tugas' THEN 1 END) as tugas,
                COUNT(CASE WHEN type = 'deadline' THEN 1 END) as deadline,
                COUNT(CASE WHEN type = 'nilai' THEN 1 END) as nilai,
                COUNT(CASE WHEN type = 'forum' THEN 1 END) as forum,
                COUNT(CASE WHEN type = 'pengumuman' THEN 1 END) as pengumuman
                FROM notifikasi
                WHERE id_user = :userId";
        
        $result = $this->query($sql, [':userId' => $userId]);
        return $result[0] ?? null;
    }
    
    /**
     * Check untuk upcoming deadlines dan kirim notifikasi
     */
    public function checkUpcomingDeadlines() {
        // Get tugas dengan deadline dalam 24 jam dan belum ada notifikasi
        $sql = "SELECT t.id, t.judul, t.deadline, jt.id_siswa
                FROM tugas t
                JOIN kelas k ON t.id_kelas = k.id
                JOIN siswa_kelas sk ON k.id = sk.id_kelas
                LEFT JOIN jawaban_tugas jt ON t.id = jt.id_tugas AND sk.id_siswa = jt.id_siswa
                WHERE t.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                AND (jt.id IS NULL OR jt.nilai IS NULL)
                AND NOT EXISTS (
                    SELECT 1 FROM notifikasi n
                    WHERE n.id_user = sk.id_siswa
                    AND n.type = 'deadline'
                    AND n.link LIKE CONCAT('%', t.id)
                    AND n.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                )";
        
        $deadlines = $this->query($sql);
        $count = 0;
        
        foreach ($deadlines as $dl) {
            $hoursLeft = round((strtotime($dl['deadline']) - time()) / 3600);
            $this->notifyDeadline($dl['id_siswa'], $dl['judul'], $dl['id'], $hoursLeft);
            $count++;
        }
        
        return $count;
    }
}
?>