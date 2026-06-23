<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chalkboard me-2"></i>Daftar Kelas</h2>
                <div>
                    <?php if ($user['peran'] === 'siswa'): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#joinKelasModal">
                        <i class="fas fa-plus me-2"></i>Gabung Kelas
                    </button>
                    <?php elseif (in_array($user['peran'], ['admin', 'guru'])): ?>
                    <a href="kelas.php?action=create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Kelas Baru
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (empty($kelas)): ?>
            <div class="text-center py-5">
                <i class="fas fa-chalkboard fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Belum Ada Kelas</h4>
                <?php if ($user['peran'] === 'siswa'): ?>
                <p>Gabung kelas dengan memasukkan kode kelas dari guru Anda</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#joinKelasModal">
                    <i class="fas fa-plus me-2"></i>Gabung Kelas
                </button>
                <?php elseif (in_array($user['peran'], ['admin', 'guru'])): ?>
                <p>Buat kelas baru untuk memulai</p>
                <a href="kelas.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Kelas
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            
            <div class="row g-4">
                <?php foreach ($kelas as $k): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <?php echo htmlspecialchars($k['nama_kelas']); ?>
                                </h5>
                                <span class="badge bg-primary"><?php echo $k['kode_kelas']; ?></span>
                            </div>
                            
                            <?php if ($k['deskripsi']): ?>
                            <p class="card-text text-muted small">
                                <?php echo truncate($k['deskripsi'], 100); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <?php if ($user['peran'] === 'siswa' && isset($k['nama_guru'])): ?>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($k['nama_guru']); ?>
                                </small>
                                <br>
                                <?php endif; ?>
                                
                                <?php if (isset($k['jumlah_siswa'])): ?>
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    <?php echo $k['jumlah_siswa']; ?> Siswa
                                </small>
                                <br>
                                <?php endif; ?>
                                
                                <?php if (isset($k['tanggal_gabung'])): ?>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Bergabung: <?php echo formatDate($k['tanggal_gabung']); ?>
                                </small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="kelas.php?action=detail&id=<?php echo $k['id']; ?>" 
                                   class="btn btn-sm btn-primary flex-fill">
                                    <i class="fas fa-arrow-right me-1"></i>Masuk Kelas
                                </a>
                                
                                <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                                <a href="kelas.php?action=edit&id=<?php echo $k['id']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteKelas(<?php echo $k['id']; ?>)" 
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Join Kelas (untuk siswa) -->
<?php if ($user['peran'] === 'siswa'): ?>
<div class="modal fade" id="joinKelasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Gabung Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="kelas.php?action=join" method="POST">
                <div class="modal-body">
                    <p class="text-muted">
                        Masukkan kode kelas yang diberikan oleh guru Anda untuk bergabung ke kelas.
                    </p>
                    
                    <div class="mb-3">
                        <label for="kode_kelas" class="form-label">Kode Kelas *</label>
                        <input type="text" class="form-control" id="kode_kelas" name="kode_kelas" 
                               placeholder="Contoh: KLS-ABC12345" required>
                        <small class="text-muted">Format: KLS-XXXXXXXX</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>Gabung
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function deleteKelas(id) {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini? Semua data terkait (materi, tugas, forum) akan ikut terhapus.')) {
        window.location.href = 'kelas.php?action=delete&id=' + id;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>