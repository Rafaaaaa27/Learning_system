<?php
/**
 * Forum Controller
 * Menangani forum diskusi
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/ForumModel.php';
require_once __DIR__ . '/../models/KelasModel.php';

class ForumController extends Controller {
    
    private $forumModel;
    private $kelasModel;
    
    public function __construct() {
        $this->forumModel = new ForumModel();
        $this->kelasModel = new KelasModel();
    }
    
    /**
     * Show forum index
     */
    public function index() {
        $this->requireLogin();
        
        $user = getCurrentUser();
        
        // Get kelas list based on role
        if ($user['peran'] === 'siswa') {
            $kelas = $this->kelasModel->getKelasBySiswa($user['id']);
        } elseif ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getKelasByGuru($user['id']);
        } else {
            // Admin dapat akses semua kelas
            $kelas = $this->kelasModel->getAllKelasWithGuru();
        }
        
        // Get selected kelas
        $selectedKelas = $_GET['kelas_id'] ?? ($kelas[0]['id'] ?? 0);
        
        $posts = [];
        $stats = null;
        
        if ($selectedKelas) {
            $posts = $this->forumModel->getPostsByKelas($selectedKelas);
            $stats = $this->forumModel->getStatistik($selectedKelas);
        }
        
        $this->view('forum/index', [
            'title' => 'Forum Diskusi',
            'kelas' => $kelas,
            'selected_kelas' => $selectedKelas,
            'posts' => $posts,
            'stats' => $stats,
            'user' => $user
        ]);
    }
    
    /**
     * Show post detail
     */
    public function detail($id) {
        $this->requireLogin();
        
        $user = getCurrentUser();
        $post = $this->forumModel->getPostDetail($id);
        
        if (!$post) {
            setMessage('error', 'Post tidak ditemukan');
            $this->redirect(BASE_URL . 'forum.php');
        }
        
        // Check access - Admin dapat akses semua
        if ($user['peran'] === 'siswa') {
            if (!$this->kelasModel->isSiswaInKelas($post['id_kelas'], $user['id'])) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'forum.php');
            }
        } elseif ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($post['id_kelas']);
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'forum.php');
            }
        }
        // Admin tidak perlu check akses
        
        // Get replies
        $replies = $this->forumModel->getReplies($id);
        
        // Check if user liked
        $userLiked = $this->forumModel->hasUserLiked($id, $user['id']);
        
        $this->view('forum/detail', [
            'title' => 'Forum - ' . substr($post['konten'], 0, 50),
            'post' => $post,
            'replies' => $replies,
            'user_liked' => $userLiked,
            'user' => $user
        ]);
    }
    
    /**
     * Create post
     */
    public function createPost() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'forum.php');
        }
        
        $user = getCurrentUser();
        
        $kelasId = $this->getPost('kelas_id');
        $konten = sanitize($this->getPost('konten'));
        
        if (empty($konten)) {
            setMessage('error', 'Konten tidak boleh kosong');
            $this->redirect(BASE_URL . 'forum.php?kelas_id=' . $kelasId);
        }
        
        // Check access - Admin tidak perlu check
        if ($user['peran'] === 'siswa') {
            if (!$this->kelasModel->isSiswaInKelas($kelasId, $user['id'])) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'forum.php');
            }
        } elseif ($user['peran'] === 'guru') {
            $kelas = $this->kelasModel->getById($kelasId);
            if ($kelas['id_guru'] != $user['id']) {
                setMessage('error', 'Anda tidak memiliki akses');
                $this->redirect(BASE_URL . 'forum.php');
            }
        }
        
        $data = [
            'id_kelas' => $kelasId,
            'id_user' => $user['id'],
            'konten' => $konten
        ];
        
        $postId = $this->forumModel->createPost($data);
        
        if ($postId) {
            setMessage('success', 'Post berhasil dibuat');
            $this->redirect(BASE_URL . 'forum.php?action=detail&id=' . $postId);
        } else {
            setMessage('error', 'Gagal membuat post');
            $this->redirect(BASE_URL . 'forum.php?kelas_id=' . $kelasId);
        }
    }
    
    /**
     * Create reply
     */
    public function createReply() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'forum.php');
        }
        
        $user = getCurrentUser();
        
        $postId = $this->getPost('post_id');
        $konten = sanitize($this->getPost('konten'));
        
        if (empty($konten)) {
            setMessage('error', 'Konten tidak boleh kosong');
            $this->redirect(BASE_URL . 'forum.php?action=detail&id=' . $postId);
        }
        
        $parentPost = $this->forumModel->getById($postId);
        
        if (!$parentPost) {
            setMessage('error', 'Post tidak ditemukan');
            $this->redirect(BASE_URL . 'forum.php');
        }
        
        $data = [
            'id_kelas' => $parentPost['id_kelas'],
            'id_user' => $user['id'],
            'konten' => $konten,
            'parent_id' => $postId
        ];
        
        $replyId = $this->forumModel->createPost($data);
        
        if ($replyId) {
            setMessage('success', 'Reply berhasil ditambahkan');
        } else {
            setMessage('error', 'Gagal menambahkan reply');
        }
        
        $this->redirect(BASE_URL . 'forum.php?action=detail&id=' . $postId);
    }
    
    /**
     * Update post
     */
    public function update() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . 'forum.php');
        }
        
        $user = getCurrentUser();
        $postId = $this->getPost('post_id');
        $konten = sanitize($this->getPost('konten'));
        
        $result = $this->forumModel->updatePost($postId, $konten, $user['id']);
        
        if ($this->isAjax()) {
            $this->json($result);
        }
        
        if ($result['success']) {
            setMessage('success', $result['message']);
        } else {
            setMessage('error', $result['message']);
        }
        
        $this->redirect(BASE_URL . 'forum.php?action=detail&id=' . $postId);
    }
    
    /**
     * Delete post
     */
    public function delete($id) {
        $this->requireLogin();
        
        $user = getCurrentUser();
        $result = $this->forumModel->deletePost($id, $user['id'], $user['peran']);
        
        if ($this->isAjax()) {
            $this->json($result);
        }
        
        if ($result['success']) {
            setMessage('success', $result['message']);
        } else {
            setMessage('error', $result['message']);
        }
        
        $this->redirect(BASE_URL . 'forum.php');
    }
}
?>