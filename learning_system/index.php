<?php
require_once 'config.php';
require_once 'functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Belajar Interaktif - Learning System</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i> Learning System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <h1 class="fade-in">Platform Belajar Interaktif</h1>
            <p class="fade-in">Sistem pembelajaran modern dengan gamifikasi, forum diskusi, dan analitik canggih</p>
            <div class="mt-4">
                <a href="register.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-user-plus"></i> Gabung Sekarang
                </a>
                <a href="login.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4">Tentang Platform</h2>
                    <p class="text-muted">Learning System adalah platform pembelajaran online yang dirancang untuk memudahkan interaksi antara guru dan siswa. Dengan fitur-fitur modern dan interface yang user-friendly, pembelajaran menjadi lebih efektif dan menyenangkan.</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Akses materi kapan saja</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Sistem penilaian otomatis</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Forum diskusi interaktif</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Laporan kemajuan real-time</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="https://via.placeholder.com/500x400/ADD8E6/FFFFFF?text=Learning+Platform" alt="About" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Fitur Unggulan</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Dashboard Interaktif</h5>
                            <p class="card-text text-muted">Pantau aktivitas belajar dengan visualisasi data yang menarik dan mudah dipahami.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-book-open fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Manajemen Materi</h5>
                            <p class="card-text text-muted">Upload dan akses materi pembelajaran dalam berbagai format dengan mudah.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tasks fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Sistem Tugas & Kuis</h5>
                            <p class="card-text text-muted">Buat dan kerjakan tugas dengan sistem penilaian otomatis dan manual.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-comments fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Forum Diskusi</h5>
                            <p class="card-text text-muted">Berkolaborasi dan berdiskusi dengan teman sekelas dan guru.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-trophy fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Gamifikasi</h5>
                            <p class="card-text text-muted">Dapatkan poin dan badge untuk memotivasi proses pembelajaran.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-bell fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Notifikasi Real-time</h5>
                            <p class="card-text text-muted">Dapatkan pengingat untuk tugas, deadline, dan pengumuman penting.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Apa Kata Mereka</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Platform yang sangat membantu! Siswa jadi lebih aktif dan terorganisir dalam belajar."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="User">
                                <div>
                                    <h6 class="mb-0">Bu Siti</h6>
                                    <small class="text-muted">Guru Matematika</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Fitur gamifikasinya keren! Jadi lebih semangat belajar dan ngerjain tugas."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="User">
                                <div>
                                    <h6 class="mb-0">Ahmad</h6>
                                    <small class="text-muted">Siswa Kelas 11</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Mudah memantau perkembangan anak. Laporan yang detail dan informatif!"</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="User">
                                <div>
                                    <h6 class="mb-0">Pak Budi</h6>
                                    <small class="text-muted">Orang Tua</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Hubungi Kami</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <form id="contactForm">
                                <div class="mb-3">
                                    <label class="form-label">Nama</label>
                                    <input type="text" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pesan</label>
                                    <textarea class="form-control" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane"></i> Kirim Pesan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-graduation-cap"></i> Learning System</h5>
                    <p class="text-muted">Platform pembelajaran interaktif untuk masa depan pendidikan yang lebih baik.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Menu</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home">Beranda</a></li>
                        <li><a href="#about">Tentang</a></li>
                        <li><a href="#features">Fitur</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Kontak</h5>
                    <p class="text-muted">
                        <i class="fas fa-envelope"></i> info@learning.com<br>
                        <i class="fas fa-phone"></i> +62 812 3456 7890
                    </p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light">
            <p class="text-center mb-0">&copy; 2024 Learning System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Contact form submit
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            alert('Terima kasih! Pesan Anda telah terkirim.');
            this.reset();
        });

        // Smooth scroll
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this.getAttribute('href'));
            if(target.length) {
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 70
                }, 1000);
            }
        });
    </script>
</body>
</html>
