<?php
require_once 'config.php';
require_once 'functions.php';

requireRole('admin');

$user = getCurrentUser();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Delete user
    if ($action === 'delete_user') {
        $userId = $_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$userId])) {
            $success = "User berhasil dihapus!";
        }
    }
    
    // Update user role
    elseif ($action === 'update_role') {
        $userId = $_POST['user_id'];
        $peran = $_POST['peran'];
        $stmt = $pdo->prepare("UPDATE users SET peran = ? WHERE id = ?");
        if ($stmt->execute([$peran, $userId])) {
            $success = "Role user berhasil diubah!";
        }
    }
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE peran = 'guru'");
$totalGuru = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE peran = 'siswa'");
$totalSiswa = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM kelas");
$totalKelas = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tugas");
$totalTugas = $stmt->fetch()['total'];

// Get all users
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($filter !== 'all') {
    $query .= " AND peran = ?";
    $params[] = $filter;
}

if ($search) {
    $query .= " AND (nama LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get recent activities
$stmt = $pdo->query("
    SELECT 'user_registered' as type, nama as detail, created_at
    FROM users
    UNION ALL
    SELECT 'class_created' as type, nama_kelas as detail, created_at
    FROM kelas
    UNION ALL
    SELECT 'assignment_created' as type, judul as detail, created_at
    FROM tugas
    ORDER BY created_at DESC
    LIMIT 10
");
$activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <h2 class="mb-4"><i class="fas fa-cog"></i> Admin Dashboard</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?= $totalUsers ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3><?= $totalGuru ?></h3>
                    <p>Guru</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3><?= $totalSiswa ?></h3>
                    <p>Siswa</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-chalkboard"></i>
                    <h3><?= $totalKelas ?></h3>
                    <p>Kelas</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3><?= $totalTugas ?></h3>
                    <p>Tugas</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - User Management -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-users"></i> Manajemen User</span>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus"></i> Tambah User
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter & Search -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" onchange="location.href='admin.php?filter=' + this.value + '&search=<?= $search ?>'">
                                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Semua Role</option>
                                    <option value="admin" <?= $filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="guru" <?= $filter === 'guru' ? 'selected' : '' ?>>Guru</option>
                                    <option value="siswa" <?= $filter === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <form method="GET" class="input-group">
                                    <input type="hidden" name="filter" value="<?= $filter ?>">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- User Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Sekolah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($u['nama']) ?></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $u['peran'] === 'admin' ? 'danger' : ($u['peran'] === 'guru' ? 'primary' : 'info') ?>">
                                                    <?= ucfirst($u['peran']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($u['sekolah'] ?? '-') ?></td>
                                            <td>
                                                <?php if ($u['id'] != $user['id']): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editRole(<?= $u['id'] ?>, '<?= $u['peran'] ?>', '<?= htmlspecialchars($u['nama']) ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">You</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Activities -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Aktivitas Terbaru
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($activities as $activity): ?>
                            <div class="notification-item">
                                <small class="text-muted d-block"><?= timeAgo($activity['created_at']) ?></small>
                                <p class="mb-0">
                                    <?php
                                    switch ($activity['type']) {
                                        case 'user_registered':
                                            echo '<i class="fas fa-user-plus text-success"></i> ' . htmlspecialchars($activity['detail']) . ' bergabung';
                                            break;
                                        case 'class_created':
                                            echo '<i class="fas fa-chalkboard text-primary"></i> Kelas baru: ' . htmlspecialchars($activity['detail']);
                                            break;
                                        case 'assignment_created':
                                            echo '<i class="fas fa-tasks text-warning"></i> Tugas baru: ' . htmlspecialchars($activity['detail']);
                                            break;
                                    }
                                    ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- System Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> Informasi Sistem
                    </div>
                    <div class="card-body">
                        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                        <p><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?></p>
                        <p><strong>Database:</strong> MySQL</p>
                        <p class="mb-0"><strong>Upload Max:</strong> <?= ini_get('upload_max_filesize') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Form (Hidden) -->
    <form id="deleteUserForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="user_id" id="deleteUserId">
    </form>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Role User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" id="editUserId">
                        
                        <p><strong>User:</strong> <span id="editUserName"></span></p>
                        
                        <div class="mb-3">
                            <label class="form-label">Role Baru</label>
                            <select class="form-select" name="peran" id="editUserRole" required>
                                <option value="admin">Admin</option>
                                <option value="guru">Guru</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function deleteUser(id, name) {
            if (confirm('Hapus user "' + name + '"? Tindakan ini tidak dapat dibatalkan!')) {
                $('#deleteUserId').val(id);
                $('#deleteUserForm').submit();
            }
        }

        function editRole(id, role, name) {
            $('#editUserId').val(id);
            $('#editUserName').text(name);
            $('#editUserRole').val(role);
            new bootstrap.Modal(document.getElementById('editRoleModal')).show();
        }
    </script>
</body>
</html>