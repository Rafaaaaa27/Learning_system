<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="<?php echo UPLOAD_URL . 'profiles/' . $profile['foto']; ?>" 
                         class="avatar-lg rounded-circle mb-3" 
                         alt="<?php echo htmlspecialchars($profile['nama']); ?>"
                         onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                    
                    <h4><?php echo htmlspecialchars($profile['nama']); ?></h4>
                    
                    <span class="badge bg-primary mb-3">
                        <?php echo ucfirst($profile['peran']); ?>
                    </span>
                    
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($profile['email']); ?>
                    </p>
                    
                    <?php if ($profile['sekolah']): ?>
                    <p class="text-muted mb-2">
                        <i class="fas fa-school me-2"></i>
                        <?php echo htmlspecialchars($profile['sekolah']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <p class="text-muted small">
                        <i class="fas fa-calendar me-2"></i>
                        Bergabung: <?php echo formatDate($profile['created_at']); ?>
                    </p>
                    
                    <?php if ($profile['id'] == $user['id']): ?>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="profile.php?action=edit" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Profil
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Gamifikasi Card (untuk siswa) -->
            <?php if ($profile['peran'] === 'siswa' && isset($gamifikasi)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-trophy me-2"></i>Gamifikasi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h3 class="text-primary mb-0">
                            <i class="fas fa-star"></i> <?php echo $gamifikasi['total_poin']; ?>
                        </h3>
                        <small class="text-muted">Total Poin</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Ranking:</strong>
                        <span class="badge bg-warning">#<?php echo $gamifikasi['rank']; ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Badges:</strong>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <?php foreach ($gamifikasi['current_badges'] as $badge): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-award"></i> <?php echo $badge; ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <?php if ($gamifikasi['next_badge']): ?>
                    <hr>
                    <div>
                        <small class="text-muted">Badge Selanjutnya:</small>
                        <p class="mb-1 fw-bold"><?php echo $gamifikasi['next_badge']['name']; ?></p>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo $gamifikasi['progress_to_next']; ?>%">
                                <?php echo round($gamifikasi['progress_to_next']); ?>%
                            </div>
                        </div>
                        <small class="text-muted">
                            Butuh <?php echo $gamifikasi['next_badge']['poin_needed']; ?> poin lagi
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Kelas yang Diikuti/Diajar -->
            <?php if (isset($kelas) && !empty($kelas)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-chalkboard me-2"></i>
                        <?php echo $profile['peran'] === 'guru' ? 'Kelas yang Diajar' : 'Kelas yang Diikuti'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($kelas as $k): ?>
                        <a href="kelas.php?action=detail&id=<?php echo $k['id']; ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($k['nama_kelas']); ?></h6>
                                    <?php if ($profile['peran'] === 'siswa' && isset($k['nama_guru'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo htmlspecialchars($k['nama_guru']); ?>
                                    </small>
                                    <?php endif; ?>
                                    <?php if ($profile['peran'] === 'guru' && isset($k['jumlah_siswa'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $k['jumlah_siswa']; ?> Siswa
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Statistik (untuk siswa) -->
            <?php if ($profile['peran'] === 'siswa' && isset($gamifikasi)): ?>
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Statistik</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="fs-2 fw-bold text-primary"><?php echo count($kelas); ?></div>
                                <small class="text-muted">Kelas Diikuti</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="fs-2 fw-bold text-success"><?php echo $gamifikasi['total_badges']; ?></div>
                                <small class="text-muted">Total Badges</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="fs-2 fw-bold text-warning">#<?php echo $gamifikasi['rank']; ?></div>
                                <small class="text-muted">Ranking</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Info untuk Guru -->
            <?php if ($profile['peran'] === 'guru'): ?>
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Informasi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Total Kelas:</strong> <?php echo count($kelas); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="badge bg-success">Aktif</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>