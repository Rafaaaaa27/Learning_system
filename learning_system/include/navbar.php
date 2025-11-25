<?php
// Navbar component untuk semua halaman dashboard
if (!isset($user)) {
    $user = getCurrentUser();
}
$peran = $user['peran'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-graduation-cap"></i> Learning System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'kelas.php' ? 'active' : '' ?>" href="kelas.php">
                        <i class="fas fa-chalkboard"></i> Kelas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'tugas.php' ? 'active' : '' ?>" href="tugas.php">
                        <i class="fas fa-tasks"></i> Tugas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'forum.php' ? 'active' : '' ?>" href="forum.php">
                        <i class="fas fa-comments"></i> Forum
                    </a>
                </li>
                <?php if ($peran === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin.php' ? 'active' : '' ?>" href="admin.php">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notifCount" style="display:none;">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="notifDropdown" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Notifikasi</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center text-muted" href="#">Memuat...</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['nama']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-item-text">
                                <small class="text-muted d-block"><?= ucfirst($peran) ?></small>
                                <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                        <?php if ($peran === 'siswa'): ?>
                        <li><a class="dropdown-item" href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>