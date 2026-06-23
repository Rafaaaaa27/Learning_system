<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar Filter Kelas -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter me-2"></i>Filter Kelas</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($kelas)): ?>
                    <p class="text-muted">Belum ada kelas</p>
                    <?php if ($user['peran'] === 'siswa'): ?>
                    <a href="kelas.php?action=join" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Gabung Kelas
                    </a>
                    <?php elseif ($user['peran'] === 'guru'): ?>
                    <a href="kelas.php?action=create" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Buat Kelas
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($kelas as $k): ?>
                        <a href="forum.php?kelas_id=<?php echo $k['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $selected_kelas == $k['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($k['nama_kelas']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($stats): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-chart-bar me-2"></i>Statistik Forum</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Total Posts:</strong>
                        <span class="float-end"><?php echo $stats['total_posts']; ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Total Replies:</strong>
                        <span class="float-end"><?php echo $stats['total_replies']; ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Pengguna Aktif:</strong>
                        <span class="float-end"><?php echo $stats['active_users']; ?></span>
                    </div>
                    <div>
                        <strong>Total Likes:</strong>
                        <span class="float-end"><?php echo $stats['total_likes']; ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-comments me-2"></i>Forum Diskusi</h2>
                <?php if ($selected_kelas): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPostModal">
                    <i class="fas fa-plus me-2"></i>Buat Post Baru
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (!$selected_kelas): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Pilih kelas di sidebar untuk melihat forum diskusi
            </div>
            <?php elseif (empty($posts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Belum Ada Diskusi</h4>
                <p class="text-muted">Jadilah yang pertama memulai diskusi di forum ini!</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPostModal">
                    <i class="fas fa-plus me-2"></i>Buat Post Pertama
                </button>
            </div>
            <?php else: ?>
            
            <!-- Posts List -->
            <div class="list-group">
                <?php foreach ($posts as $post): ?>
                <a href="forum.php?action=detail&id=<?php echo $post['id']; ?>" 
                   class="list-group-item list-group-item-action">
                    <div class="d-flex align-items-start">
                        <img src="<?php echo UPLOAD_URL . 'profiles/' . $post['foto']; ?>" 
                             class="avatar me-3" 
                             alt="<?php echo htmlspecialchars($post['nama']); ?>"
                             onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                        
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo nl2br(htmlspecialchars($post['konten'])); ?></h6>
                                    <small class="text-muted">
                                        <strong><?php echo htmlspecialchars($post['nama']); ?></strong>
                                        <?php if ($post['peran'] === 'guru'): ?>
                                        <span class="badge bg-primary">Guru</span>
                                        <?php endif; ?>
                                        • <?php echo timeAgo($post['created_at']); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-reply me-1"></i><?php echo $post['reply_count']; ?> Replies
                                </span>
                                <span class="badge bg-light text-dark ms-2">
                                    <i class="fas fa-heart me-1"></i><?php echo $post['like_count']; ?> Likes
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Buat Post Baru -->
<?php if ($selected_kelas): ?>
<div class="modal fade" id="newPostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Buat Post Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="forum.php?action=create_post" method="POST">
                <input type="hidden" name="kelas_id" value="<?php echo $selected_kelas; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="konten" class="form-label">Konten *</label>
                        <textarea class="form-control" id="konten" name="konten" 
                                  rows="5" placeholder="Tulis pertanyaan atau topik diskusi Anda..." required></textarea>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Tips: Buat judul yang jelas dan deskripsi yang detail untuk mendapatkan respon yang baik
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>