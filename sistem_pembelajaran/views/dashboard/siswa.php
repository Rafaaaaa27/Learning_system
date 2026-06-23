<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Dashboard Siswa</h2>
                    <p class="text-muted mb-0">Selamat datang, <?php echo htmlspecialchars($currentUser['nama']); ?>!</p>
                </div>
                <div>
                    <span class="badge bg-primary fs-5">
                        <i class="fas fa-trophy me-1"></i>
                        Ranking #<?php echo $leaderboard_position; ?>
                    </span>
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
                                    <div class="stat-label">Kelas Diikuti</div>
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
                                    <div class="stat-value text-success"><?php echo $gamifikasi['total_poin']; ?></div>
                                    <div class="stat-label">Total Poin</div>
                                </div>
                                <div>
                                    <i class="fas fa-star fa-2x text-warning"></i>
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
                                    <div class="stat-value text-warning"><?php echo $tugas_belum; ?></div>
                                    <div class="stat-label">Tugas Pending</div>
                                </div>
                                <div>
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
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
                                    <div class="stat-value"><?php echo $rata_rata_nilai; ?></div>
                                    <div class="stat-label">Rata-rata Nilai</div>
                                </div>
                                <div>
                                    <i class="fas fa-chart-line fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gamifikasi Progress -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-area me-2"></i>Progress Nilai</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($chart_labels)): ?>
                            <canvas id="nilaiChart" height="80"></canvas>
                            <script>
                                const ctx = document.getElementById('nilaiChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: <?php echo json_encode($chart_labels); ?>,
                                        datasets: [{
                                            label: 'Nilai',
                                            data: <?php echo json_encode($chart_data); ?>,
                                            backgroundColor: 'rgba(173, 216, 230, 0.2)',
                                            borderColor: '#ADD8E6',
                                            borderWidth: 2,
                                            tension: 0.4,
                                            fill: true
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
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-trophy me-2"></i>Badge Saya</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($gamifikasi['current_badges'])): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($gamifikasi['current_badges'] as $badge): ?>
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-award me-1"></i><?php echo htmlspecialchars($badge); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">Belum ada badge</p>
                            <?php endif; ?>
                            
                            <?php if ($gamifikasi['next_badge']): ?>
                            <hr>
                            <div class="mt-3">
                                <small class="text-muted">Badge Selanjutnya:</small>
                                <p class="mb-1 fw-bold"><?php echo $gamifikasi['next_badge']['name']; ?></p>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $gamifikasi['progress_to_next']; ?>%"
                                         aria-valuenow="<?php echo $gamifikasi['progress_to_next']; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
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
                </div>
            </div>
            
            <!-- Upcoming Deadlines & Kelas -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-clock me-2"></i>Deadline Mendekati</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($upcoming_deadlines)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($upcoming_deadlines, 0, 5) as $deadline): ?>
                                <a href="tugas.php?action=detail&id=<?php echo $deadline['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($deadline['judul']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($deadline['nama_kelas']); ?></small>
                                        </div>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php 
                                            $hours = round((strtotime($deadline['deadline']) - time()) / 3600);
                                            echo $hours . ' jam';
                                            ?>
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>Tidak ada deadline mendekati</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chalkboard me-2"></i>Kelas Saya</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($kelas)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($kelas as $k): ?>
                                <a href="kelas.php?action=detail&id=<?php echo $k['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($k['nama_kelas']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($k['nama_guru']); ?>
                                            </small>
                                        </div>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="kelas.php?action=join" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>Gabung Kelas Baru
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chalkboard fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Anda belum terdaftar di kelas manapun</p>
                                <a href="kelas.php?action=join" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Gabung Kelas
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