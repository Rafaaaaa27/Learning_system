<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Dashboard'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                EduLearn
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>kelas.php">
                            <i class="fas fa-chalkboard me-1"></i>Kelas
                        </a>
                    </li>
                    
                    <?php if ($currentUser['peran'] === 'siswa'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>tugas.php">
                            <i class="fas fa-tasks me-1"></i>Tugas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>forum.php">
                            <i class="fas fa-comments me-1"></i>Forum
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>leaderboard.php">
                            <i class="fas fa-trophy me-1"></i>Leaderboard
                        </a>
                    </li>
                    <?php elseif ($currentUser['peran'] === 'guru'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>tugas.php">
                            <i class="fas fa-tasks me-1"></i>Tugas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>materi.php">
                            <i class="fas fa-book me-1"></i>Materi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>forum.php">
                            <i class="fas fa-comments me-1"></i>Forum
                        </a>
                    </li>
                    <?php elseif ($currentUser['peran'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Admin Panel
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>Manajemen
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>admin.php?action=users">
                                    <i class="fas fa-users me-2"></i>Kelola User
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>kelas.php">
                                    <i class="fas fa-chalkboard me-2"></i>Kelola Kelas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>tugas.php">
                                    <i class="fas fa-tasks me-2"></i>Kelola Tugas
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>admin.php?action=reports">
                                    <i class="fas fa-chart-bar me-2"></i>Laporan Sistem
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Gamifikasi Badge (Siswa) -->
                    <?php if ($currentUser['peran'] === 'siswa'): ?>
                    <li class="nav-item me-3 d-flex align-items-center">
                        <div class="text-white">
                            <i class="fas fa-star text-warning"></i>
                            <span id="user-poin" class="fw-bold">0</span> Poin
                        </div>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Notifikasi -->
                    <li class="nav-item dropdown me-2" id="notif-dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="notification-badge" id="notif-badge" style="display:none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifikasi</span>
                                <a href="#" class="small" onclick="markAllAsRead(); return false;">Tandai Semua Dibaca</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <div id="notif-list">
                                <li><span class="dropdown-item text-center text-muted">Loading...</span></li>
                            </div>
                        </ul>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <img src="<?php echo UPLOAD_URL . 'profiles/' . $currentUser['foto']; ?>" 
                                 class="avatar me-2" 
                                 alt="<?php echo htmlspecialchars($currentUser['nama']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                            <span><?php echo htmlspecialchars($currentUser['nama']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a>
                            </li>
                            <?php if ($currentUser['peran'] === 'siswa'): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>laporan.php">
                                    <i class="fas fa-chart-line me-2"></i>Laporan
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <?php 
    // Display flash message
    $message = getMessage();
    if ($message): 
    ?>
    <div class="container-fluid mt-3">
        <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Function untuk mark all notifikasi as read
        function markAllAsRead() {
            fetch('<?php echo BASE_URL; ?>api/notifikasi.php?action=mark_all_read', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#notif-badge').hide();
                    location.reload();
                }
            });
        }
        
        // Load user poin untuk siswa
        <?php if ($currentUser['peran'] === 'siswa'): ?>
        fetch('<?php echo BASE_URL; ?>api/gamifikasi.php?action=get_poin')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('user-poin').textContent = data.poin;
                }
            });
        <?php endif; ?>
    </script>