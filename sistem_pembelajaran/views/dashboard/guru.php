<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Dashboard Guru</h2>
                    <p class="text-muted mb-0">Selamat datang, <?php echo htmlspecialchars($currentUser['nama']); ?>!</p>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value"><?php echo $total_kelas; ?></div>
                                    <div class="stat-label">Kelas Diajar</div>
                                </div>
                                <div>
                                    <i class="fas fa-chalkboard fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-success"><?php echo $total_siswa; ?></div>
                                    <div class="stat-label">Total Siswa</div>
                                </div>
                                <div>
                                    <i class="fas fa-users fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-warning"><?php echo $total_tugas; ?></div>
                                    <div class="stat-label">Total Tugas</div>
                                </div>
                                <div>
                                    <i class="fas fa-tasks fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-danger"><?php echo $notifikasi_count; ?></div>
                                    <div class="stat-label">Notifikasi</div>
                                </div>
                                <div>
                                    <i class="fas fa-bell fa-2x text-danger"></i>
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
                            <h5><i class="fas fa-chart-line me-2"></i>Rata-rata Nilai Per Kelas</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($chart_labels)): ?>
                            <canvas id="nilaiChart" height="80"></canvas>
                            <script>
                                const ctx = document.getElementById('nilaiChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: <?php echo json_encode($chart_labels); ?>,
                                        datasets: [{
                                            label: 'Rata-rata Nilai',
                                            data: <?php echo json_encode($chart_data); ?>,
                                            backgroundColor: 'rgba(173, 216, 230, 0.6)',
                                            borderColor: '#ADD8E6',
                                            borderWidth: 2
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: {
                                                display: false
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 100
                                            }
                                        }
                                    }
                                });
                            </script>
                            <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <p>Belum ada data nilai</p>
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
                                <a href="kelas.php?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Buat Kelas Baru
                                </a>
                                <a href="tugas.php?action=create" class="btn btn-success">
                                    <i class="fas fa-tasks me-2"></i>Buat Tugas Baru
                                </a>
                                <a href="forum.php" class="btn btn-info">
                                    <i class="fas fa-comments me-2"></i>Forum Diskusi
                                </a>
                                <a href="<?php echo BASE_URL; ?>materi.php" class="btn btn-warning">
                                    <i class="fas fa-book me-2"></i>Upload Materi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Kelas & Recent Tugas -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>Kelas Saya</h5>
                            <a href="kelas.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($kelas)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($kelas as $k): ?>
                                <a href="kelas.php?action=detail&id=<?php echo $k['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($k['nama_kelas']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>
                                                <?php echo $k['jumlah_siswa']; ?> Siswa
                                            </small>
                                        </div>
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($k['kode_kelas']); ?>
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chalkboard fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Anda belum membuat kelas</p>
                                <a href="kelas.php?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Buat Kelas
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Tugas Terbaru</h5>
                            <a href="tugas.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recent_tugas)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_tugas as $tugas): ?>
                                <a href="tugas.php?action=detail&id=<?php echo $tugas['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($tugas['judul']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo formatDateTime($tugas['deadline']); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?php echo $tugas['tipe'] === 'essay' ? 'info' : 'success'; ?>">
                                            <?php echo $tugas['total_jawaban']; ?> Jawaban
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada tugas</p>
                                <a href="tugas.php?action=create" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Buat Tugas
                                </a>
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