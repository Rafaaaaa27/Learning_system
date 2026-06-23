<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-plus me-2"></i>Buat Tugas/Kuis Baru</h4>
                </div>
                <div class="card-body">
                    <form action="tugas.php?action=store" method="POST" enctype="multipart/form-data" id="form-tugas">
                        <!-- Kelas -->
                        <div class="mb-3">
                            <label for="id_kelas" class="form-label">Kelas *</label>
                            <select class="form-select" id="id_kelas" name="id_kelas" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelas as $k): ?>
                                <option value="<?php echo $k['id']; ?>">
                                    <?php echo htmlspecialchars($k['nama_kelas']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Judul -->
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Tugas *</label>
                            <input type="text" class="form-control" id="judul" name="judul" 
                                   placeholder="Contoh: Latihan Matematika Bab 5" required>
                        </div>
                        
                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Jelaskan instruksi tugas..."></textarea>
                        </div>
                        
                        <!-- Tipe -->
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe Tugas *</label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="essay">Essay (Jawaban Bebas)</option>
                                <option value="multiple_choice">Multiple Choice (Pilihan Ganda)</option>
                            </select>
                        </div>
                        
                        <!-- Multiple Choice Section (hidden by default) -->
                        <div id="mc-section" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Tambahkan soal pilihan ganda di bawah ini
                            </div>
                            
                            <div id="soal-container">
                                <!-- Soal items will be added here -->
                            </div>
                            
                            <button type="button" class="btn btn-secondary mb-3" onclick="tambahSoal()">
                                <i class="fas fa-plus me-2"></i>Tambah Soal
                            </button>
                        </div>
                        
                        <!-- Deadline -->
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Deadline *</label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" required>
                        </div>
                        
                        <!-- File Lampiran -->
                        <div class="mb-3">
                            <label for="file_lampiran" class="form-label">File Lampiran (Opsional)</label>
                            <input type="file" class="form-control" id="file_lampiran" name="file_lampiran">
                            <small class="text-muted">Max 10MB - PDF, DOC, JPG, PNG</small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Buat Tugas
                            </button>
                            <a href="tugas.php" class="btn btn-secondary">
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
let soalCount = 0;

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

// Set default deadline (3 days from now)
const deadline = document.getElementById('deadline');
const now = new Date();
now.setDate(now.getDate() + 3);
deadline.value = now.toISOString().slice(0, 16);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>