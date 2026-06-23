<?php
/**
 * Leaderboard Page
 * Menampilkan ranking siswa berdasarkan poin
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'models/GamifikasiModel.php';
require_once 'models/KelasModel.php';

// Require login
requireLogin();

$user = getCurrentUser();
$gamifikasiModel = new GamifikasiModel();
$kelasModel = new KelasModel();

// Get filter
$kelasId = $_GET['kelas_id'] ?? null;

// Get kelas list for filter
if ($user['peran'] === 'siswa') {
    $kelasList = $kelasModel->getKelasBySiswa($user['id']);
} elseif ($user['peran'] === 'guru') {
    $kelasList = $kelasModel->getKelasByGuru($user['id']);
} else {
    $kelasList = $kelasModel->getAllKelasWithGuru();
}

// Get leaderboard
$leaderboard = $gamifikasiModel->getLeaderboard($kelasId, 50);

// Get user rank
if ($user['peran'] === 'siswa') {
    $myRank = $gamifikasiModel->getUserRank($user['id'], $kelasId);
    $myStats = $gamifikasiModel->getStatistik($user['id']);
}

$pageTitle = 'Leaderboard - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    
<?php 
$currentUser = $user;
require_once __DIR__ . '/views/layouts/header.php'; 
?>

<div class="container mt-4">
    <div class="row">
        <!-- My Stats (untuk siswa) -->
        <?php if ($user['peran'] === 'siswa'): ?>
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Statistik Saya</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="display-4 text-warning">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h2 class="text-primary">#<?php echo $myRank; ?></h2>
                        <small class="text-muted">Ranking Saya</small>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h4 class="text-success mb-0">
                            <i class="fas fa-star me-1"></i>
                            <?php echo $myStats['total_poin']; ?>
                        </h4>
                        <small class="text-muted">Total Poin</small>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="mb-0"><?php echo $myStats['total_badges']; ?></h5>
                        <small class="text-muted">Total Badges</small>
                    </div>
                    
                    <?php if (!empty($myStats['current_badges'])): ?>
                    <div>
                        <small class="text-muted d-block mb-2">Badges:</small>
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            <?php foreach (array_slice($myStats['current_badges'], 0, 4) as $badge): ?>
                            <span class="badge bg-success" title="<?php echo htmlspecialchars($badge); ?>">
                                <i class="fas fa-award"></i>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Leaderboard -->
        <div class="<?php echo $user['peran'] === 'siswa' ? 'col-lg-9' : 'col-lg-12'; ?>">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">
                                <i class="fas fa-trophy me-2 text-warning"></i>
                                Leaderboard
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <form action="leaderboard.php" method="GET" class="row g-2">
                                <div class="col-auto">
                                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">Semua Kelas</option>
                                        <?php foreach ($kelasList as $k): ?>
                                        <option value="<?php echo $k['id']; ?>" <?php echo $kelasId == $k['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($k['nama_kelas']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($leaderboard)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada data leaderboard</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="leaderboard-table">
                            <thead>
                                <tr>
                                    <th width="80">Rank</th>
                                    <th>Siswa</th>
                                    <th>Sekolah</th>
                                    <th class="text-center">Poin</th>
                                    <th>Badges</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaderboard as $index => $item): ?>
                                <tr class="<?php echo $user['peran'] === 'siswa' && $item['id_user'] == $user['id'] ? 'table-primary' : ''; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($index === 0): ?>
                                            <i class="fas fa-crown fa-2x text-warning"></i>
                                            <?php elseif ($index === 1): ?>
                                            <i class="fas fa-medal fa-2x" style="color: silver;"></i>
                                            <?php elseif ($index === 2): ?>
                                            <i class="fas fa-medal fa-2x" style="color: #CD7F32;"></i>
                                            <?php else: ?>
                                            <span class="fs-5 fw-bold">#<?php echo $index + 1; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo UPLOAD_URL . 'profiles/' . $item['foto']; ?>" 
                                                 class="avatar me-2" 
                                                 alt="<?php echo htmlspecialchars($item['nama']); ?>"
                                                 onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.jpg'">
                                            <div>
                                                <strong><?php echo htmlspecialchars($item['nama']); ?></strong>
                                                <?php if ($user['peran'] === 'siswa' && $item['id_user'] == $user['id']): ?>
                                                <span class="badge bg-info ms-2">Anda</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['sekolah'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-star me-1"></i>
                                            <?php echo $item['poin']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $badges = json_decode($item['badges'], true) ?? [];
                                        if (!empty($badges)):
                                        ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (array_slice($badges, 0, 3) as $badge): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-award me-1"></i>
                                                <?php echo htmlspecialchars($badge); ?>
                                            </span>
                                            <?php endforeach; ?>
                                            <?php if (count($badges) > 3): ?>
                                            <span class="badge bg-secondary">+<?php echo count($badges) - 3; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Info Badges -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle me-2"></i>Cara Mendapatkan Poin & Badge</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Mendapatkan Poin:</h6>
                            <ul>
                                <li>Mengerjakan tugas dengan nilai 90+: <strong>50 poin</strong></li>
                                <li>Mengerjakan tugas dengan nilai 80-89: <strong>40 poin</strong></li>
                                <li>Mengerjakan tugas dengan nilai 70-79: <strong>30 poin</strong></li>
                                <li>Membuat post forum: <strong>5 poin</strong></li>
                                <li>Memberikan reply di forum: <strong>2 poin</strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Badge Levels:</h6>
                            <ul>
                                <li><span class="badge bg-secondary">Pemula</span> - 0 poin</li>
                                <li><span class="badge bg-primary">Rajin Belajar</span> - 100 poin</li>
                                <li><span class="badge bg-warning">Juara Kuis</span> - 250 poin</li>
                                <li><span class="badge bg-info">Ahli Diskusi</span> - 500 poin</li>
                                <li><span class="badge bg-success">Master</span> - 1000 poin</li>
                                <li><span class="badge bg-danger">Legend</span> - 2000 poin</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>

</body>
</html>