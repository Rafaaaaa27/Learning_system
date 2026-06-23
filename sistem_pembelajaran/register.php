<?php
/**
 * Register Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'controllers/AuthController.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
}

// Process register form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new AuthController();
    $authController->processRegister();
}

$pageTitle = 'Daftar - ' . SITE_NAME;
$message = getMessage();
$errors = $_SESSION['register_errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];

// Clear session errors
unset($_SESSION['register_errors']);
unset($_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #ADD8E6 0%, #87CEEB 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .register-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-floating > .form-control,
        .form-floating > .form-select {
            border-radius: 8px;
        }
        
        .btn-register {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            transition: var(--transition);
        }
        
        .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        
        .password-strength {
            height: 5px;
            border-radius: 3px;
            background: #ddd;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s;
            width: 0;
        }
        
        .password-strength-bar.weak {
            width: 33%;
            background: var(--danger);
        }
        
        .password-strength-bar.medium {
            width: 66%;
            background: var(--warning);
        }
        
        .password-strength-bar.strong {
            width: 100%;
            background: var(--success);
        }
    </style>
</head>
<body>
    
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h3>Daftar Akun Baru</h3>
                <p class="mb-0">Bergabunglah dengan EduLearn</p>
            </div>
            
            <div class="register-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form action="register.php" method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($oldInput['nama'] ?? ''); ?>" required>
                                <label for="nama"><i class="fas fa-user me-2"></i>Nama Lengkap</label>
                                <div class="invalid-feedback">
                                    Nama lengkap harus diisi.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="name@example.com" value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>" required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                                <div class="invalid-feedback">
                                    Email harus diisi dengan format yang benar.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-floating">
                                <select class="form-select" id="peran" name="peran" required>
                                    <option value="">Pilih Peran</option>
                                    <option value="siswa" <?php echo ($oldInput['peran'] ?? '') === 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                                    <option value="guru" <?php echo ($oldInput['peran'] ?? '') === 'guru' ? 'selected' : ''; ?>>Guru</option>
                                    <option value="admin" <?php echo ($oldInput['peran'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <label for="peran"><i class="fas fa-user-tag me-2"></i>Daftar Sebagai</label>
                                <div class="invalid-feedback">
                                    Pilih peran Anda.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="sekolah" name="sekolah" 
                                       placeholder="Nama Sekolah" value="<?php echo htmlspecialchars($oldInput['sekolah'] ?? ''); ?>">
                                <label for="sekolah"><i class="fas fa-school me-2"></i>Nama Sekolah (Opsional)</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Password" minlength="6" required>
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                <div class="invalid-feedback">
                                    Password minimal 6 karakter.
                                </div>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="password-strength-bar"></div>
                            </div>
                            <small class="text-muted" id="password-strength-text"></small>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                       placeholder="Konfirmasi Password" required>
                                <label for="password_confirm"><i class="fas fa-lock me-2"></i>Konfirmasi Password</label>
                                <div class="invalid-feedback">
                                    Konfirmasi password harus sama.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            Saya setuju dengan <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Syarat & Ketentuan</a>
                        </label>
                        <div class="invalid-feedback">
                            Anda harus menyetujui syarat dan ketentuan.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-register mt-4">
                        <i class="fas fa-user-plus me-2"></i>Daftar
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <p class="mb-0">Sudah punya akun?</p>
                    <a href="login.php" class="btn btn-outline-primary mt-2 w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3 text-white">
            <small>&copy; <?php echo date('Y'); ?> EduLearn. All Rights Reserved.</small>
        </div>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Syarat & Ketentuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Ketentuan Umum</h6>
                    <p>Dengan mendaftar di EduLearn, Anda setuju untuk mematuhi semua ketentuan yang berlaku.</p>
                    
                    <h6>2. Privasi Data</h6>
                    <p>Data pribadi Anda akan dilindungi sesuai kebijakan privasi kami.</p>
                    
                    <h6>3. Penggunaan Platform</h6>
                    <p>Platform ini hanya untuk keperluan pembelajaran. Dilarang menyalahgunakan fitur yang tersedia.</p>
                    
                    <h6>4. Tanggung Jawab Pengguna</h6>
                    <p>Pengguna bertanggung jawab atas aktivitas yang dilakukan menggunakan akun mereka.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    // Check password match
                    const password = $('#password').val();
                    const confirmPassword = $('#password_confirm').val();
                    
                    if (password !== confirmPassword) {
                        event.preventDefault();
                        event.stopPropagation();
                        $('#password_confirm')[0].setCustomValidity('Password tidak cocok');
                    } else {
                        $('#password_confirm')[0].setCustomValidity('');
                    }
                    
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Password strength checker
        $('#password').on('input', function() {
            const password = $(this).val();
            const strengthBar = $('#password-strength-bar');
            const strengthText = $('#password-strength-text');
            
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
        $('#password_confirm').on('input', function() {
            const password = $('#password').val();
            const confirm = $(this).val();
            
            if (confirm && password !== confirm) {
                $(this)[0].setCustomValidity('Password tidak cocok');
                $(this).addClass('is-invalid');
            } else {
                $(this)[0].setCustomValidity('');
                $(this).removeClass('is-invalid');
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
    
</body>
</html>