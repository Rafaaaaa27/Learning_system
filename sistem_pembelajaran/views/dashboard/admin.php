<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Dashboard Admin</h2>
                    <p class="text-muted mb-0">Selamat datang, <?php echo htmlspecialchars($currentUser['nama']); ?>!</p>
                </div>
                <div>
                    <a href="admin.php" class="btn btn-primary">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </a>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-success"><?php echo $total_siswa; ?></div>
                                    <div class="stat-label">Total Siswa</div>
                                </div>
                                <div>
                                    <i class="fas fa-user-graduate fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-info"><?php echo $total_guru; ?></div>
                                    <div class="stat-label">Total Guru</div>
                                </div>
                                <div>
                                    <i class="fas fa-chalkboard-teacher fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-warning"><?php echo $total_admin; ?></div>
                                    <div class="stat-label">Total Admin</div>
                                </div>
                                <div>
                                    <i class="fas fa-user-shield fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value"><?php echo $total_kelas; ?></div>
                                    <div class="stat-label">Total Kelas</div>
                                </div>
                                <div>
                                    <i class="fas fa-school fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart & Quick Actions -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line me-2"></i>Pertumbuhan Siswa</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($chart_labels)): ?>
                            <canvas id="siswaChart" height="80"></canvas>
                            <script>
                                const ctx = document.getElementById('siswaChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: <?php echo json_encode($chart_labels); ?>,
                                        datasets: [{
                                            label: 'Jumlah Siswa',
                                            data: <?php echo json_encode($chart_data); ?>,
                                            backgroundColor: 'rgba(46, 204, 113, 0.2)',
                                            borderColor: '#2ECC71',
                                            borderWidth: 2,
                                            tension: 0.4,
                                            fill: true
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                display: false
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            </script>
                            <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <p>Belum ada data</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="admin.php?page=create_user" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Tambah User
                                </a>
                                <a href="admin.php?page=users" class="btn btn-info">
                                    <i class="fas fa-users me-2"></i>Kelola User
                                </a>
                                <a href="admin.php?page=kelas" class="btn btn-success">
                                    <i class="fas fa-chalkboard me-2"></i>Kelola Kelas
                                </a>
                                <a href="admin.php?page=reports" class="btn btn-warning">
                                    <i class="fas fa-chart-bar me-2"></i>Lihat Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>User Terbaru</h5>
                            <a href="admin.php?page=users" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recent_users)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_users as $user): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo UPLOAD_URL . 'profiles/' . $user['foto']; ?>" 
                                             class="avatar me-3" alt="">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($user['nama']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                        <span class="badge bg-<?php 
                                            echo $user['peran'] === 'admin' ? 'warning' : 
                                                ($user['peran'] === 'guru' ? 'info' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($user['peran']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <p>Belum ada user</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>Kelas Terbaru</h5>
                            <a href="admin.php?page=kelas" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recent_kelas)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_kelas as $kelas): ?>
                                <a href="kelas.php?action=detail&id=<?php echo $kelas['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($kelas['nama_guru']); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-primary">
                                            <?php echo $kelas['jumlah_siswa']; ?> Siswa
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <p>Belum ada kelas</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>