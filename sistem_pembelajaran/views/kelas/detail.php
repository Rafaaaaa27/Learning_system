<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Header Kelas -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></h2>
                    <p class="text-muted mb-2">
                        <i class="fas fa-user me-2"></i>
                        Guru: <?php echo htmlspecialchars($kelas['nama_guru']); ?>
                    </p>
                    <?php if ($kelas['deskripsi']): ?>
                    <p class="mb-0"><?php echo htmlspecialchars($kelas['deskripsi']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="mb-2">
                        <strong>Kode Kelas:</strong>
                        <span class="badge bg-primary fs-6"><?php echo $kelas['kode_kelas']; ?></span>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyKodeKelas()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div>
                        <i class="fas fa-users me-1"></i>
                        <?php echo count($siswa); ?> Siswa
                    </div>
                    <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                    <div class="mt-3">
                        <a href="kelas.php?action=edit&id=<?php echo $kelas['id']; ?>" 
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit Kelas
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="kelasTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="materi-tab" data-bs-toggle="tab" 
                    data-bs-target="#materi" type="button">
                <i class="fas fa-book me-2"></i>Materi
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tugas-tab" data-bs-toggle="tab" 
                    data-bs-target="#tugas" type="button">
                <i class="fas fa-tasks me-2"></i>Tugas
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="siswa-tab" data-bs-toggle="tab" 
                    data-bs-target="#siswa-tab-content" type="button">
                <i class="fas fa-users me-2"></i>Siswa
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="forum-tab" data-bs-toggle="tab" 
                    data-bs-target="#forum" type="button">
                <i class="fas fa-comments me-2"></i>Forum
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="kelasTabsContent">
        <!-- Tab Materi -->
        <div class="tab-pane fade show active" id="materi" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Materi Pembelajaran</h5>
                <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadMateriModal">
                    <i class="fas fa-upload me-2"></i>Upload Materi
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (empty($materi)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada materi</p>
            </div>
            <?php else: ?>
            <div class="list-group">
                <?php foreach ($materi as $m): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                <?php echo htmlspecialchars($m['judul']); ?>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDateTime($m['uploaded_at']); ?>
                            </small>
                        </div>
                        <div>
                            <a href="<?php echo UPLOAD_URL . $m['file_path']; ?>" 
                               class="btn btn-sm btn-primary" download>
                                <i class="fas fa-download"></i>
                            </a>
                            <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                            <button onclick="deleteMateri(<?php echo $m['id']; ?>)" 
                                    class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Tab Tugas -->
        <div class="tab-pane fade" id="tugas" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Daftar Tugas</h5>
                <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                <a href="tugas.php?action=create&kelas_id=<?php echo $kelas['id']; ?>" 
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i>Buat Tugas
                </a>
                <?php endif; ?>
            </div>
            
            <p class="text-muted">Lihat semua tugas di <a href="tugas.php">halaman tugas</a></p>
        </div>
        
        <!-- Tab Siswa -->
        <div class="tab-pane fade" id="siswa-tab-content" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Daftar Siswa (<?php echo count($siswa); ?>)</h5>
                <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSiswaModal">
                    <i class="fas fa-user-plus me-2"></i>Tambah Siswa
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (empty($siswa)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada siswa</p>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($siswa as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo UPLOAD_URL . 'profiles/' . $s['foto']; ?>" 
                                     class="avatar me-3" alt="<?php echo htmlspecialchars($s['nama']); ?>"
                                     onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($s['nama']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($s['email']); ?></small>
                                    <?php if (isset($s['poin'])): ?>
                                    <div class="mt-1">
                                        <span class="badge bg-warning">
                                            <i class="fas fa-star me-1"></i><?php echo $s['poin']; ?> Poin
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                                <button onclick="removeSiswa(<?php echo $kelas['id']; ?>, <?php echo $s['id']; ?>)" 
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i>
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
        
        <!-- Tab Forum -->
        <div class="tab-pane fade" id="forum" role="tabpanel">
            <p class="text-muted">
                Buka <a href="forum.php?kelas_id=<?php echo $kelas['id']; ?>">halaman forum</a> 
                untuk berdiskusi dengan anggota kelas
            </p>
        </div>
    </div>
</div>

<!-- Modal Upload Materi -->
<?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
<div class="modal fade" id="uploadMateriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Materi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="materi.php?action=upload" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_kelas" value="<?php echo $kelas['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Materi *</label>
                        <input type="text" class="form-control" id="judul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File *</label>
                        <input type="file" class="form-control" id="file" name="file" 
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Format: PDF, DOC, PPT, JPG (Max 10MB)</small>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi_materi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi_materi" name="deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function copyKodeKelas() {
    const kode = '<?php echo $kelas['kode_kelas']; ?>';
    navigator.clipboard.writeText(kode).then(() => {
        alert('Kode kelas berhasil disalin: ' + kode);
    });
}

function deleteMateri(id) {
    if (confirm('Hapus materi ini?')) {
        window.location.href = 'materi.php?action=delete&id=' + id + '&kelas_id=<?php echo $kelas['id']; ?>';
    }
}

function removeSiswa(kelasId, siswaId) {
    if (confirm('Hapus siswa dari kelas ini?')) {
        window.location.href = 'kelas.php?action=remove_siswa&id=' + kelasId + '&siswa_id=' + siswaId;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>