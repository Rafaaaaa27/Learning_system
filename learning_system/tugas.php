<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user = getCurrentUser();
$peran = $user['peran'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Create assignment (Guru)
    if ($action === 'create_tugas' && $peran === 'guru') {
        $idKelas = $_POST['id_kelas'];
        $judul = clean($_POST['judul']);
        $deskripsi = clean($_POST['deskripsi']);
        $deadline = $_POST['deadline'];
        $tipe = $_POST['tipe'];
        
        $fileLampiran = null;
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['file'], 'tugas');
            if ($upload['success']) {
                $fileLampiran = $upload['filename'];
            }
        }
        
        $opsiJawaban = null;
        if ($tipe === 'multiple_choice') {
            $opsiJawaban = json_encode($_POST['opsi'] ?? []);
        }
        
        $stmt = $pdo->prepare("INSERT INTO tugas (id_kelas, judul, deskripsi, deadline, file_lampiran, tipe, opsi_jawaban) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$idKelas, $judul, $deskripsi, $deadline, $fileLampiran, $tipe, $opsiJawaban])) {
            // Notify all students
            $stmt = $pdo->prepare("SELECT id_siswa FROM siswa_kelas WHERE id_kelas = ?");
            $stmt->execute([$idKelas]);
            $siswa = $stmt->fetchAll();
            
            foreach ($siswa as $s) {
                createNotification($s['id_siswa'], "Tugas baru: $judul (Deadline: " . formatDate($deadline) . ")", 'tugas', "tugas.php?id=" . $pdo->lastInsertId());
            }
            
            $success = "Tugas berhasil dibuat!";
        }
    }
    
    // Submit assignment (Siswa)
    elseif ($action === 'submit_tugas' && $peran === 'siswa') {
        $idTugas = $_POST['id_tugas'];
        $jawaban = clean($_POST['jawaban']);
        
        $fileJawaban = null;
        if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['file_jawaban'], 'jawaban');
            if ($upload['success']) {
                $fileJawaban = $upload['filename'];
            }
        }
        
        // Check if already submitted
        $stmt = $pdo->prepare("SELECT id FROM jawaban_tugas WHERE id_tugas = ? AND id_siswa = ?");
        $stmt->execute([$idTugas, $user['id']]);
        
        if ($stmt->fetch()) {
            // Update existing submission
            $stmt = $pdo->prepare("UPDATE jawaban_tugas SET jawaban = ?, file_jawaban = ?, submitted_at = NOW() WHERE id_tugas = ? AND id_siswa = ?");
            $stmt->execute([$jawaban, $fileJawaban, $idTugas, $user['id']]);
        } else {
            // New submission
            $stmt = $pdo->prepare("INSERT INTO jawaban_tugas (id_tugas, id_siswa, jawaban, file_jawaban) VALUES (?, ?, ?, ?)");
            $stmt->execute([$idTugas, $user['id'], $jawaban, $fileJawaban]);
            
            // Award points
            addPoints($user['id'], 10);
        }
        
        $success = "Tugas berhasil dikumpulkan!";
    }
    
    // Grade assignment (Guru)
    elseif ($action === 'grade_tugas' && $peran === 'guru') {
        $idJawaban = $_POST['id_jawaban'];
        $nilai = $_POST['nilai'];
        $feedback = clean($_POST['feedback']);
        
        $stmt = $pdo->prepare("UPDATE jawaban_tugas SET nilai = ?, feedback = ? WHERE id = ?");
        
        if ($stmt->execute([$nilai, $feedback, $idJawaban])) {
            // Get student ID and notify
            $stmt = $pdo->prepare("SELECT jt.id_siswa, t.judul FROM jawaban_tugas jt JOIN tugas t ON jt.id_tugas = t.id WHERE jt.id = ?");
            $stmt->execute([$idJawaban]);
            $data = $stmt->fetch();
            
            if ($data) {
                createNotification($data['id_siswa'], "Tugas '{$data['judul']}' telah dinilai. Nilai: $nilai", 'nilai');
                
                // Award bonus points for good grades
                if ($nilai >= 80) {
                    addPoints($data['id_siswa'], 20);
                } elseif ($nilai >= 60) {
                    addPoints($data['id_siswa'], 10);
                }
            }
            
            $success = "Nilai berhasil disimpan!";
        }
    }
}

// Get assignments based on role
$tugasList = [];
if ($peran === 'guru') {
    $stmt = $pdo->prepare("
        SELECT t.*, k.nama_kelas,
        (SELECT COUNT(*) FROM jawaban_tugas jt WHERE jt.id_tugas = t.id) as total_submitted
        FROM tugas t
        JOIN kelas k ON t.id_kelas = k.id
        WHERE k.id_guru = ?
        ORDER BY t.deadline DESC
    ");
    $stmt->execute([$user['id']]);
    $tugasList = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT t.*, k.nama_kelas,
        jt.id as jawaban_id, jt.nilai, jt.submitted_at, jt.feedback
        FROM tugas t
        JOIN kelas k ON t.id_kelas = k.id
        JOIN siswa_kelas sk ON k.id = sk.id_kelas
        LEFT JOIN jawaban_tugas jt ON t.id = jt.id_tugas AND jt.id_siswa = ?
        WHERE sk.id_siswa = ?
        ORDER BY t.deadline DESC
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $tugasList = $stmt->fetchAll();
}

// Get specific assignment if ID provided
$selectedTugas = null;
if (isset($_GET['id'])) {
    $tugasId = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT t.*, k.nama_kelas FROM tugas t JOIN kelas k ON t.id_kelas = k.id WHERE t.id = ?");
    $stmt->execute([$tugasId]);
    $selectedTugas = $stmt->fetch();
    
    if ($selectedTugas && $peran === 'guru') {
        // Get all submissions
        $stmt = $pdo->prepare("
            SELECT jt.*, u.nama as nama_siswa
            FROM jawaban_tugas jt
            JOIN users u ON jt.id_siswa = u.id
            WHERE jt.id_tugas = ?
            ORDER BY jt.submitted_at DESC
        ");
        $stmt->execute([$tugasId]);
        $submissions = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas - Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tasks"></i> Manajemen Tugas</h2>
            <?php if ($peran === 'guru'): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTugasModal">
                    <i class="fas fa-plus"></i> Buat Tugas Baru
                </button>
            <?php endif; ?>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($selectedTugas): ?>
            <!-- Detail Tugas -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0"><?= htmlspecialchars($selectedTugas['judul']) ?></h4>
                            <small class="text-muted"><?= htmlspecialchars($selectedTugas['nama_kelas']) ?></small>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Deskripsi:</strong>
                                <p><?= nl2br(htmlspecialchars($selectedTugas['deskripsi'])) ?></p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Deadline:</strong>
                                    <p><i class="fas fa-calendar"></i> <?= formatDate($selectedTugas['deadline']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Tipe:</strong>
                                    <p><span class="badge bg-info"><?= ucfirst($selectedTugas['tipe']) ?></span></p>
                                </div>
                            </div>
                            <?php if ($selectedTugas['file_lampiran']): ?>
                                <div class="mt-3">
                                    <a href="uploads/tugas/<?= $selectedTugas['file_lampiran'] ?>" class="btn btn-outline-primary" target="_blank" download>
                                        <i class="fas fa-download"></i> Download Lampiran
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($peran === 'siswa'): ?>
                        <!-- Form Submit (Siswa) -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-paper-plane"></i> Kumpulkan Jawaban
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM jawaban_tugas WHERE id_tugas = ? AND id_siswa = ?");
                                $stmt->execute([$tugasId, $user['id']]);
                                $existingAnswer = $stmt->fetch();
                                ?>
                                
                                <?php if ($existingAnswer && $existingAnswer['nilai']): ?>
                                    <div class="alert alert-info">
                                        <strong>Nilai: <?= $existingAnswer['nilai'] ?></strong>
                                        <?php if ($existingAnswer['feedback']): ?>
                                            <p class="mb-0 mt-2"><strong>Feedback:</strong><br><?= nl2br(htmlspecialchars($existingAnswer['feedback'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="submit_tugas">
                                    <input type="hidden" name="id_tugas" value="<?= $tugasId ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jawaban</label>
                                        <textarea class="form-control" name="jawaban" rows="6" required><?= $existingAnswer['jawaban'] ?? '' ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Upload File (Opsional)</label>
                                        <input type="file" class="form-control" name="file_jawaban" onchange="previewFile(this)">
                                        <?php if ($existingAnswer && $existingAnswer['file_jawaban']): ?>
                                            <small class="text-muted">File sebelumnya: <?= $existingAnswer['file_jawaban'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> <?= $existingAnswer ? 'Update Jawaban' : 'Kumpulkan' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($peran === 'guru' && isset($submissions)): ?>
                        <!-- List Submissions (Guru) -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-list"></i> Jawaban Siswa (<?= count($submissions) ?>)
                            </div>
                            <div class="card-body">
                                <?php if (empty($submissions)): ?>
                                    <p class="text-muted">Belum ada siswa yang mengumpulkan.</p>
                                <?php else: ?>
                                    <?php foreach ($submissions as $submission): ?>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h6><?= htmlspecialchars($submission['nama_siswa']) ?></h6>
                                                <small class="text-muted">Dikumpulkan: <?= timeAgo($submission['submitted_at']) ?></small>
                                                
                                                <div class="mt-2">
                                                    <strong>Jawaban:</strong>
                                                    <p><?= nl2br(htmlspecialchars($submission['jawaban'])) ?></p>
                                                </div>
                                                
                                                <?php if ($submission['file_jawaban']): ?>
                                                    <a href="uploads/jawaban/<?= $submission['file_jawaban'] ?>" class="btn btn-sm btn-outline-primary mb-2" target="_blank" download>
                                                        <i class="fas fa-download"></i> Download File
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-sm btn-success" onclick="gradeModal(<?= $submission['id'] ?>, '<?= htmlspecialchars($submission['nama_siswa']) ?>', <?= $submission['nilai'] ?? 0 ?>, '<?= htmlspecialchars($submission['feedback'] ?? '') ?>')">
                                                    <i class="fas fa-edit"></i> <?= $submission['nilai'] ? 'Edit Nilai' : 'Beri Nilai' ?>
                                                </button>
                                                
                                                <?php if ($submission['nilai']): ?>
                                                    <span class="badge bg-primary ms-2">Nilai: <?= $submission['nilai'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-clock"></i> Info Tugas
                        </div>
                        <div class="card-body">
                            <?php
                            $deadline = strtotime($selectedTugas['deadline']);
                            $now = time();
                            $diff = $deadline - $now;
                            $isOverdue = $diff < 0;
                            ?>
                            
                            <div class="text-center mb-3">
                                <?php if ($isOverdue): ?>
                                    <span class="badge bg-danger">Sudah Lewat Deadline</span>
                                <?php else: ?>
                                    <h4 class="text-primary"><?= ceil($diff / 86400) ?> Hari Lagi</h4>
                                    <small class="text-muted">Sampai deadline</small>
                                <?php endif; ?>
                            </div>
                            
                            <hr>
                            
                            <?php if ($peran === 'guru'): ?>
                                <p><strong>Total Siswa:</strong> <?= $selectedTugas['total_submitted'] ?? 0 ?></p>
                                <p><strong>Sudah Mengumpulkan:</strong> <?= count($submissions ?? []) ?></p>
                            <?php endif; ?>
                            
                            <a href="tugas.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- List Tugas -->
            <div class="row">
                <?php if (empty($tugasList)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada tugas.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($tugasList as $tugas): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0"><?= htmlspecialchars($tugas['judul']) ?></h5>
                                        <?php if ($peran === 'siswa'): ?>
                                            <?php if ($tugas['nilai']): ?>
                                                <span class="badge bg-success">Nilai: <?= $tugas['nilai'] ?></span>
                                            <?php elseif ($tugas['jawaban_id']): ?>
                                                <span class="badge bg-info">Sudah Dikumpulkan</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Belum Dikumpulkan</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small"><?= htmlspecialchars($tugas['nama_kelas']) ?></p>
                                    <p class="card-text"><?= substr(htmlspecialchars($tugas['deskripsi']), 0, 100) ?>...</p>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-calendar"></i> Deadline: <?= formatDate($tugas['deadline']) ?>
                                    </p>
                                    <?php if ($peran === 'guru'): ?>
                                        <p class="text-muted small">
                                            <i class="fas fa-users"></i> <?= $tugas['total_submitted'] ?> dikumpulkan
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <a href="tugas.php?id=<?= $tugas['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-arrow-right"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Create Assignment Modal (Guru) -->
    <?php if ($peran === 'guru'): ?>
    <div class="modal fade" id="createTugasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Buat Tugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_tugas">
                        
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <select class="form-select" name="id_kelas" required>
                                <option value="">Pilih Kelas</option>
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id_guru = ?");
                                $stmt->execute([$user['id']]);
                                $kelasGuru = $stmt->fetchAll();
                                foreach ($kelasGuru as $k):
                                ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" class="form-control" name="judul" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deadline</label>
                                <input type="datetime-local" class="form-control" name="deadline" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe</label>
                                <select class="form-select" name="tipe" required>
                                    <option value="essay">Essay</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">File Lampiran (Opsional)</label>
                            <input type="file" class="form-control" name="file" onchange="previewFile(this)">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Tugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Grade Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Beri Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="gradeForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="grade_tugas">
                        <input type="hidden" name="id_jawaban" id="gradeIdJawaban">
                        
                        <p><strong>Siswa:</strong> <span id="gradeSiswaName"></span></p>
                        
                        <div class="mb-3">
                            <label class="form-label">Nilai (0-100)</label>
                            <input type="number" class="form-control" name="nilai" id="gradeNilai" min="0" max="100" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Feedback</label>
                            <textarea class="form-control" name="feedback" id="gradeFeedback" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Nilai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <script>
        function gradeModal(id, nama, nilai, feedback) {
            $('#gradeIdJawaban').val(id);
            $('#gradeSiswaName').text(nama);
            $('#gradeNilai').val(nilai);
            $('#gradeFeedback').val(feedback);
            new bootstrap.Modal(document.getElementById('gradeModal')).show();
        }
    </script>
</body>
</html>