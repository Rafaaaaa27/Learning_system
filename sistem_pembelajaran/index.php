<?php
/**
 * Landing Page
 * Halaman utama untuk pengunjung yang belum login
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
}

$pageTitle = 'Selamat Datang - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                EduLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary ms-2" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="register.php">Daftar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section id="beranda" class="hero-section" style="margin-top: 76px;">
        <div class="container">
            <h1 class="animate__animated animate__fadeInDown">Platform Belajar Interaktif</h1>
            <p class="lead animate__animated animate__fadeInUp">
                Tingkatkan pengalaman belajar dengan sistem pembelajaran yang modern, interaktif, dan menyenangkan
            </p>
            <div class="mt-4">
                <a href="register.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-user-plus me-2"></i>Gabung Sekarang
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="tentang" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2>Tentang EduLearn</h2>
                    <p class="lead">
                        EduLearn adalah platform pembelajaran digital yang dirancang untuk memudahkan proses belajar mengajar antara guru dan siswa.
                    </p>
                    <p>
                        Dengan fitur-fitur canggih seperti manajemen kelas, tugas interaktif, forum diskusi, dan sistem gamifikasi, kami membantu meningkatkan motivasi dan hasil belajar siswa.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Akses materi pembelajaran kapan saja, di mana saja
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Sistem penilaian otomatis dan real-time
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Forum diskusi untuk kolaborasi
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Gamifikasi untuk meningkatkan motivasi
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="assets/images/learning-illustration.svg" alt="Learning" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="fitur" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Fitur Unggulan</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 feature-box">
                        <div class="card-body text-center">
                            <i class="fas fa-chalkboard-teacher fa-3x text-primary mb-3"></i>
                            <h4>Manajemen Kelas</h4>
                            <p>Buat dan kelola kelas dengan mudah. Upload materi, atur jadwal, dan pantau perkembangan siswa.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 feature-box">
                        <div class="card-body text-center">
                            <i class="fas fa-tasks fa-3x text-primary mb-3"></i>
                            <h4>Tugas & Kuis</h4>
                            <p>Buat tugas dan kuis interaktif dengan berbagai jenis soal. Penilaian otomatis dan manual.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 feature-box">
                        <div class="card-body text-center">
                            <i class="fas fa-comments fa-3x text-primary mb-3"></i>
                            <h4>Forum Diskusi</h4>
                            <p>Ruang diskusi untuk siswa dan guru. Tanya jawab, berbagi ide, dan kolaborasi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 feature-box">
                        <div class="card-body text-center">
                            <i class="fas fa-trophy fa-3x text-primary mb-3"></i>
                            <h4>Gamifikasi</h4>
                            <p>Sistem poin, badge, dan leaderboard untuk meningkatkan motivasi belajar siswa.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 feature-box">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                            <h4>Laporan & Analitik</h4>
                            <p>Pantau perkembangan siswa dengan laporan detail dan visualisasi data yang mudah dipahami.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 feature-box">
                        <div class="card-body text-center">
                            <i class="fas fa-bell fa-3x text-primary mb-3"></i>
                            <h4>Notifikasi Real-time</h4>
                            <p>Dapatkan notifikasi untuk tugas baru, deadline, dan pengumuman penting.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Apa Kata Mereka</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/images/avatar1.jpg" alt="User" class="avatar-lg me-3">
                                <div>
                                    <h5 class="mb-0">Budi Santoso</h5>
                                    <small class="text-muted">Guru Matematika</small>
                                </div>
                            </div>
                            <p class="fst-italic">
                                "EduLearn sangat membantu saya dalam mengelola kelas dan materi pembelajaran. Siswa jadi lebih aktif dan termotivasi!"
                            </p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/images/avatar2.jpg" alt="User" class="avatar-lg me-3">
                                <div>
                                    <h5 class="mb-0">Siti Nurhaliza</h5>
                                    <small class="text-muted">Siswa Kelas XII</small>
                                </div>
                            </div>
                            <p class="fst-italic">
                                "Belajar jadi lebih menyenangkan dengan sistem poin dan badge. Saya jadi lebih semangat mengerjakan tugas!"
                            </p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/images/avatar3.jpg" alt="User" class="avatar-lg me-3">
                                <div>
                                    <h5 class="mb-0">Andi Wijaya</h5>
                                    <small class="text-muted">Kepala Sekolah</small>
                                </div>
                            </div>
                            <p class="fst-italic">
                                "Platform yang sangat baik untuk digitalisasi pembelajaran. Laporan analitik membantu kami memantau progress siswa."
                            </p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section id="kontak" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Hubungi Kami</h2>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="api/contact.php" method="POST">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subjek" class="form-label">Subjek</label>
                                    <input type="text" class="form-control" id="subjek" name="subjek" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pesan" class="form-label">Pesan</label>
                                    <textarea class="form-control" id="pesan" name="pesan" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>EduLearn</h5>
                    <p>Platform Pembelajaran Interaktif untuk Masa Depan Pendidikan yang Lebih Baik</p>
                </div>
                <div class="col-md-3">
                    <h6>Link Cepat</h6>
                    <ul class="list-unstyled">
                        <li><a href="#beranda" class="text-white">Beranda</a></li>
                        <li><a href="#fitur" class="text-white">Fitur</a></li>
                        <li><a href="#tentang" class="text-white">Tentang</a></li>
                        <li><a href="#kontak" class="text-white">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Sosial Media</h6>
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-2x"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-2x"></i></a>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> EduLearn. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
</body>
</html>