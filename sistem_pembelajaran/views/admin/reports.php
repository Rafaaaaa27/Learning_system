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
                    <a href="admin.php?page=index" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                    <a href="admin.php?page=users" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Manajemen User
                    </a>
                    <a href="admin.php?page=kelas" class="list-group-item list-group-item-action">
                        <i class="fas fa-chalkboard me-2"></i>Manajemen Kelas
                    </a>
                    <a href="admin.php?page=reports" class="list-group-item list-group-item-action active">
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
            <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Laporan Sistem</h2>
            
            <!-- Summary Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Total User</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-primary mb-0"><?php echo $stats['users']['total']; ?></h2>
                                    <small class="text-muted">Pengguna terdaftar</small>
                                </div>
                                <i class="fas fa-users fa-3x text-primary opacity-25"></i>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-4">
                                    <strong><?php echo $stats['users']['admin']; ?></strong>
                                    <br><small class="text-muted">Admin</small>
                                </div>
                                <div class="col-4">
                                    <strong><?php echo $stats['users']['guru']; ?></strong>
                                    <br><small class="text-muted">Guru</small>
                                </div>
                                <div class="col-4">
                                    <strong><?php echo $stats['users']['siswa']; ?></strong>
                                    <br><small class="text-muted">Siswa</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Kelas</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-success mb-0"><?php echo $stats['kelas']['total']; ?></h2>
                                    <small class="text-muted">Total kelas</small>
                                </div>
                                <i class="fas fa-chalkboard fa-3x text-success opacity-25"></i>
                            </div>
                            <hr>
                            <div class="text-center">
                                <strong class="text-success"><?php echo $stats['kelas']['active']; ?></strong>
                                <br><small class="text-muted">Kelas Aktif</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Tugas</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-info mb-0"><?php echo $stats['tugas']['total']; ?></h2>
                                    <small class="text-muted">Total tugas</small>
                                </div>
                                <i class="fas fa-tasks fa-3x text-info opacity-25"></i>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <strong><?php echo $stats['tugas']['essay']; ?></strong>
                                    <br><small class="text-muted">Essay</small>
                                </div>
                                <div class="col-6">
                                    <strong><?php echo $stats['tugas']['multiple_choice']; ?></strong>
                                    <br><small class="text-muted">Pilihan Ganda</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Students -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 10 Siswa Terbaik</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ranking</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Sekolah</th>
                                    <th>Poin</th>
                                    <th>Badges</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_students)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada data siswa
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($top_students as $index => $student): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $index === 0 ? 'warning' : 
                                                    ($index === 1 ? 'secondary' : 
                                                    ($index === 2 ? 'danger' : 'primary')); 
                                            ?> fs-6">
                                                #<?php echo $index + 1; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <img src="<?php echo UPLOAD_URL . 'profiles/' . $student['foto']; ?>" 
                                                 class="avatar-sm me-2" alt="">
                                            <strong><?php echo htmlspecialchars($student['nama']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['sekolah'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-star me-1"></i><?php echo $student['poin']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $badges = json_decode($student['badges'], true) ?? [];
                                            foreach ($badges as $badge): 
                                            ?>
                                            <span class="badge bg-info me-1"><?php echo htmlspecialchars($badge); ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Active Classes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>Kelas Aktif</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Kelas</th>
                                    <th>Kode</th>
                                    <th>Guru</th>
                                    <th>Jumlah Siswa</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($active_classes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Belum ada kelas
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($active_classes as $kelas): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($kelas['nama_kelas']); ?></strong>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($kelas['kode_kelas']); ?></code>
                                        </td>
                                        <td><?php echo htmlspecialchars($kelas['nama_guru']); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo $kelas['jumlah_siswa']; ?> Siswa
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDate($kelas['created_at']); ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Export Buttons -->
            <div class="text-end mt-4">
                <button class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Laporan
                </button>
                <button class="btn btn-primary" onclick="alert('Fitur export akan segera hadir!')">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>