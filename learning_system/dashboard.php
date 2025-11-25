<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser();
$peran = $user['peran'];

// Get statistics based on role
if ($peran === 'siswa') {
    // Siswa stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM siswa_kelas WHERE id_siswa = ?");
    $stmt->execute([$user['id']]);
    $totalKelas = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT jt.id_tugas) as total
        FROM jawaban_tugas jt
        JOIN tugas t ON jt.id_tugas = t.id
        WHERE jt.id_siswa = ?
    ");
    $stmt->execute([$user['id']]);
    $totalTugasSelesai = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT AVG(nilai) as rata
        FROM jawaban_tugas
        WHERE id_siswa = ? AND nilai IS NOT NULL
    ");
    $stmt->execute([$user['id']]);
    $rataRataNilai = round($stmt->fetch()['rata'] ?? 0);
    
    $stmt = $pdo->prepare("SELECT poin FROM gamifikasi WHERE id_user = ?");
    $stmt->execute([$user['id']]);
    $poin = $stmt->fetch()['poin'] ?? 0;
    
} elseif ($peran === 'guru') {
    // Guru stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kelas WHERE id_guru = ?");
    $stmt->execute([$user['id']]);
    $totalKelas = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM siswa_kelas sk
        JOIN kelas k ON sk.id_kelas = k.id
        WHERE k.id_guru = ?
    ");
    $stmt->execute([$user['id']]);
    $totalSiswa = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM tugas t
        JOIN kelas k ON t.id_kelas = k.id
        WHERE k.id_guru = ?
    ");
    $stmt->execute([$user['id']]);
    $totalTugas = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM materi m
        JOIN kelas k ON m.id_kelas = k.id
        WHERE k.id_guru = ?
    ");
    $stmt->execute([$user['id']]);
    $totalMateri = $stmt->fetch()['total'];
}

// Get recent activities
$stmt = $pdo->prepare("
    SELECT * FROM notifikasi
    WHERE id_user = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$notifications = $stmt->fetchAll();

// Get user's classes
$classes = getUserClasses($user['id'], $peran);

// Get upcoming assignments for siswa
if ($peran === 'siswa') {
    $stmt = $pdo->prepare("
        SELECT t.*, k.nama_kelas FROM tugas t
        JOIN kelas k ON t.id_kelas = k.id
        JOIN siswa_kelas sk ON k.id = sk.id_kelas
        WHERE sk.id_siswa = ? AND t.deadline > NOW()
        ORDER BY t.deadline ASC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $upcomingTugas = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
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
                        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelas.php"><i class="fas fa-chalkboard"></i> Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tugas.php"><i class="fas fa-tasks"></i> Tugas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="forum.php"><i class="fas fa-comments"></i> Forum</a>
                    </li>
                    <?php if ($peran === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php"><i class="fas fa-cog"></i> Admin</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle position-relative" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notifCount">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="notifDropdown">
                            <li><h6 class="dropdown-header">Notifikasi</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">Tidak ada notifikasi baru</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['nama']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <h2 class="mb-4">Dashboard <?= ucfirst($peran) ?></h2>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php if ($peran === 'siswa'): ?>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h3><?= $totalKelas ?></h3>
                        <p>Total Kelas</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <h3><?= $totalTugasSelesai ?></h3>
                        <p>Tugas Selesai</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-star"></i>
                        <h3><?= $rataRataNilai ?></h3>
                        <p>Rata-rata Nilai</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-trophy"></i>
                        <h3><?= $poin ?></h3>
                        <p>Total Poin</p>
                    </div>
                </div>
            <?php elseif ($peran === 'guru'): ?>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-chalkboard"></i>
                        <h3><?= $totalKelas ?></h3>
                        <p>Total Kelas</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3><?= $totalSiswa ?></h3>
                        <p>Total Siswa</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-tasks"></i>
                        <h3><?= $totalTugas ?></h3>
                        <p>Total Tugas</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-book"></i>
                        <h3><?= $totalMateri ?></h3>
                        <p>Total Materi</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Statistik Belajar
                    </div>
                    <div class="card-body">
                        <canvas id="learningChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Upcoming Assignments (for siswa) -->
                <?php if ($peran === 'siswa' && !empty($upcomingTugas)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt"></i> Tugas Mendatang
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($upcomingTugas as $tugas): ?>
                                <a href="tugas.php?id=<?= $tugas['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($tugas['judul']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($tugas['nama_kelas']) ?></small>
                                        </div>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> <?= formatDate($tugas['deadline']) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Classes -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard"></i> Kelas Saya
                    </div>
                    <div class="card-body">
                        <?php if (empty($classes)): ?>
                            <p class="text-muted">Belum ada kelas.</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($classes as $kelas): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($kelas['nama_kelas']) ?></h5>
                                                <p class="card-text text-muted"><?= htmlspecialchars($kelas['deskripsi'] ?? 'Tidak ada deskripsi') ?></p>
                                                <a href="kelas.php?id=<?= $kelas['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-arrow-right"></i> Buka Kelas
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Recent Notifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-bell"></i> Notifikasi Terbaru
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <p class="p-3 text-muted mb-0">Tidak ada notifikasi.</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="notification-item <?= $notif['read_status'] ? '' : 'unread' ?>">
                                    <small class="text-muted d-block"><?= timeAgo($notif['created_at']) ?></small>
                                    <p class="mb-0"><?= htmlspecialchars($notif['pesan']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gamification -->
                <?php if ($peran === 'siswa'): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-trophy"></i> Pencapaian
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM gamifikasi WHERE id_user = ?");
                        $stmt->execute([$user['id']]);
                        $gamifikasi = $stmt->fetch();
                        $badges = $gamifikasi ? json_decode($gamifikasi['badges'], true) : [];
                        ?>
                        <div class="text-center mb-3">
                            <h3 class="text-primary"><?= $gamifikasi['poin'] ?? 0 ?> Poin</h3>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= min(($gamifikasi['poin'] ?? 0) / 10, 100) ?>%"></div>
                            </div>
                        </div>
                        <h6>Badge:</h6>
                        <?php if (empty($badges)): ?>
                            <p class="text-muted">Belum ada badge.</p>
                        <?php else: ?>
                            <?php foreach ($badges as $badge): ?>
                                <span class="badge-custom me-2 mb-2 d-inline-block">
                                    <i class="fas fa-medal"></i> <?= htmlspecialchars($badge) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
    <script>
        // Chart
        const ctx = document.getElementById('learningChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Aktivitas Belajar',
                    data: [12, 19, 15, 25, 22, 30, 28],
                    borderColor: '#ADD8E6',
                    backgroundColor: 'rgba(173, 216, 230, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>