<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tasks me-2"></i>Daftar Tugas</h2>
                <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                <a href="tugas.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Tugas Baru
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Filter Tabs untuk Siswa -->
            <?php if ($user['peran'] === 'siswa'): ?>
            <ul class="nav nav-tabs mb-4" id="tugasTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="semua-tab" data-bs-toggle="tab" data-bs-target="#semua" type="button">
                        <i class="fas fa-list me-2"></i>Semua Tugas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="belum-tab" data-bs-toggle="tab" data-bs-target="#belum" type="button">
                        <i class="fas fa-clock me-2"></i>Belum Dikerjakan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button">
                        <i class="fas fa-check me-2"></i>Sudah Dikumpulkan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="dinilai-tab" data-bs-toggle="tab" data-bs-target="#dinilai" type="button">
                        <i class="fas fa-star me-2"></i>Sudah Dinilai
                    </button>
                </li>
            </ul>
            <?php endif; ?>
            
            <?php if (empty($tugas)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Belum Ada Tugas</h4>
                <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                <p>Buat tugas baru untuk kelas Anda</p>
                <a href="tugas.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Tugas
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            
            <div class="tab-content" id="tugasTabsContent">
                <?php 
                // Group tugas by status untuk siswa
                if ($user['peran'] === 'siswa') {
                    $tugasBelum = array_filter($tugas, fn($t) => ($t['status'] ?? '') === 'belum');
                    $tugasSubmitted = array_filter($tugas, fn($t) => ($t['status'] ?? '') === 'submitted');
                    $tugasDinilai = array_filter($tugas, fn($t) => ($t['status'] ?? '') === 'dinilai');
                }
                ?>
                
                <!-- Tab Semua -->
                <div class="tab-pane fade show active" id="semua" role="tabpanel">
                    <div class="row g-4">
                        <?php foreach ($tugas as $t): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title"><?php echo htmlspecialchars($t['judul']); ?></h5>
                                        <?php if ($user['peran'] === 'siswa'): ?>
                                            <?php if ($t['status'] === 'belum'): ?>
                                            <span class="badge bg-warning">Belum</span>
                                            <?php elseif ($t['status'] === 'submitted'): ?>
                                            <span class="badge bg-info">Dikumpulkan</span>
                                            <?php else: ?>
                                            <span class="badge bg-success">Dinilai</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-book me-1"></i>
                                            <?php echo ucfirst($t['tipe']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="card-text text-muted small">
                                        <?php echo truncate($t['deskripsi'] ?? 'Tidak ada deskripsi', 100); ?>
                                    </p>
                                    
                                    <div class="mb-3">
                                        <?php if (isset($t['nama_kelas'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-chalkboard me-1"></i>
                                            <?php echo htmlspecialchars($t['nama_kelas']); ?>
                                        </small>
                                        <br>
                                        <?php endif; ?>
                                        
                                        <?php if ($t['deadline']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Deadline: <?php echo formatDateTime($t['deadline']); ?>
                                        </small>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($t['total_jawaban']) && $user['peran'] === 'guru'): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $t['total_jawaban']; ?> Jawaban
                                        </small>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($t['nilai']) && $t['nilai'] !== null): ?>
                                        <br>
                                        <strong class="text-success">
                                            <i class="fas fa-star me-1"></i>
                                            Nilai: <?php echo $t['nilai']; ?>
                                        </strong>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="tugas.php?action=detail&id=<?php echo $t['id']; ?>" 
                                           class="btn btn-sm btn-primary flex-fill">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </a>
                                        
                                        <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                                        <a href="tugas.php?action=edit&id=<?php echo $t['id']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteTugas(<?php echo $t['id']; ?>)" 
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
                </div>
                
                <?php if ($user['peran'] === 'siswa'): ?>
                <!-- Tab Belum -->
                <div class="tab-pane fade" id="belum" role="tabpanel">
                    <div class="row g-4">
                        <?php foreach ($tugasBelum as $t): ?>
                        <div class="col-md-6 col-lg-4">
                            <!-- Same card structure -->
                            <div class="card h-100 border-warning">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($t['judul']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo truncate($t['deskripsi'] ?? 'Tidak ada deskripsi', 100); ?>
                                    </p>
                                    <a href="tugas.php?action=detail&id=<?php echo $t['id']; ?>" 
                                       class="btn btn-sm btn-warning w-100">
                                        <i class="fas fa-pencil-alt me-1"></i>Kerjakan Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($tugasBelum)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">Semua tugas sudah dikerjakan!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tab Submitted -->
                <div class="tab-pane fade" id="submitted" role="tabpanel">
                    <div class="row g-4">
                        <?php foreach ($tugasSubmitted as $t): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-info">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($t['judul']); ?></h5>
                                    <span class="badge bg-info mb-2">Menunggu Penilaian</span>
                                    <a href="tugas.php?action=detail&id=<?php echo $t['id']; ?>" 
                                       class="btn btn-sm btn-primary w-100">
                                        <i class="fas fa-eye me-1"></i>Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($tugasSubmitted)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada tugas yang menunggu penilaian</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tab Dinilai -->
                <div class="tab-pane fade" id="dinilai" role="tabpanel">
                    <div class="row g-4">
                        <?php foreach ($tugasDinilai as $t): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-success">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($t['judul']); ?></h5>
                                    <div class="mb-2">
                                        <strong class="text-success fs-4">
                                            <i class="fas fa-star me-1"></i><?php echo $t['nilai']; ?>
                                        </strong>
                                    </div>
                                    <a href="tugas.php?action=detail&id=<?php echo $t['id']; ?>" 
                                       class="btn btn-sm btn-success w-100">
                                        <i class="fas fa-eye me-1"></i>Lihat Feedback
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($tugasDinilai)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada tugas yang dinilai</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteTugas(id) {
    if (confirm('Apakah Anda yakin ingin menghapus tugas ini?')) {
        window.location.href = 'tugas.php?action=delete&id=' + id;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>