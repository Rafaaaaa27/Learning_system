<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-edit me-2"></i>Edit Kelas</h4>
                </div>
                <div class="card-body">
                    <form action="kelas.php?action=update&id=<?php echo $kelas['id']; ?>" method="POST">
                        <!-- Nama Kelas -->
                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas *</label>
                            <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" 
                                   value="<?php echo htmlspecialchars($kelas['nama_kelas']); ?>" 
                                   placeholder="Contoh: Matematika XII IPA 1" required>
                        </div>
                        
                        <!-- Guru (untuk admin) -->
                        <?php if ($user['peran'] === 'admin' && !empty($guruList)): ?>
                        <div class="mb-3">
                            <label for="id_guru" class="form-label">Guru Pengajar *</label>
                            <select class="form-select" id="id_guru" name="id_guru" required>
                                <option value="">Pilih Guru</option>
                                <?php foreach ($guruList as $guru): ?>
                                <option value="<?php echo $guru['id']; ?>" 
                                        <?php echo $guru['id'] == $kelas['id_guru'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($guru['nama']); ?> 
                                    (<?php echo htmlspecialchars($guru['email']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi Kelas</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Jelaskan tentang kelas ini..."><?php echo htmlspecialchars($kelas['deskripsi'] ?? ''); ?></textarea>
                            <small class="text-muted">Opsional: Tambahkan informasi tentang kelas, jadwal, atau catatan penting</small>
                        </div>
                        
                        <!-- Kode Kelas (readonly) -->
                        <div class="mb-3">
                            <label class="form-label">Kode Kelas</label>
                            <input type="text" class="form-control" value="<?php echo $kelas['kode_kelas']; ?>" readonly>
                            <small class="text-muted">Kode kelas tidak dapat diubah</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Info:</strong> Perubahan akan langsung terlihat oleh semua siswa di kelas ini.
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="kelas.php?action=detail&id=<?php echo $kelas['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="button" class="btn btn-danger ms-auto" 
                                    onclick="deleteKelas(<?php echo $kelas['id']; ?>)">
                                <i class="fas fa-trash me-2"></i>Hapus Kelas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Info tambahan -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6>Informasi Kelas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Total Siswa:</strong> 
                                <?php 
                                require_once __DIR__ . '/../../models/KelasModel.php';
                                $kelasModel = new KelasModel();
                                $siswa = $kelasModel->getSiswaInKelas($kelas['id']);
                                echo count($siswa);
                                ?>
                            </p>
                            <p><strong>Dibuat:</strong> <?php echo formatDateTime($kelas['created_at']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Terakhir Update:</strong> <?php echo formatDateTime($kelas['updated_at']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteKelas(id) {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini?\n\nSemua data terkait akan ikut terhapus:\n- Materi pembelajaran\n- Tugas dan jawaban\n- Post forum\n- Data siswa di kelas ini\n\nTindakan ini tidak dapat dibatalkan!')) {
        if (confirm('Konfirmasi sekali lagi: Hapus kelas ini?')) {
            window.location.href = 'kelas.php?action=delete&id=' + id;
        }
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>