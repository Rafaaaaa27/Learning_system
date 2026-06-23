<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Admin Panel</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="admin.php?page=index" class="list-group-item list-group-item-action active">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                    <a href="admin.php?page=kelas" class="list-group-item list-group-item-action">
                        <i class="fas fa-chalkboard me-2"></i>Manajemen Kelas
                    </a>
                    <a href="admin.php?page=reports" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Laporan
                    </a>
                    <a href="admin.php?page=settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-sliders-h me-2"></i>Pengaturan
                    </a>
                    <a href="admin.php?page=logs" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list me-2"></i>System Logs
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <h2 class="mb-4">Dashboard Admin</h2>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value text-primary"><?php echo $stats['total_users']; ?></div>
                                    <div class="stat-label">Total Users</div>
                                </div>
                                <div>
                                    <i class="fas fa-users fa-3x text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value text-success"><?php echo $stats['total_siswa']; ?></div>
                                    <div class="stat-label">Siswa</div>
                                </div>
                                <div>
                                    <i class="fas fa-user-graduate fa-3x text-success opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value text-info"><?php echo $stats['total_guru']; ?></div>
                                    <div class="stat-label">Guru</div>
                                </div>
                                <div>
                                    <i class="fas fa-chalkboard-teacher fa-3x text-info opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value text-warning"><?php echo $stats['total_admin']; ?></div>
                                    <div class="stat-label">Admin</div>
                                </div>
                                <div>
                                    <i class="fas fa-user-shield fa-3x text-warning opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value"><?php echo $stats['total_kelas']; ?></div>
                                    <div class="stat-label">Total Kelas</div>
                                </div>
                                <div>
                                    <i class="fas fa-school fa-3x text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value"><?php echo $stats['total_tugas']; ?></div>
                                    <div class="stat-label">Total Tugas</div>
                                </div>
                                <div>
                                    <i class="fas fa-tasks fa-3x text-success opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts & Recent Activity -->
            <div class="row g-4">
                <!-- User Growth Chart -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line me-2"></i>Pertumbuhan User</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="userGrowthChart" height="100"></canvas>
                            <script>
                                const labels = <?php echo json_encode(array_column($chart_data, 'bulan')); ?>;
                                const dataSiswa = <?php echo json_encode(array_column($chart_data, 'siswa')); ?>;
                                const dataGuru = <?php echo json_encode(array_column($chart_data, 'guru')); ?>;
                                const dataAdmin = <?php echo json_encode(array_column($chart_data, 'admin')); ?>;
                                
                                const ctx = document.getElementById('userGrowthChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: labels,
                                        datasets: [
                                            {
                                                label: 'Siswa',
                                                data: dataSiswa,
                                                borderColor: '#2ECC71',
                                                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                                                tension: 0.4,
                                                fill: true
                                            },
                                            {
                                                label: 'Guru',
                                                data: dataGuru,
                                                borderColor: '#3498DB',
                                                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                                                tension: 0.4,
                                                fill: true
                                            },
                                            {
                                                label: 'Admin',
                                                data: dataAdmin,
                                                borderColor: '#F39C12',
                                                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                                                tension: 0.4,
                                                fill: true
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top'
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
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle me-2"></i>Statistik Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Siswa Aktif</span>
                                    <strong class="text-success"><?php echo $stats['total_siswa']; ?></strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo ($stats['total_users'] > 0) ? ($stats['total_siswa'] / $stats['total_users'] * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Guru Aktif</span>
                                    <strong class="text-info"><?php echo $stats['total_guru']; ?></strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo ($stats['total_users'] > 0) ? ($stats['total_guru'] / $stats['total_users'] * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Admin</span>
                                    <strong class="text-warning"><?php echo $stats['total_admin']; ?></strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo ($stats['total_users'] > 0) ? ($stats['total_admin'] / $stats['total_users'] * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="text-center">
                                <h4 class="text-primary mb-2"><?php echo $stats['total_kelas']; ?></h4>
                                <small class="text-muted">Total Kelas Tersedia</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Users & Kelas -->
            <div class="row g-4 mt-2">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>User Terbaru</h5>
                            <a href="admin.php?page=users" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo UPLOAD_URL . 'profiles/' . $user['foto']; ?>" 
                                                     class="avatar-sm me-2" alt="">
                                                <?php echo htmlspecialchars($user['nama']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $user['peran'] === 'admin' ? 'warning' : 
                                                        ($user['peran'] === 'guru' ? 'info' : 'success'); 
                                                ?>">
                                                    <?php echo ucfirst($user['peran']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($user['created_at']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama Kelas</th>
                                            <th>Guru</th>
                                            <th>Siswa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_kelas as $kelas): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                            <td><?php echo htmlspecialchars($kelas['nama_guru']); ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo $kelas['jumlah_siswa']; ?> Siswa
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>