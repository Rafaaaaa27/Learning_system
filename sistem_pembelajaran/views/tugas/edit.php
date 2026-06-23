<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-edit me-2"></i>Edit Tugas/Kuis</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['tugas_errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['tugas_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['tugas_errors']); endif; ?>
                    
                    <form action="tugas.php?action=update&id=<?php echo $tugas['id']; ?>" 
                          method="POST" enctype="multipart/form-data" id="form-tugas">
                        
                        <!-- Kelas -->
                        <div class="mb-3">
                            <label for="id_kelas" class="form-label">Kelas *</label>
                            <select class="form-select" id="id_kelas" name="id_kelas" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelas as $k): ?>
                                <option value="<?php echo $k['id']; ?>" 
                                        <?php echo $k['id'] == $tugas['id_kelas'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($k['nama_kelas']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Judul -->
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Tugas *</label>
                            <input type="text" class="form-control" id="judul" name="judul" 
                                   value="<?php echo htmlspecialchars($tugas['judul']); ?>" 
                                   placeholder="Contoh: Latihan Matematika Bab 5" required>
                        </div>
                        
                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Jelaskan instruksi tugas..."><?php echo htmlspecialchars($tugas['deskripsi'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Tipe -->
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe Tugas *</label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="essay" <?php echo $tugas['tipe'] === 'essay' ? 'selected' : ''; ?>>
                                    Essay (Jawaban Bebas)
                                </option>
                                <option value="multiple_choice" <?php echo $tugas['tipe'] === 'multiple_choice' ? 'selected' : ''; ?>>
                                    Multiple Choice (Pilihan Ganda)
                                </option>
                            </select>
                        </div>
                        
                        <!-- Multiple Choice Section -->
                        <div id="mc-section" style="display: <?php echo $tugas['tipe'] === 'multiple_choice' ? 'block' : 'none'; ?>;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Edit soal pilihan ganda di bawah ini
                            </div>
                            
                            <div id="soal-container">
                                <?php 
                                if ($tugas['tipe'] === 'multiple_choice' && isset($tugas['opsi_jawaban'])):
                                    $soalList = is_array($tugas['opsi_jawaban']) ? $tugas['opsi_jawaban'] : [];
                                    foreach ($soalList as $index => $soal):
                                ?>
                                <div class="card mb-3" id="soal-<?php echo $index + 1; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6>Soal <?php echo $index + 1; ?></h6>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="hapusSoal(<?php echo $index + 1; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Pertanyaan *</label>
                                            <textarea class="form-control" name="soal[]" rows="2" required><?php echo htmlspecialchars($soal['soal']); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <label class="form-label">Pilihan Jawaban *</label>
                                        </div>
                                        
                                        <?php 
                                        $labels = ['A', 'B', 'C', 'D'];
                                        foreach ($labels as $key => $label): 
                                        ?>
                                        <div class="mb-2">
                                            <div class="input-group">
                                                <span class="input-group-text"><?php echo $label; ?></span>
                                                <input type="text" class="form-control" 
                                                       name="pilihan_<?php echo strtolower($label); ?>[]" 
                                                       value="<?php echo htmlspecialchars($soal['pilihan'][$key] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <div class="mb-2">
                                            <label class="form-label">Jawaban Benar *</label>
                                            <select class="form-select" name="jawaban_benar[]" required>
                                                <option value="0" <?php echo $soal['jawaban_benar'] == 0 ? 'selected' : ''; ?>>A</option>
                                                <option value="1" <?php echo $soal['jawaban_benar'] == 1 ? 'selected' : ''; ?>>B</option>
                                                <option value="2" <?php echo $soal['jawaban_benar'] == 2 ? 'selected' : ''; ?>>C</option>
                                                <option value="3" <?php echo $soal['jawaban_benar'] == 3 ? 'selected' : ''; ?>>D</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </div>
                            
                            <button type="button" class="btn btn-secondary mb-3" onclick="tambahSoal()">
                                <i class="fas fa-plus me-2"></i>Tambah Soal
                            </button>
                        </div>
                        
                        <!-- Deadline -->
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Deadline *</label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" 
                                   value="<?php echo date('Y-m-d\TH:i', strtotime($tugas['deadline'])); ?>" required>
                        </div>
                        
                        <!-- File Lampiran -->
                        <div class="mb-3">
                            <label for="file_lampiran" class="form-label">File Lampiran</label>
                            <?php if ($tugas['file_lampiran']): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-file me-2"></i>
                                File saat ini: 
                                <a href="<?php echo UPLOAD_URL . 'tugas/' . $tugas['file_lampiran']; ?>" 
                                   target="_blank">
                                    <?php echo htmlspecialchars($tugas['file_lampiran']); ?>
                                </a>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="hapus_file" id="hapus_file">
                                    <label class="form-check-label" for="hapus_file">
                                        Hapus file ini
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="file_lampiran" name="file_lampiran">
                            <small class="text-muted">Max 10MB - PDF, DOC, JPG, PNG (Upload file baru untuk mengganti)</small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="tugas.php?action=detail&id=<?php echo $tugas['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let soalCount = <?php echo ($tugas['tipe'] === 'multiple_choice' && isset($tugas['opsi_jawaban'])) ? count($tugas['opsi_jawaban']) : 0; ?>;

// Show/hide MC section
document.getElementById('tipe').addEventListener('change', function() {
    const mcSection = document.getElementById('mc-section');
    if (this.value === 'multiple_choice') {
        mcSection.style.display = 'block';
        if (soalCount === 0) {
            tambahSoal();
        }
    } else {
        mcSection.style.display = 'none';
    }
});

function tambahSoal() {
    soalCount++;
    const container = document.getElementById('soal-container');
    
    const soalDiv = document.createElement('div');
    soalDiv.className = 'card mb-3';
    soalDiv.id = 'soal-' + soalCount;
    soalDiv.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6>Soal ${soalCount}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="hapusSoal(${soalCount})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Pertanyaan *</label>
                <textarea class="form-control" name="soal[]" rows="2" required></textarea>
            </div>
            
            <div class="mb-2">
                <label class="form-label">Pilihan Jawaban *</label>
            </div>
            
            <div class="mb-2">
                <div class="input-group">
                    <span class="input-group-text">A</span>
                    <input type="text" class="form-control" name="pilihan_a[]" required>
                </div>
            </div>
            
            <div class="mb-2">
                <div class="input-group">
                    <span class="input-group-text">B</span>
                    <input type="text" class="form-control" name="pilihan_b[]" required>
                </div>
            </div>
            
            <div class="mb-2">
                <div class="input-group">
                    <span class="input-group-text">C</span>
                    <input type="text" class="form-control" name="pilihan_c[]" required>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">D</span>
                    <input type="text" class="form-control" name="pilihan_d[]" required>
                </div>
            </div>
            
            <div class="mb-2">
                <label class="form-label">Jawaban Benar *</label>
                <select class="form-select" name="jawaban_benar[]" required>
                    <option value="0">A</option>
                    <option value="1">B</option>
                    <option value="2">C</option>
                    <option value="3">D</option>
                </select>
            </div>
        </div>
    `;
    
    container.appendChild(soalDiv);
}

function hapusSoal(id) {
    const soalDiv = document.getElementById('soal-' + id);
    if (soalDiv && confirm('Hapus soal ini?')) {
        soalDiv.remove();
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>