<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-plus me-2"></i>Buat Kelas Baru</h4>
                </div>
                <div class="card-body">
                    <form action="kelas.php?action=store" method="POST">
                        <!-- Nama Kelas -->
                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas *</label>
                            <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" 
                                   placeholder="Contoh: Matematika XII IPA 1" required>
                        </div>
                        
                        <!-- Guru (untuk admin) -->
                        <?php if ($user['peran'] === 'admin' && !empty($guruList)): ?>
                        <div class="mb-3">
                            <label for="id_guru" class="form-label">Guru Pengajar *</label>
                            <select class="form-select" id="id_guru" name="id_guru" required>
                                <option value="">Pilih Guru</option>
                                <?php foreach ($guruList as $guru): ?>
                                <option value="<?php echo $guru['id']; ?>">
                                    <?php echo htmlspecialchars($guru['nama']); ?> (<?php echo htmlspecialchars($guru['email']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi Kelas</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Jelaskan tentang kelas ini..."></textarea>
                            <small class="text-muted">Opsional: Tambahkan informasi tentang kelas, jadwal, atau catatan penting</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Info:</strong> Kode kelas akan digenerate otomatis setelah kelas dibuat. 
                            Bagikan kode ini kepada siswa untuk bergabung ke kelas.
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Buat Kelas
                            </button>
                            <a href="kelas.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>