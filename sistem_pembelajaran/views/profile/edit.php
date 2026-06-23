<?php 
require_once __DIR__ . '/../layouts/header.php'; 

$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_errors']);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-edit me-2"></i>Edit Profil</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form action="profile.php?action=update" method="POST" enctype="multipart/form-data">
                        <!-- Profile Photo -->
                        <div class="text-center mb-4">
                            <img src="<?php echo UPLOAD_URL . 'profiles/' . $profile['foto']; ?>" 
                                 class="avatar-lg rounded-circle mb-3" 
                                 id="preview-foto"
                                 alt="Profile Photo"
                                 onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                            
                            <div>
                                <label for="foto" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-camera me-2"></i>Ubah Foto
                                </label>
                                <input type="file" class="d-none image-upload" id="foto" name="foto" 
                                       accept="image/*" data-preview="#preview-foto">
                                <p class="text-muted small mt-2 mb-0">
                                    Max 2MB, format: JPG, JPEG, PNG, GIF
                                </p>
                            </div>
                        </div>
                        
                        <!-- Nama -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?php echo htmlspecialchars($profile['nama']); ?>" required>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                        </div>
                        
                        <!-- Sekolah -->
                        <div class="mb-3">
                            <label for="sekolah" class="form-label">Nama Sekolah</label>
                            <input type="text" class="form-control" id="sekolah" name="sekolah" 
                                   value="<?php echo htmlspecialchars($profile['sekolah'] ?? ''); ?>">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-lock me-2"></i>Ubah Password</h5>
                </div>
                <div class="card-body">
                    <form action="profile.php?action=change_password" method="POST" id="form-change-password">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Lama *</label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru *</label>
                            <input type="password" class="form-control" id="new_password" 
                                   name="new_password" minlength="6" required>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="password-strength-bar-new"></div>
                            </div>
                            <small class="text-muted" id="password-strength-text-new"></small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Ubah Password
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Delete Account Card -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Zona Bahaya</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Setelah akun dihapus, semua data Anda akan hilang secara permanen. 
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                    
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash me-2"></i>Hapus Akun
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="profile.php?action=delete" method="POST">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    
                    <p>Semua data Anda termasuk:</p>
                    <ul>
                        <li>Profil dan informasi pribadi</li>
                        <li>Jawaban tugas</li>
                        <li>Post forum</li>
                        <li>Poin dan badge</li>
                    </ul>
                    <p>akan dihapus secara permanen.</p>
                    
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">
                            Masukkan password untuk konfirmasi:
                        </label>
                        <input type="password" class="form-control" id="delete_password" 
                               name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Ya, Hapus Akun Saya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password strength checker
$('#new_password').on('input', function() {
    const password = $(this).val();
    const strengthBar = $('#password-strength-bar-new');
    const strengthText = $('#password-strength-text-new');
    
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    strengthBar.removeClass('weak medium strong');
    
    if (strength < 2) {
        strengthBar.addClass('weak');
        strengthText.text('Lemah').css('color', 'var(--danger)');
    } else if (strength < 4) {
        strengthBar.addClass('medium');
        strengthText.text('Sedang').css('color', 'var(--warning)');
    } else {
        strengthBar.addClass('strong');
        strengthText.text('Kuat').css('color', 'var(--success)');
    }
});

// Password confirmation validation
$('#confirm_password').on('input', function() {
    const password = $('#new_password').val();
    const confirm = $(this).val();
    
    if (confirm && password !== confirm) {
        $(this)[0].setCustomValidity('Password tidak cocok');
        $(this).addClass('is-invalid');
    } else {
        $(this)[0].setCustomValidity('');
        $(this).removeClass('is-invalid');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>