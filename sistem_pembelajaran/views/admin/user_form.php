<?php 
require_once __DIR__ . '/../layouts/header.php'; 

$errors = $_SESSION['admin_errors'] ?? [];
$oldInput = $_SESSION['old_admin_input'] ?? [];
unset($_SESSION['admin_errors']);
unset($_SESSION['old_admin_input']);
?>

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
                    <a href="admin.php?page=kelas" class="list-group-item list-group-item-action">
                        <i class="fas fa-chalkboard me-2"></i>Manajemen Kelas
                    </a>
                    <a href="admin.php?page=reports" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i>Laporan
                    </a>
                    <a href="admin.php?page=settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-sliders-h me-2"></i>Pengaturan
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-<?php echo $is_edit ? 'edit' : 'plus'; ?> me-2"></i>
                                <?php echo $is_edit ? 'Edit User' : 'Tambah User Baru'; ?>
                            </h4>
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
                            
                            <form action="admin.php?page=<?php echo $is_edit ? 'update_user&id=' . $user_data['id'] : 'store_user'; ?>" 
                                  method="POST" class="needs-validation" novalidate>
                                
                                <!-- Nama -->
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama" name="nama" 
                                           value="<?php echo htmlspecialchars($user_data['nama'] ?? $oldInput['nama'] ?? ''); ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        Nama harus diisi
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user_data['email'] ?? $oldInput['email'] ?? ''); ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        Email harus diisi dengan format yang benar
                                    </div>
                                </div>
                                
                                <!-- Role -->
                                <div class="mb-3">
                                    <label for="peran" class="form-label">Role *</label>
                                    <select class="form-select" id="peran" name="peran" required>
                                        <option value="">Pilih Role</option>
                                        <option value="admin" <?php echo (($user_data['peran'] ?? $oldInput['peran'] ?? '') === 'admin') ? 'selected' : ''; ?>>
                                            Admin
                                        </option>
                                        <option value="guru" <?php echo (($user_data['peran'] ?? $oldInput['peran'] ?? '') === 'guru') ? 'selected' : ''; ?>>
                                            Guru
                                        </option>
                                        <option value="siswa" <?php echo (($user_data['peran'] ?? $oldInput['peran'] ?? '') === 'siswa') ? 'selected' : ''; ?>>
                                            Siswa
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Role harus dipilih
                                    </div>
                                </div>
                                
                                <!-- Sekolah -->
                                <div class="mb-3">
                                    <label for="sekolah" class="form-label">Nama Sekolah</label>
                                    <input type="text" class="form-control" id="sekolah" name="sekolah" 
                                           value="<?php echo htmlspecialchars($user_data['sekolah'] ?? $oldInput['sekolah'] ?? ''); ?>">
                                </div>
                                
                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        Password <?php echo !$is_edit ? '*' : '(Kosongkan jika tidak diubah)'; ?>
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" <?php echo !$is_edit ? 'required' : ''; ?>>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                    <div class="invalid-feedback">
                                        Password minimal 6 karakter
                                    </div>
                                </div>
                                
                                <?php if (!$is_edit): ?>
                                <!-- Confirm Password (hanya untuk create) -->
                                <div class="mb-3">
                                    <label for="password_confirm" class="form-label">Konfirmasi Password *</label>
                                    <input type="password" class="form-control" id="password_confirm" 
                                           name="password_confirm" required>
                                    <div class="invalid-feedback">
                                        Konfirmasi password harus sama dengan password
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $is_edit ? 'Update User' : 'Tambah User'; ?>
                                    </button>
                                    <a href="admin.php?page=users" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            <?php if (!$is_edit): ?>
            // Check password match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirm').value;
            
            if (password !== confirmPassword) {
                event.preventDefault();
                event.stopPropagation();
                document.getElementById('password_confirm').setCustomValidity('Password tidak cocok');
            } else {
                document.getElementById('password_confirm').setCustomValidity('');
            }
            <?php endif; ?>
            
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();

<?php if (!$is_edit): ?>
// Password confirmation validation
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    
    if (confirm && password !== confirm) {
        this.setCustomValidity('Password tidak cocok');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>