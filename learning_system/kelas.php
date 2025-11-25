<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser();
$peran = $user['peran'];

// Handle class creation (Guru only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $peran === 'guru') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_class') {
        $namaKelas = clean($_POST['nama_kelas']);
        $deskripsi = clean($_POST['deskripsi']);
        
        $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas, id_guru, deskripsi) VALUES (?, ?, ?)");
        if ($stmt->execute([$namaKelas, $user['id'], $deskripsi])) {
            $success = "Kelas berhasil dibuat!";
        }
    }
    
    elseif ($action === 'upload_materi') {
        $idKelas = $_POST['id_kelas'];
        $judul = clean($_POST['judul']);
        $deskripsi = clean($_POST['deskripsi']);
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['file'], 'materi');
            
            if ($upload['success']) {
                $stmt = $pdo->prepare("INSERT INTO materi (id_kelas, judul, file_path, deskripsi) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$idKelas, $judul, $upload['filename'], $deskripsi])) {
                    // Notify all students in this class
                    $stmt = $pdo->prepare("SELECT id_siswa FROM siswa_kelas WHERE id_kelas = ?");
                    $stmt->execute([$idKelas]);
                    $siswa = $stmt->fetchAll();
                    
                    foreach ($siswa as $s) {
                        createNotification($s['id_siswa'], "Materi baru: $judul", 'pengumuman', "kelas.php?id=$idKelas");
                    }
                    
                    $success = "Materi berhasil diupload!";
                }
            }
        }
    }
}

// Get specific class if ID provided
$kelasId = $_GET['id'] ?? null;
$selectedClass = null;

if ($kelasId) {
    if ($peran === 'guru') {
        $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ? AND id_guru = ?");
        $stmt->execute([$kelasId, $user['id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT k.* FROM kelas k
            JOIN siswa_kelas sk ON k.id = sk.id_kelas
            WHERE k.id = ? AND sk.id_siswa = ?
        ");
        $stmt->execute([$kelasId, $user['id']]);
    }
    $selectedClass = $stmt->fetch();
    
    if ($selectedClass) {
        // Get class materials
        $stmt = $pdo->prepare("SELECT * FROM materi WHERE id_kelas = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$kelasId]);
        $materials = $stmt->fetchAll();
        
        // Get class students
        $stmt = $pdo->prepare("
            SELECT u.* FROM users u
            JOIN siswa_kelas sk ON u.id = sk.id_siswa
            WHERE sk.id_kelas = ?
            ORDER BY u.nama
        ");
        $stmt->execute([$kelasId]);
        $students = $stmt->fetchAll();
    }
}

// Get all user's classes
$classes = getUserClasses($user['id'], $peran);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard"></i> Kelas Saya
                        <?php if ($peran === 'guru'): ?>
                            <button class="btn btn-sm btn-primary float-end" data-bs-toggle="modal" data-bs-target="#createClassModal">
                                <i class="fas fa-plus"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (empty($classes)): ?>
                            <div class="list-group-item text-muted">Belum ada kelas</div>
                        <?php else: ?>
                            <?php foreach ($classes as $kelas): ?>
                                <a href="kelas.php?id=<?= $kelas['id'] ?>" 
                                   class="list-group-item list-group-item-action <?= $kelasId == $kelas['id'] ? 'active' : '' ?>">
                                    <i class="fas fa-chalkboard-teacher"></i> 
                                    <?= htmlspecialchars($kelas['nama_kelas']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <?php if ($selectedClass): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0"><?= htmlspecialchars($selectedClass['nama_kelas']) ?></h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted"><?= htmlspecialchars($selectedClass['deskripsi'] ?? 'Tidak ada deskripsi') ?></p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <i class="fas fa-users"></i>
                                        <h3><?= count($students) ?></h3>
                                        <p>Siswa</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <i class="fas fa-book"></i>
                                        <h3><?= count($materials) ?></h3>
                                        <p>Materi</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <i class="fas fa-tasks"></i>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas WHERE id_kelas = ?");
                                        $stmt->execute([$kelasId]);
                                        $totalTugas = $stmt->fetch()['total'];
                                        ?>
                                        <h3><?= $totalTugas ?></h3>
                                        <p>Tugas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#materi">
                                <i class="fas fa-book"></i> Materi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#siswa">
                                <i class="fas fa-users"></i> Siswa
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Materi Tab -->
                        <div class="tab-pane fade show active" id="materi">
                            <?php if ($peran === 'guru'): ?>
                                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#uploadMateriModal">
                                    <i class="fas fa-upload"></i> Upload Materi
                                </button>
                            <?php endif; ?>

                            <?php if (empty($materials)): ?>
                                <div class="alert alert-info">Belum ada materi.</div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($materials as $materi): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i class="fas fa-file-alt text-primary"></i>
                                                        <?= htmlspecialchars($materi['judul']) ?>
                                                    </h5>
                                                    <p class="card-text text-muted small"><?= htmlspecialchars($materi['deskripsi']) ?></p>
                                                    <p class="text-muted small mb-0">
                                                        <i class="fas fa-clock"></i> <?= formatDate($materi['uploaded_at']) ?>
                                                    </p>
                                                </div>
                                                <div class="card-footer">
                                                    <a href="uploads/materi/<?= $materi['file_path'] ?>" 
                                                       class="btn btn-sm btn-primary" target="_blank" download>
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                    <?php if ($peran === 'guru'): ?>
                                                        <button class="btn btn-sm btn-danger" onclick="deleteMateri(<?= $materi['id'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Siswa Tab -->
                        <div class="tab-pane fade" id="siswa">
                            <?php if (empty($students)): ?>
                                <div class="alert alert-info">Belum ada siswa.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Sekolah</th>
                                                <?php if ($peran === 'guru'): ?>
                                                <th>Aksi</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $siswa): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($siswa['nama']) ?></td>
                                                    <td><?= htmlspecialchars($siswa['email']) ?></td>
                                                    <td><?= htmlspecialchars($siswa['sekolah']) ?></td>
                                                    <?php if ($peran === 'guru'): ?>
                                                    <td>
                                                        <a href="laporan.php?siswa=<?= $siswa['id'] ?>&kelas=<?= $kelasId ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="fas fa-chart-bar"></i> Laporan
                                                        </a>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chalkboard fa-4x text-muted mb-3"></i>
                            <h4>Pilih Kelas</h4>
                            <p class="text-muted">Pilih kelas dari sidebar untuk melihat detail</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Class Modal (Guru only) -->
    <?php if ($peran === 'guru'): ?>
    <div class="modal fade" id="createClassModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Buat Kelas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_class">
                        <div class="mb-3">
                            <label class="form-label">Nama Kelas</label>
                            <input type="text" class="form-control" name="nama_kelas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Materi Modal -->
    <div class="modal fade" id="uploadMateriModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload"></i> Upload Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload_materi">
                        <input type="hidden" name="id_kelas" value="<?= $kelasId ?>">
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" class="form-control" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" class="form-control" name="file" required onchange="previewFile(this)">
                            <small class="text-muted">Max 5MB. Format: PDF, JPG, DOC, PPT</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>