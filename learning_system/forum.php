<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser();
$peran = $user['peran'];

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_post') {
        $idKelas = $_POST['id_kelas'];
        $konten = clean($_POST['konten']);
        
        $stmt = $pdo->prepare("INSERT INTO forum_posts (id_kelas, id_user, konten) VALUES (?, ?, ?)");
        if ($stmt->execute([$idKelas, $user['id'], $konten])) {
            // Notify class members
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id FROM users u
                JOIN siswa_kelas sk ON u.id = sk.id_siswa
                WHERE sk.id_kelas = ? AND u.id != ?
            ");
            $stmt->execute([$idKelas, $user['id']]);
            $members = $stmt->fetchAll();
            
            foreach ($members as $member) {
                createNotification($member['id'], "{$user['nama']} memposting di forum", 'pengumuman', "forum.php?kelas=$idKelas");
            }
            
            addPoints($user['id'], 5);
            $success = "Post berhasil dibuat!";
        }
    }
    
    elseif ($action === 'create_reply') {
        $parentId = $_POST['parent_id'];
        $idKelas = $_POST['id_kelas'];
        $konten = clean($_POST['konten']);
        
        $stmt = $pdo->prepare("INSERT INTO forum_posts (id_kelas, id_user, konten, parent_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$idKelas, $user['id'], $konten, $parentId])) {
            // Notify original poster
            $stmt = $pdo->prepare("SELECT id_user FROM forum_posts WHERE id = ?");
            $stmt->execute([$parentId]);
            $originalPoster = $stmt->fetch();
            
            if ($originalPoster && $originalPoster['id_user'] != $user['id']) {
                createNotification($originalPoster['id_user'], "{$user['nama']} membalas post Anda", 'pengumuman', "forum.php?kelas=$idKelas");
            }
            
            addPoints($user['id'], 3);
            $success = "Reply berhasil ditambahkan!";
        }
    }
}

// Get user's classes
$classes = getUserClasses($user['id'], $peran);

// Get selected class
$selectedKelasId = $_GET['kelas'] ?? null;
$selectedKelas = null;
$forumPosts = [];

if ($selectedKelasId) {
    // Verify access
    if ($peran === 'guru') {
        $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ? AND id_guru = ?");
        $stmt->execute([$selectedKelasId, $user['id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT k.* FROM kelas k
            JOIN siswa_kelas sk ON k.id = sk.id_kelas
            WHERE k.id = ? AND sk.id_siswa = ?
        ");
        $stmt->execute([$selectedKelasId, $user['id']]);
    }
    $selectedKelas = $stmt->fetch();
    
    if ($selectedKelas) {
        // Get forum posts
        $stmt = $pdo->prepare("
            SELECT fp.*, u.nama as author_name, u.foto, u.peran as author_role,
            (SELECT COUNT(*) FROM forum_posts WHERE parent_id = fp.id) as reply_count
            FROM forum_posts fp
            JOIN users u ON fp.id_user = u.id
            WHERE fp.id_kelas = ? AND fp.parent_id IS NULL
            ORDER BY fp.created_at DESC
        ");
        $stmt->execute([$selectedKelasId]);
        $forumPosts = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <h2 class="mb-4"><i class="fas fa-comments"></i> Forum Diskusi</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar - Class List -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard"></i> Pilih Kelas
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (empty($classes)): ?>
                            <div class="list-group-item text-muted">Belum ada kelas</div>
                        <?php else: ?>
                            <?php foreach ($classes as $kelas): ?>
                                <a href="forum.php?kelas=<?= $kelas['id'] ?>" 
                                   class="list-group-item list-group-item-action <?= $selectedKelasId == $kelas['id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($kelas['nama_kelas']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <?php if ($selectedKelas): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0"><?= htmlspecialchars($selectedKelas['nama_kelas']) ?></h4>
                            <small class="text-muted">Forum Diskusi</small>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                                <i class="fas fa-plus"></i> Buat Post Baru
                            </button>
                        </div>
                    </div>

                    <!-- Forum Posts -->
                    <?php if (empty($forumPosts)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada post. Jadilah yang pertama berdiskusi!
                        </div>
                    <?php else: ?>
                        <?php foreach ($forumPosts as $post): ?>
                            <div class="forum-post">
                                <div class="d-flex align-items-start mb-3">
                                    <img src="uploads/foto/<?= $post['foto'] ?>" 
                                         onerror="this.src='assets/images/default.jpg'"
                                         class="rounded-circle me-3" 
                                         width="50" height="50" alt="User">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="author"><?= htmlspecialchars($post['author_name']) ?></span>
                                                <span class="badge bg-secondary ms-2"><?= ucfirst($post['author_role']) ?></span>
                                                <br>
                                                <small class="text-muted"><?= timeAgo($post['created_at']) ?></small>
                                            </div>
                                        </div>
                                        <p class="mt-2 mb-2"><?= nl2br(htmlspecialchars($post['konten'])) ?></p>
                                        
                                        <div class="d-flex gap-3">
                                            <button class="btn btn-sm btn-outline-primary" onclick="likePost(<?= $post['id'] ?>)">
                                                <i class="fas fa-thumbs-up"></i> <span id="likes-<?= $post['id'] ?>"><?= $post['likes'] ?></span>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleReply(<?= $post['id'] ?>)">
                                                <i class="fas fa-reply"></i> Reply (<?= $post['reply_count'] ?>)
                                            </button>
                                        </div>

                                        <!-- Reply Form -->
                                        <div id="reply-form-<?= $post['id'] ?>" class="mt-3" style="display: none;">
                                            <form method="POST" class="reply-form">
                                                <input type="hidden" name="action" value="create_reply">
                                                <input type="hidden" name="parent_id" value="<?= $post['id'] ?>">
                                                <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">
                                                <div class="input-group">
                                                    <textarea class="form-control" name="konten" rows="2" placeholder="Tulis reply..." required></textarea>
                                                    <button type="submit" class="btn btn-primary">Kirim</button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Replies -->
                                        <div id="replies-<?= $post['id'] ?>" class="mt-3">
                                            <?php
                                            $stmt = $pdo->prepare("
                                                SELECT fp.*, u.nama as author_name, u.foto, u.peran as author_role
                                                FROM forum_posts fp
                                                JOIN users u ON fp.id_user = u.id
                                                WHERE fp.parent_id = ?
                                                ORDER BY fp.created_at ASC
                                            ");
                                            $stmt->execute([$post['id']]);
                                            $replies = $stmt->fetchAll();
                                            
                                            foreach ($replies as $reply):
                                            ?>
                                                <div class="forum-reply">
                                                    <div class="d-flex align-items-start">
                                                        <img src="uploads/foto/<?= $reply['foto'] ?>" 
                                                             onerror="this.src='assets/images/default.jpg'"
                                                             class="rounded-circle me-2" 
                                                             width="35" height="35" alt="User">
                                                        <div class="flex-grow-1">
                                                            <span class="author small"><?= htmlspecialchars($reply['author_name']) ?></span>
                                                            <span class="badge bg-secondary ms-1" style="font-size: 0.7rem;"><?= ucfirst($reply['author_role']) ?></span>
                                                            <br>
                                                            <small class="text-muted"><?= timeAgo($reply['created_at']) ?></small>
                                                            <p class="mt-1 mb-0 small"><?= nl2br(htmlspecialchars($reply['konten'])) ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                            <h4>Pilih Kelas</h4>
                            <p class="text-muted">Pilih kelas dari sidebar untuk melihat forum diskusi</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Post Modal -->
    <div class="modal fade" id="createPostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Buat Post Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_post">
                        <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Konten</label>
                            <textarea class="form-control" name="konten" rows="5" placeholder="Tulis pertanyaan atau topik diskusi..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <script>
        function toggleReply(postId) {
            $('#reply-form-' + postId).slideToggle();
        }

        function likePost(postId) {
            $.ajax({
                url: 'api/forum.php',
                method: 'POST',
                data: { action: 'like', post_id: postId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#likes-' + postId).text(response.likes);
                    }
                }
            });
        }
    </script>
</body>
</html>