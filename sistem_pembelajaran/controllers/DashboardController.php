<?php
/**
 * Dashboard Controller
 * Menangani dashboard untuk semua role
 */

require_once 'Controller.php';

class DashboardController extends Controller {
    
    private $userModel;
    private $kelasModel;
    private $tugasModel;
    private $gamifikasiModel;
    private $notifikasiModel;
    
    public function __construct() {
        $this->userModel = $this->model('UserModel');
        $this->kelasModel = $this->model('KelasModel');
        $this->tugasModel = $this->model('TugasModel');
        $this->gamifikasiModel = $this->model('GamifikasiModel');
        $this->notifikasiModel = $this->model('NotifikasiModel');
    }
    
    /**
     * Main dashboard
     */
    public function index() {
        $this->requireLogin();
        
        $user = getCurrentUser();
        
        switch ($user['peran']) {
            case 'admin':
                $this->adminDashboard();
                break;
            case 'guru':
                $this->guruDashboard();
                break;
            case 'siswa':
                $this->siswaDashboard();
                break;
            default:
                $this->redirect(BASE_URL . 'login.php');
        }
    }
    
    /**
     * Admin Dashboard
     */
    private function adminDashboard() {
        $data = [
            'title' => 'Dashboard Admin',
            'total_siswa' => $this->userModel->count(['peran' => 'siswa']),
            'total_guru' => $this->userModel->count(['peran' => 'guru']),
            'total_admin' => $this->userModel->count(['peran' => 'admin']),
            'total_kelas' => $this->kelasModel->count(),
            'total_tugas' => $this->tugasModel->count(),
            'recent_users' => $this->userModel->getAll([], 'created_at DESC', 5),
            'recent_kelas' => array_slice($this->kelasModel->getAllKelasWithGuru(), 0, 5)
        ];
        
        // Get statistics per bulan
        $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as bulan,
                COUNT(*) as total
                FROM users 
                WHERE peran = 'siswa'
                GROUP BY bulan
                ORDER BY bulan DESC
                LIMIT 6";
        
        $stats = $this->userModel->query($sql);
        $data['chart_labels'] = array_reverse(array_column($stats, 'bulan'));
        $data['chart_data'] = array_reverse(array_column($stats, 'total'));
        
        $this->view('dashboard/admin', $data);
    }
    
    /**
     * Guru Dashboard
     */
    private function guruDashboard() {
        $user = getCurrentUser();
        
        $kelas = $this->kelasModel->getKelasByGuru($user['id']);
        $kelasIds = array_column($kelas, 'id');
        
        // Get total siswa across all kelas
        $totalSiswa = 0;
        foreach ($kelasIds as $kelasId) {
            $totalSiswa += $this->kelasModel->count(['id_kelas' => $kelasId], 'siswa_kelas');
        }
        
        // Get tugas statistics
        $tugasStats = [];
        foreach ($kelasIds as $kelasId) {
            $tugas = $this->tugasModel->getTugasByKelas($kelasId);
            foreach ($tugas as $t) {
                $tugasStats[] = $this->tugasModel->getStatistikTugas($t['id']);
            }
        }
        
        // Get recent activity
        $recentTugas = [];
        foreach ($kelasIds as $kelasId) {
            $tugas = $this->tugasModel->getTugasByKelas($kelasId, true);
            $recentTugas = array_merge($recentTugas, array_slice($tugas, 0, 5));
        }
        
        $data = [
            'title' => 'Dashboard Guru',
            'total_kelas' => count($kelas),
            'total_siswa' => $totalSiswa,
            'total_tugas' => count($tugasStats),
            'kelas' => $kelas,
            'recent_tugas' => $recentTugas,
            'notifikasi_count' => $this->notifikasiModel->getUnreadCount($user['id'])
        ];
        
        // Chart data: Rata-rata nilai per kelas
        $chartLabels = [];
        $chartData = [];
        foreach ($kelas as $k) {
            $chartLabels[] = substr($k['nama_kelas'], 0, 15);
            $stats = $this->tugasModel->query(
                "SELECT AVG(jt.nilai) as avg_nilai
                 FROM jawaban_tugas jt
                 JOIN tugas t ON jt.id_tugas = t.id
                 WHERE t.id_kelas = :kelasId AND jt.nilai IS NOT NULL",
                [':kelasId' => $k['id']]
            );
            $chartData[] = round($stats[0]['avg_nilai'] ?? 0, 2);
        }
        
        $data['chart_labels'] = $chartLabels;
        $data['chart_data'] = $chartData;
        
        $this->view('dashboard/guru', $data);
    }
    
    /**
     * Siswa Dashboard
     */
    private function siswaDashboard() {
        $user = getCurrentUser();
        
        $kelas = $this->kelasModel->getKelasBySiswa($user['id']);
        $tugas = $this->tugasModel->getTugasForSiswa($user['id']);
        
        // Separate tugas by status
        $tugasBelum = array_filter($tugas, fn($t) => $t['status'] === 'belum');
        $tugasSubmitted = array_filter($tugas, fn($t) => $t['status'] === 'submitted');
        $tugasDinilai = array_filter($tugas, fn($t) => $t['status'] === 'dinilai');
        
        // Get upcoming deadlines
        $upcomingDeadlines = $this->tugasModel->getUpcomingDeadlines($user['id'], 7);
        
        // Get gamifikasi stats
        $gamifikasi = $this->gamifikasiModel->getStatistik($user['id']);
        
        // Get leaderboard position
        $leaderboard = $this->gamifikasiModel->getLeaderboard(null, 100);
        $position = 0;
        foreach ($leaderboard as $index => $item) {
            if ($item['id_user'] == $user['id']) {
                $position = $index + 1;
                break;
            }
        }
        
        $data = [
            'title' => 'Dashboard Siswa',
            'total_kelas' => count($kelas),
            'total_tugas' => count($tugas),
            'tugas_belum' => count($tugasBelum),
            'tugas_selesai' => count($tugasDinilai),
            'kelas' => $kelas,
            'upcoming_deadlines' => $upcomingDeadlines,
            'gamifikasi' => $gamifikasi,
            'leaderboard_position' => $position,
            'notifikasi_count' => $this->notifikasiModel->getUnreadCount($user['id'])
        ];
        
        // Chart data: Progress nilai
        $nilaiData = array_filter(array_column($tugasDinilai, 'nilai'));
        $chartLabels = [];
        $chartData = [];
        
        foreach (array_slice($tugasDinilai, 0, 10) as $t) {
            if ($t['nilai'] !== null) {
                $chartLabels[] = substr($t['judul'], 0, 15);
                $chartData[] = $t['nilai'];
            }
        }
        
        $data['chart_labels'] = array_reverse($chartLabels);
        $data['chart_data'] = array_reverse($chartData);
        $data['rata_rata_nilai'] = !empty($nilaiData) ? round(array_sum($nilaiData) / count($nilaiData), 2) : 0;
        
        $this->view('dashboard/siswa', $data);
    }
    
    /**
     * Get dashboard stats via AJAX
     */
    public function getStats() {
        $this->requireLogin();
        
        if (!$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $user = getCurrentUser();
        
        $stats = [
            'notifikasi_count' => $this->notifikasiModel->getUnreadCount($user['id'])
        ];
        
        if ($user['peran'] === 'siswa') {
            $gamifikasi = $this->gamifikasiModel->getByUserId($user['id']);
            $stats['poin'] = $gamifikasi['poin'];
            $stats['badges'] = json_decode($gamifikasi['badges'], true);
        }
        
        $this->json(['success' => true, 'data' => $stats]);
    }
}
?>