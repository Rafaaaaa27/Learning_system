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
                    <a href="admin.php?page=kelas" class="list-group-item list-group-item-action">
                        <i class="fas fa-chalkboard me-2"></i>Manajemen Kelas
                    </a>
                    <a href="admin.php?page=reports" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Laporan
                    </a>
                    <a href="admin.php?page=settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-sliders-h me-2"></i>Pengaturan
                    </a>
                    <a href="admin.php?page=logs" class="list-group-item list-group-item-action active">
                        <i class="fas fa-clipboard-list me-2"></i>System Logs
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <h2 class="mb-4"><i class="fas fa-clipboard-list me-2"></i>System Logs</h2>
            
            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipe Log</label>
                            <select class="form-select">
                                <option selected>Semua Log</option>
                                <option>Login Activity</option>
                                <option>CRUD Operations</option>
                                <option>Errors</option>
                                <option>System Events</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Aktivitas Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Aktivitas</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sample Data -->
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                    <td>
                                        <strong>Admin</strong>
                                        <br><small class="text-muted">admin@sekolah.com</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-sign-in-alt text-success me-2"></i>
                                        Login ke sistem
                                    </td>
                                    <td><code>127.0.0.1</code></td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime('-5 minutes')); ?></td>
                                    <td>
                                        <strong>Budi Santoso</strong>
                                        <br><small class="text-muted">budi.guru@sekolah.com</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-edit text-warning me-2"></i>
                                        Update tugas: Matematika Dasar
                                    </td>
                                    <td><code>127.0.0.1</code></td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime('-15 minutes')); ?></td>
                                    <td>
                                        <strong>Andi Wijaya</strong>
                                        <br><small class="text-muted">andi.siswa@sekolah.com</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-upload text-info me-2"></i>
                                        Submit jawaban tugas
                                    </td>
                                    <td><code>127.0.0.1</code></td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime('-30 minutes')); ?></td>
                                    <td>
                                        <strong>System</strong>
                                        <br><small class="text-muted">Automated</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-database text-primary me-2"></i>
                                        Database backup completed
                                    </td>
                                    <td><code>Internal</code></td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime('-1 hour')); ?></td>
                                    <td>
                                        <strong>Unknown User</strong>
                                        <br><small class="text-muted">unknown@email.com</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-times-circle text-danger me-2"></i>
                                        Failed login attempt
                                    </td>
                                    <td><code>192.168.1.100</code></td>
                                    <td><span class="badge bg-danger">Failed</span></td>
                                </tr>
                                
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime('-2 hours')); ?></td>
                                    <td>
                                        <strong>Admin</strong>
                                        <br><small class="text-muted">admin@sekolah.com</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-user-plus text-success me-2"></i>
                                        Created new user: Dewi Lestari
                                    </td>
                                    <td><code>127.0.0.1</code></td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Menampilkan 6 aktivitas terakhir</small>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary">1</button>
                            <button class="btn btn-sm btn-outline-primary">2</button>
                            <button class="btn btn-sm btn-outline-primary">3</button>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="row g-4 mt-2">
                <div class="col-md-3">
                    <div class="card stat-card border-success">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-0">156</h3>
                            <small class="text-muted">Successful Logins</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card border-danger">
                        <div class="card-body text-center">
                            <h3 class="text-danger mb-0">3</h3>
                            <small class="text-muted">Failed Logins</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body text-center">
                            <h3 class="text-warning mb-0">42</h3>
                            <small class="text-muted">CRUD Operations</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card border-info">
                        <div class="card-body text-center">
                            <h3 class="text-info mb-0">0</h3>
                            <small class="text-muted">System Errors</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info Alert -->
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Info:</strong> Sistem logging sedang dalam pengembangan. Data di atas adalah contoh data untuk demonstrasi.
                Fitur logging lengkap akan tersedia di update berikutnya.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>