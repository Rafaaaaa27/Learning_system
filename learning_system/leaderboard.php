<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser();

// Get filter
$kelasId = $_GET['kelas'] ?? 'all';

// Get leaderboard
if ($kelasId === 'all') {
    // Global leaderboard
    $stmt = $pdo->query("
        SELECT u.id, u.nama, u.foto, u.sekolah, g.poin, g.badges
        FROM users u
        LEFT JOIN gamifikasi g ON u.id = g.id_user
        WHERE u.peran = 'siswa'
        ORDER BY g.poin DESC
        LIMIT 50
    ");
    $leaderboard = $stmt->fetchAll();
} else {
    // Class-specific leaderboard
    $stmt = $pdo->prepare("
        SELECT u.id, u.nama, u.foto, u.sekolah, g.poin, g.badges
        FROM users u
        JOIN siswa_kelas sk ON u.id = sk.id_siswa
        LEFT JOIN gamifikasi g ON u.id = g.id_user
        WHERE sk.id_kelas = ?
        ORDER BY g.poin DESC
    ");
    $stmt->execute([$kelasId]);
    $leaderboard = $stmt->fetchAll();
}

// Get user's classes for filter
$userClasses = getUserClasses($user['id'], $user['peran']);

// Get user's rank
$userRank = 0;
foreach ($leaderboard as $index => $entry) {
    if ($entry['id'] == $user['id']) {
        $userRank = $index + 1;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="text-center mb-4">
            <h1><i class="fas fa-trophy text-warning"></i> Leaderboard</h1>
            <p class="text-muted">Kompetisi Poin & Pencapaian</p>
        </div>

        <!-- Filter -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <select class="form-select" onchange="location.href='leaderboard.php?kelas=' + this.value">
                    <option value="all" <?= $kelasId === 'all' ? 'selected' : '' ?>>🌍 Global Leaderboard</option>
                    <?php foreach ($userClasses as $kelas): ?>
                        <option value="<?= $kelas['id'] ?>" <?= $kelasId == $kelas['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kelas['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($user['peran'] === 'siswa' && $userRank > 0): ?>
        <!-- Your Position -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5>Posisi Anda</h5>
                        <h2 class="text-primary">#<?= $userRank ?></h2>
                        <p class="mb-0">Tetap semangat! 💪</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Top 3 -->
        <?php if (count($leaderboard) >= 3): ?>
        <div class="row justify-content-center mb-4">
            <!-- 2nd Place -->
            <div class="col-md-4 text-center">
                <div class="card h-100" style="margin-top: 40px;">
                    <div class="card-body">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="uploads/foto/<?= $leaderboard[1]['foto'] ?>" 
                                 onerror="this.src='assets/images/default.jpg'"
                                 class="rounded-circle" 
                                 width="100" height="100" 
                                 style="object-fit: cover; border: 5px solid #C0C0C0;">
                            <div class="position-absolute top-0 start-100 translate-middle">
                                <span class="badge rounded-pill" style="background: #C0C0C0; font-size: 1.2rem; padding: 10px 15px;">2</span>
                            </div>
                        </div>
                        <h5><?= htmlspecialchars($leaderboard[1]['nama']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($leaderboard[1]['sekolah']) ?></p>
                        <h3 class="text-primary"><?= $leaderboard[1]['poin'] ?? 0 ?> <small>poin</small></h3>
                        <?php
                        $badges = json_decode($leaderboard[1]['badges'] ?? '[]', true);
                        if (!empty($badges)):
                        ?>
                            <div class="mt-2">
                                <?php foreach (array_slice($badges, 0, 3) as $badge): ?>
                                    <span class="badge bg-secondary me-1"><i class="fas fa-medal"></i></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 1st Place -->
            <div class="col-md-4 text-center">
                <div class="card h-100 border-warning" style="box-shadow: 0 8px 24px rgba(255, 215, 0, 0.3);">
                    <div class="card-body">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="uploads/foto/<?= $leaderboard[0]['foto'] ?>" 
                                 onerror="this.src='assets/images/default.jpg'"
                                 class="rounded-circle" 
                                 width="120" height="120" 
                                 style="object-fit: cover; border: 5px solid #FFD700;">
                            <div class="position-absolute top-0 start-100 translate-middle">
                                <i class="fas fa-crown fa-2x text-warning"></i>
                            </div>
                        </div>
                        <h4><?= htmlspecialchars($leaderboard[0]['nama']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($leaderboard[0]['sekolah']) ?></p>
                        <h2 class="text-warning"><?= $leaderboard[0]['poin'] ?? 0 ?> <small>poin</small></h2>
                        <?php
                        $badges = json_decode($leaderboard[0]['badges'] ?? '[]', true);
                        if (!empty($badges)):
                        ?>
                            <div class="mt-2">
                                <?php foreach (array_slice($badges, 0, 3) as $badge): ?>
                                    <span class="badge bg-warning text-dark me-1"><i class="fas fa-medal"></i></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 3rd Place -->
            <div class="col-md-4 text-center">
                <div class="card h-100" style="margin-top: 40px;">
                    <div class="card-body">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="uploads/foto/<?= $leaderboard[2]['foto'] ?>" 
                                 onerror="this.src='assets/images/default.jpg'"
                                 class="rounded-circle" 
                                 width="100" height="100" 
                                 style="object-fit: cover; border: 5px solid #CD7F32;">
                            <div class="position-absolute top-0 start-100 translate-middle">
                                <span class="badge rounded-pill" style="background: #CD7F32; font-size: 1.2rem; padding: 10px 15px;">3</span>
                            </div>
                        </div>
                        <h5><?= htmlspecialchars($leaderboard[2]['nama']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($leaderboard[2]['sekolah']) ?></p>
                        <h3 class="text-primary"><?= $leaderboard[2]['poin'] ?? 0 ?> <small>poin</small></h3>
                        <?php
                        $badges = json_decode($leaderboard[2]['badges'] ?? '[]', true);
                        if (!empty($badges)):
                        ?>
                            <div class="mt-2">
                                <?php foreach (array_slice($badges, 0, 3) as $badge): ?>
                                    <span class="badge bg-secondary me-1"><i class="fas fa-medal"></i></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rest of Leaderboard -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Semua Ranking
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($leaderboard as $index => $entry): ?>
                            <?php
                            $rank = $index + 1;
                            $isCurrentUser = ($entry['id'] == $user['id']);
                            $rankClass = '';
                            if ($rank === 1) $rankClass = 'rank-1';
                            elseif ($rank === 2) $rankClass = 'rank-2';
                            elseif ($rank === 3) $rankClass = 'rank-3';
                            ?>
                            <div class="leaderboard-item <?= $rankClass ?> <?= $isCurrentUser ? 'bg-light' : '' ?>">
                                <div class="leaderboard-rank"><?= $rank ?></div>
                                <img src="uploads/foto/<?= $entry['foto'] ?>" 
                                     onerror="this.src='assets/images/default.jpg'"
                                     class="rounded-circle me-3" 
                                     width="50" height="50" 
                                     style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">
                                        <?= htmlspecialchars($entry['nama']) ?>
                                        <?= $isCurrentUser ? '<span class="badge bg-primary ms-2">You</span>' : '' ?>
                                    </h6>
                                    <small class="text-muted"><?= htmlspecialchars($entry['sekolah']) ?></small>
                                </div>
                                <div class="text-end">
                                    <h5 class="mb-0 text-primary"><?= $entry['poin'] ?? 0 ?></h5>
                                    <small class="text-muted">poin</small>
                                </div>
                                <?php
                                $badges = json_decode($entry['badges'] ?? '[]', true);
                                if (!empty($badges)):
                                ?>
                                    <div class="ms-3">
                                        <?php foreach (array_slice($badges, 0, 2) as $badge): ?>
                                            <i class="fas fa-medal text-warning" title="<?= htmlspecialchars($badge) ?>"></i>
                                        <?php endforeach; ?>
                                        <?php if (count($badges) > 2): ?>
                                            <small class="text-muted">+<?= count($badges) - 2 ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Badge Legend -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> Cara Mendapatkan Poin
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Kumpulkan tugas: <strong>+10 poin</strong></li>
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Nilai ≥80: <strong>+20 poin</strong></li>
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Nilai 60-79: <strong>+10 poin</strong></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Post di forum: <strong>+5 poin</strong></li>
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Reply forum: <strong>+3 poin</strong></li>
                                    <li class="mb-2"><i class="fas fa-trophy text-warning"></i> Badge unlock di: <strong>100, 500, 1000 poin</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>