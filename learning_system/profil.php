<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Update profile
    if ($action === 'update_profile') {
        $nama = clean($_POST['nama']);
        $email = clean($_POST['email']);
        $sekolah = clean($_POST['sekolah']);
        
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        
        if ($stmt->fetch()) {
            $error = 'Email sudah digunakan oleh user lain!';
        } else {
            // Handle photo upload
            $foto = $user['foto'];
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['foto'], 'foto');
                if ($upload['success']) {
                    $foto = $upload['filename'];
                    
                    // Delete old photo if not default
                    if ($user['foto'] !== 'default.jpg' && file_exists(UPLOAD_DIR . 'foto/' . $user['foto'])) {
                        unlink(UPLOAD_DIR . 'foto/' . $user['foto']);
                    }
                }
            }
            
            $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, sekolah = ?, foto = ? WHERE id = ?");
            if ($stmt->execute([$nama, $email, $sekolah, $foto, $user['id']])) {
                $success = 'Profil berhasil diupdate!';
                $user = getCurrentUser(); // Refresh user data
            }
        }
    }
    
    // Change password
    elseif ($action === 'change_password') {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (!password_verify($oldPassword, $user['password'])) {
            $error = 'Password lama salah!';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Password baru tidak cocok!';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashedPassword, $user['id']])) {
                $success = 'Password berhasil diubah!';
            }
        }
    }
    
    // Delete account
    elseif ($action === 'delete_account') {
        $confirmPassword = $_POST['confirm_password'];
        
        if (!password_verify($confirmPassword, $user['password'])) {
            $error = 'Password salah!';
        } else {
            // Delete user and all related data (cascade will handle it)
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                session_destroy();
                header('Location: index.php?message=account_deleted');
                exit;
            }
        }
    }
}

// Get user statistics
$stats = [];
if ($user['peran'] === 'siswa') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM siswa_kelas WHERE id_siswa = ?");
    $stmt->execute([$user['id']]);
    $stats['kelas'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jawaban_tugas WHERE id_siswa = ?");
    $stmt->execute([$user['id']]);
    $stats['tugas'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT poin, badges FROM gamifikasi WHERE id_user = ?");
    $stmt->execute([$user['id']]);
    $gamifikasi = $stmt->fetch();
    $stats['poin'] = $gamifikasi['poin'] ?? 0;
    $stats['badges'] = json_decode($gamifikasi['badges'] ?? '[]', true);
} elseif ($user['peran'] === 'guru') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kelas WHERE id_guru = ?");
    $stmt->execute([$user['id']]);
    $stats['kelas'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM tugas t
        JOIN kelas k ON t.id_kelas = k.id
        WHERE k.id_guru = ?
    ");
    $stmt->execute([$user['id']]);
    $stats['tugas'] = $stmt->fetch()['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <h2 class="mb-4"><i class="fas fa-user-circle"></i> Profil Saya</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column - Profile Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="uploads/foto/<?= $user['foto'] ?>" 
                             onerror="this.src='assets/images/default.jpg'"
                             class="rounded-circle mb-3" 
                             width="150" height="150" 
                             alt="Profile Photo"
                             style="object-fit: cover;">
                        <h4><?= htmlspecialchars($user['nama']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                        <span class="badge bg-primary"><?= ucfirst($user['peran']) ?></span>
                        <?php if ($user['sekolah']): ?>
                            <p class="text-muted mt-2 mb-0"><i class="fas fa-school"></i> <?= htmlspecialchars($user['sekolah']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Statistik
                    </div>
                    <div class="card-body">
                        <?php if ($user['peran'] === 'siswa'): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Kelas:</span>
                                <strong><?= $stats['kelas'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tugas Selesai:</span>
                                <strong><?= $stats['tugas'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Poin:</span>
                                <strong><?= $stats['poin'] ?></strong>
                            </div>
                            <div class="mt-3">
                                <strong>Badges:</strong><br>
                                <?php if (empty($stats['badges'])): ?>
                                    <small class="text-muted">Belum ada badge</small>
                                <?php else: ?>
                                    <?php foreach ($stats['badges'] as $badge): ?>
                                        <span class="badge-custom me-1 mb-1 d-inline-block">
                                            <i class="fas fa-medal"></i> <?= htmlspecialchars($badge) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($user['peran'] === 'guru'): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Kelas:</span>
                                <strong><?= $stats['kelas'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Tugas:</span>
                                <strong><?= $stats['tugas'] ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Bergabung:</span>
                            <strong><?= formatDate($user['created_at']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Forms -->
            <div class="col-md-8">
                <!-- Update Profile Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-edit"></i> Edit Profil
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sekolah</label>
                                <input type="text" class="form-control" name="sekolah" value="<?= htmlspecialchars($user['sekolah'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" name="foto" accept="image/*" onchange="previewFile(this)">
                                <small class="text-muted">Format: JPG, PNG (Max 5MB)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change Password Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-key"></i> Ubah Password
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" class="form-control" name="old_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Ubah Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-triangle"></i> Zona Berbahaya
                    </div>
                    <div class="card-body">
                        <h6>Hapus Akun</h6>
                        <p class="text-muted">Tindakan ini tidak dapat dibatalkan. Semua data Anda akan dihapus permanen.</p>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="fas fa-trash"></i> Hapus Akun
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_account">
                        
                        <div class="alert alert-danger">
                            <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan. Semua data Anda termasuk:
                            <ul class="mb-0 mt-2">
                                <li>Profil dan foto</li>
                                <li>Kelas dan materi</li>
                                <li>Tugas dan jawaban</li>
                                <li>Post forum</li>
                                <li>Poin dan badge</li>
                            </ul>
                            akan dihapus permanen!
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Masukkan password untuk konfirmasi:</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus akun? Tindakan ini TIDAK DAPAT dibatalkan!')">
                            <i class="fas fa-trash"></i> Ya, Hapus Akun Saya
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>