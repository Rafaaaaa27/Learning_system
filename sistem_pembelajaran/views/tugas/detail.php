<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Tugas Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo htmlspecialchars($tugas['judul']); ?></h4>
                        <span class="badge bg-primary"><?php echo ucfirst($tugas['tipe']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($tugas['deskripsi']): ?>
                    <div class="mb-3">
                        <h6>Deskripsi:</h6>
                        <p><?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Kelas:</strong>
                            <p><?php echo htmlspecialchars($tugas['nama_kelas']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Guru:</strong>
                            <p><?php echo htmlspecialchars($tugas['nama_guru']); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($tugas['deadline']): ?>
                    <div class="alert alert-<?php echo strtotime($tugas['deadline']) < time() ? 'danger' : 'warning'; ?>">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Deadline:</strong> <?php echo formatDateTime($tugas['deadline']); ?>
                        <?php if (strtotime($tugas['deadline']) < time()): ?>
                        <span class="badge bg-danger ms-2">Terlambat</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($tugas['file_lampiran']): ?>
                    <div class="mb-3">
                        <strong>File Lampiran:</strong>
                        <a href="<?php echo UPLOAD_URL . 'tugas/' . $tugas['file_lampiran']; ?>" 
                           class="btn btn-sm btn-outline-primary" download>
                            <i class="fas fa-download me-1"></i>Download
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($user['peran'], ['admin', 'guru'])): ?>
                    <div class="d-flex gap-2 mt-3">
                        <a href="tugas.php?action=edit&id=<?php echo $tugas['id']; ?>" 
                           class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Tugas
                        </a>
                        <button onclick="deleteTugas(<?php echo $tugas['id']; ?>)" 
                                class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Jawaban Siswa -->
            <?php if ($user['peran'] === 'siswa'): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Jawaban Anda</h5>
                </div>
                <div class="card-body">
                    <?php if ($jawaban): ?>
                        <?php if ($jawaban['nilai'] !== null): ?>
                        <div class="alert alert-success">
                            <h4 class="mb-0">
                                <i class="fas fa-star me-2"></i>
                                Nilai: <?php echo $jawaban['nilai']; ?>
                            </h4>
                            <?php if ($jawaban['feedback']): ?>
                            <hr>
                            <strong>Feedback:</strong>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($jawaban['feedback'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-clock me-2"></i>
                            Jawaban Anda sedang menunggu penilaian dari guru
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>Jawaban Anda:</strong>
                            <p><?php echo nl2br(htmlspecialchars($jawaban['jawaban'])); ?></p>
                        </div>
                        
                        <?php if ($jawaban['file_jawaban']): ?>
                        <div class="mb-3">
                            <strong>File:</strong>
                            <a href="<?php echo UPLOAD_URL . 'jawaban/' . $jawaban['file_jawaban']; ?>" 
                               class="btn btn-sm btn-primary" download>
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <small class="text-muted">
                            Dikumpulkan: <?php echo formatDateTime($jawaban['submitted_at']); ?>
                        </small>
                    <?php else: ?>
                        <!-- Form Submit Jawaban -->
                        <form action="tugas.php?action=submit&id=<?php echo $tugas['id']; ?>" 
                              method="POST" enctype="multipart/form-data">
                            <?php if ($tugas['tipe'] === 'essay'): ?>
                            <div class="mb-3">
                                <label for="jawaban" class="form-label">Jawaban *</label>
                                <textarea class="form-control" id="jawaban" name="jawaban" 
                                          rows="10" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="file_jawaban" class="form-label">File Jawaban (Opsional)</label>
                                <input type="file" class="form-control" id="file_jawaban" name="file_jawaban">
                                <small class="text-muted">Max 10MB</small>
                            </div>
                            <?php else: ?>
                            <!-- Multiple Choice -->
                            <?php 
                            $soalList = json_decode($tugas['opsi_jawaban'], true);
                            foreach ($soalList as $index => $soal): 
                            ?>
                            <div class="mb-4">
                                <h6><?php echo ($index + 1) . '. ' . htmlspecialchars($soal['soal']); ?></h6>
                                <?php foreach ($soal['pilihan'] as $key => $pilihan): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="jawaban_mc[<?php echo $index; ?>]" 
                                           id="soal<?php echo $index; ?>_<?php echo $key; ?>"
                                           value="<?php echo $key; ?>" required>
                                    <label class="form-check-label" 
                                           for="soal<?php echo $index; ?>_<?php echo $key; ?>">
                                        <?php echo htmlspecialchars($pilihan); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Jawaban
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Daftar Jawaban (Guru) -->
            <?php if (in_array($user['peran'], ['admin', 'guru']) && is_array($jawaban)): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Daftar Jawaban Siswa</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($jawaban)): ?>
                    <p class="text-muted">Belum ada siswa yang mengumpulkan</p>
                    <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($jawaban as $j): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6><?php echo htmlspecialchars($j['nama']); ?></h6>
                                    <p class="mb-1"><?php echo truncate($j['jawaban'], 100); ?></p>
                                    <small class="text-muted">
                                        Dikumpulkan: <?php echo formatDateTime($j['submitted_at']); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <?php if ($j['nilai'] !== null): ?>
                                    <h5 class="mb-0 text-success">
                                        <i class="fas fa-star me-1"></i><?php echo $j['nilai']; ?>
                                    </h5>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-primary mt-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#nilaiModal<?php echo $j['id']; ?>">
                                        <i class="fas fa-edit me-1"></i>
                                        <?php echo $j['nilai'] !== null ? 'Edit Nilai' : 'Beri Nilai'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Nilai -->
                        <div class="modal fade" id="nilaiModal<?php echo $j['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Beri Nilai</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h6><?php echo htmlspecialchars($j['nama']); ?></h6>
                                        <p><?php echo nl2br(htmlspecialchars($j['jawaban'])); ?></p>
                                        
                                        <form id="form-nilai-<?php echo $j['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nilai (0-100) *</label>
                                                <input type="number" class="form-control" name="nilai" 
                                                       min="0" max="100" 
                                                       value="<?php echo $j['nilai']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Feedback</label>
                                                <textarea class="form-control" name="feedback" 
                                                          rows="3"><?php echo htmlspecialchars($j['feedback'] ?? ''); ?></textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-primary" 
                                                onclick="submitNilai(<?php echo $j['id']; ?>)">
                                            Simpan Nilai
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php if (isset($statistik)): ?>
            <div class="card">
                <div class="card-header">
                    <h6>Statistik</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Total Siswa:</strong>
                        <span class="float-end"><?php echo $statistik['total_siswa']; ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Sudah Mengumpulkan:</strong>
                        <span class="float-end"><?php echo $statistik['total_submit']; ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Sudah Dinilai:</strong>
                        <span class="float-end"><?php echo $statistik['total_dinilai']; ?></span>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <strong>Rata-rata:</strong>
                        <span class="float-end"><?php echo round($statistik['rata_rata'], 2); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Tertinggi:</strong>
                        <span class="float-end"><?php echo $statistik['nilai_tertinggi']; ?></span>
                    </div>
                    <div>
                        <strong>Terendah:</strong>
                        <span class="float-end"><?php echo $statistik['nilai_terendah']; ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteTugas(id) {
    if (confirm('Hapus tugas ini? Semua jawaban siswa akan ikut terhapus.')) {
        window.location.href = 'tugas.php?action=delete&id=' + id;
    }
}

function submitNilai(jawabanId) {
    const form = document.getElementById('form-nilai-' + jawabanId);
    const formData = new FormData(form);
    formData.append('jawaban_id', jawabanId);
    
    fetch('tugas.php?action=grade', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Nilai berhasil disimpan');
            location.reload();
        } else {
            alert('Gagal menyimpan nilai: ' + data.message);
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>