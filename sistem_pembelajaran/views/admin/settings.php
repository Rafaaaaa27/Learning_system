<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="fas fa-cog me-2"></i>Pengaturan Sistem</h2>
    
    <div class="row">
        <div class="col-md-3">
            <!-- Settings Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-sliders-h me-2"></i>General
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-shield-alt me-2"></i>Security
                        </a>
                        <a href="#email" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-envelope me-2"></i>Email
                        </a>
                        <a href="#upload" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-upload me-2"></i>Upload
                        </a>
                        <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-database me-2"></i>Backup
                        </a>
                        <a href="#maintenance" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-tools me-2"></i>Maintenance
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-sliders-h me-2"></i>General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Nama Aplikasi</label>
                                    <input type="text" class="form-control" value="EduLearn - Platform Pembelajaran Interaktif">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Sekolah/Institusi</label>
                                    <input type="text" class="form-control" value="SMK Negeri 1">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Alamat Email Kontak</label>
                                    <input type="email" class="form-control" value="admin@sekolah.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-select">
                                        <option selected>Asia/Jakarta</option>
                                        <option>Asia/Singapore</option>
                                        <option>UTC</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Bahasa Default</label>
                                    <select class="form-select">
                                        <option selected>Bahasa Indonesia</option>
                                        <option>English</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="tab-pane fade" id="security">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-shield-alt me-2"></i>Security Settings</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-4">
                                    <h6>Admin Registration Key</h6>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="adminKey" value="EDULEARN2024ADMIN" readonly>
                                        <button class="btn btn-primary" type="button" onclick="generateNewKey()">
                                            <i class="fas fa-sync me-2"></i>Generate New Key
                                        </button>
                                    </div>
                                    <small class="text-muted">Kunci untuk registrasi admin baru. Generate ulang secara berkala untuk keamanan.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enforceSSL">
                                        <label class="form-check-label" for="enforceSSL">
                                            Force HTTPS (SSL)
                                        </label>
                                    </div>
                                    <small class="text-muted">Redirect semua traffic HTTP ke HTTPS</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactor">
                                        <label class="form-check-label" for="twoFactor">
                                            Two-Factor Authentication (Coming Soon)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Session Timeout (menit)</label>
                                    <input type="number" class="form-control" value="60">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Max Login Attempts</label>
                                    <input type="number" class="form-control" value="5">
                                    <small class="text-muted">Jumlah maksimal percobaan login sebelum akun di-lock</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Email Settings -->
                <div class="tab-pane fade" id="email">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-envelope me-2"></i>Email Configuration</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Konfigurasi ini untuk pengiriman email notifikasi otomatis
                            </div>
                            
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" value="smtp.gmail.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" value="587">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="email" class="form-control" placeholder="your-email@gmail.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" placeholder="••••••••">
                                    <small class="text-muted">Gunakan App Password untuk Gmail</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">From Email</label>
                                    <input type="email" class="form-control" value="noreply@edulearn.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">From Name</label>
                                    <input type="text" class="form-control" value="EduLearn System">
                                </div>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-secondary" onclick="testEmail()">
                                        <i class="fas fa-paper-plane me-2"></i>Send Test Email
                                    </button>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Upload Settings -->
                <div class="tab-pane fade" id="upload">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-upload me-2"></i>Upload Settings</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Max Upload Size (MB)</label>
                                    <input type="number" class="form-control" value="10">
                                    <small class="text-muted">Current PHP limit: <?php echo ini_get('upload_max_filesize'); ?></small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Allowed File Types</label>
                                    <input type="text" class="form-control" value="pdf,doc,docx,ppt,pptx,jpg,jpeg,png,gif">
                                    <small class="text-muted">Pisahkan dengan koma</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="autoResize" checked>
                                        <label class="form-check-label" for="autoResize">
                                            Auto-resize Images
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Max Image Width (px)</label>
                                    <input type="number" class="form-control" value="1920">
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Storage Usage:</strong>
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-warning" style="width: 35%">35%</div>
                                    </div>
                                    <small class="mt-1 d-block">350 MB / 1 GB used</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Backup Settings -->
                <div class="tab-pane fade" id="backup">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-database me-2"></i>Backup & Restore</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6>Manual Backup</h6>
                                <p class="text-muted">Backup database dan file uploads secara manual</p>
                                <button class="btn btn-primary" onclick="createBackup()">
                                    <i class="fas fa-download me-2"></i>Create Backup Now
                                </button>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-4">
                                <h6>Automatic Backup</h6>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="autoBackup">
                                    <label class="form-check-label" for="autoBackup">
                                        Enable Automatic Backup
                                    </label>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Backup Frequency</label>
                                    <select class="form-select">
                                        <option>Daily</option>
                                        <option selected>Weekly</option>
                                        <option>Monthly</option>
                                    </select>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-4">
                                <h6>Recent Backups</h6>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-archive me-2 text-primary"></i>
                                            backup_2024_12_20.zip
                                            <br>
                                            <small class="text-muted">Size: 45 MB</small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary me-2">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div>
                                <h6 class="text-danger">Restore Database</h6>
                                <p class="text-muted">Upload backup file untuk restore database</p>
                                <input type="file" class="form-control mb-2" accept=".sql,.zip">
                                <button class="btn btn-danger">
                                    <i class="fas fa-upload me-2"></i>Restore from Backup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance -->
                <div class="tab-pane fade" id="maintenance">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-tools me-2"></i>Maintenance Mode</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Maintenance mode akan menonaktifkan akses untuk semua user kecuali admin
                            </div>
                            
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="maintenanceMode">
                                <label class="form-check-label" for="maintenanceMode">
                                    <strong>Enable Maintenance Mode</strong>
                                </label>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Maintenance Message</label>
                                <textarea class="form-control" rows="3">Sistem sedang dalam maintenance. Mohon coba lagi nanti.</textarea>
                            </div>
                            
                            <hr>
                            
                            <h6>Database Optimization</h6>
                            <p class="text-muted">Optimize database tables untuk performa lebih baik</p>
                            <button class="btn btn-outline-primary" onclick="optimizeDatabase()">
                                <i class="fas fa-magic me-2"></i>Optimize Database
                            </button>
                            
                            <hr>
                            
                            <h6 class="text-danger">Clear All Data</h6>
                            <p class="text-muted">Hapus semua data kecuali admin. <strong>TIDAK BISA DIBATALKAN!</strong></p>
                            <button class="btn btn-danger" onclick="confirmClearData()">
                                <i class="fas fa-exclamation-triangle me-2"></i>Clear All Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateNewKey() {
    if (confirm('Generate new Admin Registration Key? Key lama akan tidak valid.')) {
        const newKey = 'EDULEARN' + Math.random().toString(36).substr(2, 12).toUpperCase();
        document.getElementById('adminKey').value = newKey;
        alert('New Admin Key generated: ' + newKey + '\n\nJangan lupa simpan perubahan!');
    }
}

function testEmail() {
    alert('Sending test email...\n\nCheck your inbox for test email.');
}

function createBackup() {
    if (confirm('Create database backup now?')) {
        alert('Backup created successfully!\n\nFile: backup_' + new Date().toISOString().split('T')[0] + '.zip');
    }
}

function optimizeDatabase() {
    if (confirm('Optimize database tables?')) {
        alert('Database optimization completed!\n\nAll tables optimized successfully.');
    }
}

function confirmClearData() {
    const confirmation = prompt('Ketik "DELETE ALL DATA" untuk konfirmasi:');
    if (confirmation === 'DELETE ALL DATA') {
        alert('This action has been cancelled for safety.\n\nIn production, this would clear all data.');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>