<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="forum.php?kelas_id=<?php echo $post['id_kelas']; ?>">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Forum
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($post['nama_kelas']); ?></li>
                </ol>
            </nav>
            
            <!-- Main Post -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <img src="<?php echo UPLOAD_URL . 'profiles/' . $post['foto']; ?>" 
                             class="avatar-lg me-3" 
                             alt="<?php echo htmlspecialchars($post['nama']); ?>"
                             onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                        
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($post['nama']); ?></h5>
                                    <?php if ($post['peran'] === 'guru'): ?>
                                    <span class="badge bg-primary">Guru</span>
                                    <?php elseif ($post['peran'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                    <?php endif; ?>
                                    <small class="text-muted d-block mt-1">
                                        <?php echo formatDateTime($post['created_at']); ?>
                                    </small>
                                </div>
                                
                                <?php if ($user['id'] == $post['id_user'] || in_array($user['peran'], ['admin', 'guru'])): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if ($user['id'] == $post['id_user']): ?>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editPostModal">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deletePost(<?php echo $post['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="post-content mb-3">
                                <p><?php echo nl2br(htmlspecialchars($post['konten'])); ?></p>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button class="btn btn-sm btn-outline-danger btn-like-post <?php echo $user_liked ? 'liked' : ''; ?>" 
                                        data-post-id="<?php echo $post['id']; ?>">
                                    <i class="<?php echo $user_liked ? 'fas' : 'far'; ?> fa-heart me-1"></i>
                                    <span class="like-count"><?php echo $post['like_count']; ?></span> Like
                                </button>
                                
                                <span class="text-muted">
                                    <i class="fas fa-reply me-1"></i>
                                    <?php echo $post['reply_count']; ?> Replies
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reply Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-reply me-2"></i>Balas Diskusi</h6>
                </div>
                <div class="card-body">
                    <form action="forum.php?action=create_reply" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="konten" rows="3" 
                                      placeholder="Tulis balasan Anda..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Balasan
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Replies -->
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-comments me-2"></i>Balasan (<?php echo count($replies); ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($replies)): ?>
                    <p class="text-muted text-center py-4">Belum ada balasan</p>
                    <?php else: ?>
                    <?php foreach ($replies as $reply): ?>
                    <div class="reply-item mb-4 pb-4 border-bottom">
                        <div class="d-flex align-items-start">
                            <img src="<?php echo UPLOAD_URL . 'profiles/' . $reply['foto']; ?>" 
                                 class="avatar me-3" 
                                 alt="<?php echo htmlspecialchars($reply['nama']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                            
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($reply['nama']); ?></h6>
                                        <?php if ($reply['peran'] === 'guru'): ?>
                                        <span class="badge bg-primary">Guru</span>
                                        <?php elseif ($reply['peran'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                        <?php endif; ?>
                                        <small class="text-muted d-block mt-1">
                                            <?php echo timeAgo($reply['created_at']); ?>
                                        </small>
                                    </div>
                                    
                                    <?php if ($user['id'] == $reply['id_user'] || in_array($user['peran'], ['admin', 'guru'])): ?>
                                    <button class="btn btn-sm btn-light" onclick="deleteReply(<?php echo $reply['id']; ?>)">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($reply['konten'])); ?></p>
                                
                                <button class="btn btn-sm btn-outline-danger btn-like-post" 
                                        data-post-id="<?php echo $reply['id']; ?>">
                                    <i class="far fa-heart me-1"></i>
                                    <span class="like-count"><?php echo $reply['like_count']; ?></span> Like
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Post -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="forum.php?action=update" method="POST">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_konten" class="form-label">Konten *</label>
                        <textarea class="form-control" id="edit_konten" name="konten" 
                                  rows="5" required><?php echo htmlspecialchars($post['konten']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deletePost(id) {
    if (confirm('Hapus post ini? Semua balasan akan ikut terhapus.')) {
        window.location.href = 'forum.php?action=delete&id=' + id;
    }
}

function deleteReply(id) {
    if (confirm('Hapus balasan ini?')) {
        window.location.href = 'forum.php?action=delete&id=' + id;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>