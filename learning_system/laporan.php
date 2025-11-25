<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser();
$peran = $user['peran'];

// Get target student (for guru/admin viewing student reports)
$targetSiswaId = $_GET['siswa'] ?? $user['id'];
$targetKelasId = $_GET['kelas'] ?? null;

// Verify access rights
if ($peran === 'siswa' && $targetSiswaId != $user['id']) {
    die('Akses ditolak');
}

// Get student data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND peran = 'siswa'");
$stmt->execute([$targetSiswaId]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die('Siswa tidak ditemukan');
}

// Get student's classes
$stmt = $pdo->prepare("
    SELECT k.* FROM kelas k
    JOIN siswa_kelas sk ON k.id = sk.id_kelas
    WHERE sk.id_siswa = ?
");
$stmt->execute([$targetSiswaId]);
$kelasSiswa = $stmt->fetchAll();

// Get overall statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT jt.id_tugas) as total_tugas_selesai,
        AVG(jt.nilai) as rata_rata_nilai,
        COUNT(DISTINCT CASE WHEN jt.nilai >= 80 THEN jt.id_tugas END) as tugas_a,
        COUNT(DISTINCT CASE WHEN jt.nilai >= 60 AND jt.nilai < 80 THEN jt.id_tugas END) as tugas_b
    FROM jawaban_tugas jt
    WHERE jt.id_siswa = ? AND jt.nilai IS NOT NULL
");
$stmt->execute([$targetSiswaId]);
$overallStats = $stmt->fetch();

// Get gamification data
$stmt = $pdo->prepare("SELECT * FROM gamifikasi WHERE id_user = ?");
$stmt->execute([$targetSiswaId]);
$gamifikasi = $stmt->fetch();

// Get grades by class
$gradesByClass = [];
if ($targetKelasId) {
    $stmt = $pdo->prepare("
        SELECT t.judul, jt.nilai, jt.submitted_at, t.deadline
        FROM jawaban_tugas jt
        JOIN tugas t ON jt.id_tugas = t.id
        WHERE jt.id_siswa = ? AND t.id_kelas = ? AND jt.nilai IS NOT NULL
        ORDER BY jt.submitted_at DESC
    ");
    $stmt->execute([$targetSiswaId, $targetKelasId]);
    $gradesByClass = $stmt->fetchAll();
}

// Get monthly activity for chart
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(submitted_at, '%Y-%m') as bulan,
        COUNT(*) as jumlah
    FROM jawaban_tugas
    WHERE id_siswa = ?
    GROUP BY bulan
    ORDER BY bulan DESC
    LIMIT 6
");
$stmt->execute([$targetSiswaId]);
$monthlyActivity = array_reverse($stmt->fetchAll());
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-bar"></i> Laporan Perkembangan</h2>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>

        <!-- Student Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="uploads/foto/<?= $siswa['foto'] ?>" 
                             onerror="this.src='assets/images/default.jpg'"
                             class="rounded-circle" 
                             width="100" height="100" 
                             style="object-fit: cover;">
                    </div>
                    <div class="col-md-10">
                        <h4><?= htmlspecialchars($siswa['nama']) ?></h4>
                        <p class="mb-1"><i class="fas fa-envelope"></i> <?= htmlspecialchars($siswa['email']) ?></p>
                        <p class="mb-1"><i class="fas fa-school"></i> <?= htmlspecialchars($siswa['sekolah']) ?></p>
                        <p class="mb-0"><i class="fas fa-calendar"></i> Bergabung: <?= formatDate($siswa['created_at']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3><?= count($kelasSiswa) ?></h3>
                    <p>Kelas Diikuti</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3><?= $overallStats['total_tugas_selesai'] ?? 0 ?></h3>
                    <p>Tugas Selesai</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-star"></i>
                    <h3><?= round($overallStats['rata_rata_nilai'] ?? 0) ?></h3>
                    <p>Rata-rata Nilai</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-trophy"></i>
                    <h3><?= $gamifikasi['poin'] ?? 0 ?></h3>
                    <p>Total Poin</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- Activity Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Aktivitas 6 Bulan Terakhir
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i> Distribusi Nilai
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6 mb-3">
                                <h3 class="text-success"><?= $overallStats['tugas_a'] ?? 0 ?></h3>
                                <p>Nilai A (≥80)</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h3 class="text-primary"><?= $overallStats['tugas_b'] ?? 0 ?></h3>
                                <p>Nilai B (60-79)</p>
                            </div>
                        </div>
                        <canvas id="gradeChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Grades by Class -->
                <?php if (!empty($kelasSiswa)): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Nilai per Kelas
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select class="form-select" onchange="location.href='laporan.php?siswa=<?= $targetSiswaId ?>&kelas=' + this.value">
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelasSiswa as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= $targetKelasId == $k['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($targetKelasId && !empty($gradesByClass)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tugas</th>
                                            <th>Nilai</th>
                                            <th>Dikumpulkan</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($gradesByClass as $grade): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($grade['judul']) ?></td>
                                                <td>
                                                    <strong class="<?= $grade['nilai'] >= 80 ? 'text-success' : ($grade['nilai'] >= 60 ? 'text-primary' : 'text-danger') ?>">
                                                        <?= $grade['nilai'] ?>
                                                    </strong>
                                                </td>
                                                <td><?= formatDate($grade['submitted_at']) ?></td>
                                                <td>
                                                    <?php
                                                    $onTime = strtotime($grade['submitted_at']) <= strtotime($grade['deadline']);
                                                    ?>
                                                    <span class="badge bg-<?= $onTime ? 'success' : 'warning' ?>">
                                                        <?= $onTime ? 'Tepat Waktu' : 'Terlambat' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif ($targetKelasId): ?>
                            <p class="text-muted">Belum ada nilai untuk kelas ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Badges & Achievements -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-medal"></i> Pencapaian
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="progress mb-2">
                                <div class="progress-bar" style="width: <?= min(($gamifikasi['poin'] ?? 0) / 10, 100) ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $gamifikasi['poin'] ?? 0 ?> / 1000 poin</small>
                        </div>

                        <h6>Badges:</h6>
                        <?php
                        $badges = $gamifikasi ? json_decode($gamifikasi['badges'], true) : [];
                        if (empty($badges)):
                        ?>
                            <p class="text-muted">Belum ada badge</p>
                        <?php else: ?>
                            <?php foreach ($badges as $badge): ?>
                                <div class="badge-custom me-2 mb-2 d-inline-block">
                                    <i class="fas fa-medal"></i> <?= htmlspecialchars($badge) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Classes -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard"></i> Kelas yang Diikuti
                    </div>
                    <div class="card-body">
                        <?php if (empty($kelasSiswa)): ?>
                            <p class="text-muted">Belum mengikuti kelas</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($kelasSiswa as $k): ?>
                                    <a href="kelas.php?id=<?= $k['id'] ?>" class="list-group-item list-group-item-action">
                                        <i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($k['nama_kelas']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Link (for parents) -->
        <?php if ($peran === 'guru' || $peran === 'admin'): ?>
        <div class="card mt-4">
            <div class="card-body">
                <h6><i class="fas fa-share-alt"></i> Bagikan Laporan ke Orang Tua</h6>
                <p class="text-muted small">Link ini dapat dibagikan ke orang tua untuk melihat laporan perkembangan siswa.</p>
                <div class="input-group">
                    <input type="text" class="form-control" id="shareLink" value="<?= SITE_URL ?>/laporan.php?siswa=<?= $targetSiswaId ?>" readonly>
                    <button class="btn btn-primary" onclick="copyShareLink()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthlyActivity, 'bulan')) ?>,
                datasets: [{
                    label: 'Tugas Dikumpulkan',
                    data: <?= json_encode(array_column($monthlyActivity, 'jumlah')) ?>,
                    borderColor: '#ADD8E6',
                    backgroundColor: 'rgba(173, 216, 230, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Grade Distribution Chart
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Nilai A (≥80)', 'Nilai B (60-79)'],
                datasets: [{
                    data: [<?= $overallStats['tugas_a'] ?? 0 ?>, <?= $overallStats['tugas_b'] ?? 0 ?>],
                    backgroundColor: ['#28a745', '#ADD8E6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        function copyShareLink() {
            const link = document.getElementById('shareLink');
            link.select();
            document.execCommand('copy');
            alert('Link berhasil dicopy!');
        }
    </script>
</body>
</html>