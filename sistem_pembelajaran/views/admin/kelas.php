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
                    <a href="admin.php?page=kelas" class="list-group-item list-group-item-action active">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chalkboard me-2"></i>Manajemen Kelas</h2>
                <a href="kelas.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Kelas
                </a>
            </div>
            
            <!-- Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="admin.php" method="GET" class="row g-3">
                        <input type="hidden" name="page" value="kelas">
                        
                        <div class="col-md-10">
                            <label class="form-label">Cari Kelas</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari nama kelas atau nama guru..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-primary"><?php echo count($kelas); ?></div>
                                    <div class="stat-label">Total Kelas</div>
                                </div>
                                <div>
                                    <i class="fas fa-chalkboard fa-3x text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card stat-card border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-success">
                                        <?php echo array_sum(array_column($kelas, 'jumlah_siswa')); ?>
                                    </div>
                                    <div class="stat-label">Total Siswa</div>
                                </div>
                                <div>
                                    <i class="fas fa-users fa-3x text-success opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card stat-card border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-value text-info">
                                        <?php 
                                        $avgSiswa = count($kelas) > 0 ? 
                                                    round(array_sum(array_column($kelas, 'jumlah_siswa')) / count($kelas), 1) : 0;
                                        echo $avgSiswa;
                                        ?>
                                    </div>
                                    <div class="stat-label">Rata-rata Siswa/Kelas</div>
                                </div>
                                <div>
                                    <i class="fas fa-chart-line fa-3x text-info opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Kelas Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Kelas (<?php echo count($kelas); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($kelas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chalkboard fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada kelas ditemukan</p>
                        <a href="kelas.php?action=create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Kelas
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Kelas</th>
                                    <th>Kode Kelas</th>
                                    <th>Guru</th>
                                    <th>Jumlah Siswa</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kelas as $k): ?>
                                <tr>
                                    <td><?php echo $k['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($k['nama_kelas']); ?></strong>
                                        <?php if ($k['deskripsi']): ?>
                                        <br><small class="text-muted">
                                            <?php echo truncate($k['deskripsi'], 50); ?>
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code class="bg-light p-1 rounded"><?php echo htmlspecialchars($k['kode_kelas']); ?></code>
                                    </td>
                                    <td><?php echo htmlspecialchars($k['nama_guru']); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $k['jumlah_siswa']; ?> Siswa
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo formatDate($k['created_at']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="kelas.php?action=detail&id=<?php echo $k['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="kelas.php?action=edit&id=<?php echo $k['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteKelas(<?php echo $k['id']; ?>)" 
                                                    class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteKelas(id) {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini?\n\nSemua data terkait (materi, tugas, dll) akan ikut terhapus!')) {
        window.location.href = 'kelas.php?action=delete&id=' + id;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>