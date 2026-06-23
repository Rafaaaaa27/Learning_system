<?php require_once __DIR__ . '/../layouts/header.php'; ?>

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
                    <a href="admin.php?page=logs" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list me-2"></i>System Logs
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
                <a href="admin.php?page=create_user" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah User
                </a>
            </div>
            
            <!-- Filter & Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="admin.php" method="GET" class="row g-3">
                        <input type="hidden" name="page" value="users">
                        
                        <div class="col-md-4">
                            <label class="form-label">Filter Role</label>
                            <select name="role" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $current_role === 'all' ? 'selected' : ''; ?>>Semua Role</option>
                                <option value="admin" <?php echo $current_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="guru" <?php echo $current_role === 'guru' ? 'selected' : ''; ?>>Guru</option>
                                <option value="siswa" <?php echo $current_role === 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Cari User</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari nama atau email..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar User (<?php echo count($users); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada user ditemukan</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Sekolah</th>
                                    <th>Terdaftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <img src="<?php echo UPLOAD_URL . 'profiles/' . $user['foto']; ?>" 
                                             class="avatar-sm rounded-circle" 
                                             alt="<?php echo htmlspecialchars($user['nama']); ?>"
                                             onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['nama']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $user['peran'] === 'admin' ? 'warning' : 
                                                ($user['peran'] === 'guru' ? 'info' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($user['peran']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['sekolah'] ?? '-'); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo formatDate($user['created_at']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="profile.php?user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Lihat Profil">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="admin.php?page=edit_user&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $currentUser['id']): ?>
                                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                                    class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteUser(id) {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?\n\nSemua data terkait (tugas, forum, dll) akan ikut terhapus!')) {
        window.location.href = 'admin.php?page=delete_user&id=' + id;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>