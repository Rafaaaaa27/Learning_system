/**
 * Sistem Pembelajaran - Main JavaScript
 * Menggunakan jQuery dan Chart.js
 */

$(document).ready(function() {
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Form validation
    $('.needs-validation').on('submit', function(e) {
        if (this.checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Preview uploaded image
    $('.image-upload').on('change', function(e) {
        const file = e.target.files[0];
        const preview = $(this).data('preview');
        
        if (file && preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(preview).attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Notifikasi Real-time (Polling setiap 30 detik)
    if ($('#notif-badge').length > 0) {
        loadNotifications();
        setInterval(loadNotifications, 30000);
    }
    
    function loadNotifications() {
        $.ajax({
            url: 'api/notifikasi.php?action=unread_count',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.count > 0) {
                    $('#notif-badge').text(response.count).show();
                    
                    // Play notification sound (optional)
                    if (response.count > parseInt($('#notif-badge').data('prev-count') || 0)) {
                        playNotificationSound();
                    }
                    
                    $('#notif-badge').data('prev-count', response.count);
                } else {
                    $('#notif-badge').hide();
                }
            }
        });
    }
    
    function playNotificationSound() {
        // Optional: Play notification sound
        const audio = new Audio('assets/sounds/notification.mp3');
        audio.play().catch(e => console.log('Audio play failed:', e));
    }
    
    // Load notifikasi dropdown
    $('#notif-dropdown').on('show.bs.dropdown', function() {
        loadNotificationList();
    });
    
    function loadNotificationList() {
        $.ajax({
            url: 'api/notifikasi.php?action=list',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderNotifications(response.data);
                }
            }
        });
    }
    
    function renderNotifications(notifications) {
        const container = $('#notif-list');
        container.empty();
        
        if (notifications.length === 0) {
            container.html('<div class="dropdown-item text-center text-muted">Tidak ada notifikasi</div>');
            return;
        }
        
        notifications.forEach(function(notif) {
            const item = `
                <a href="#" class="dropdown-item notif-item ${!notif.read_status ? 'unread' : ''}" 
                   data-id="${notif.id}" data-link="${notif.link || '#'}">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="mb-0">${notif.pesan}</p>
                            <small class="text-muted">${timeAgo(notif.created_at)}</small>
                        </div>
                        ${!notif.read_status ? '<span class="badge bg-primary">Baru</span>' : ''}
                    </div>
                </a>
            `;
            container.append(item);
        });
    }
    
    // Mark notification as read
    $(document).on('click', '.notif-item', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const link = $(this).data('link');
        
        $.ajax({
            url: 'api/notifikasi.php?action=mark_read',
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (link && link !== '#') {
                        window.location.href = link;
                    } else {
                        loadNotifications();
                    }
                }
            }
        });
    });
    
    // Like forum post
    $(document).on('click', '.btn-like-post', function(e) {
        e.preventDefault();
        const postId = $(this).data('post-id');
        const btn = $(this);
        
        $.ajax({
            url: 'api/forum.php?action=like',
            method: 'POST',
            data: { post_id: postId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const likeCount = btn.find('.like-count');
                    likeCount.text(response.likes);
                    btn.toggleClass('liked');
                    
                    // Update icon
                    const icon = btn.find('i');
                    if (btn.hasClass('liked')) {
                        icon.removeClass('far').addClass('fas');
                    } else {
                        icon.removeClass('fas').addClass('far');
                    }
                }
            }
        });
    });
    
    // Submit forum reply
    $('#form-forum-reply').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = form.serialize();
        
        $.ajax({
            url: 'api/forum.php?action=reply',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear form
                    form[0].reset();
                    
                    // Reload replies
                    loadForumReplies(form.find('[name="post_id"]').val());
                    
                    // Show success message
                    showToast('success', 'Reply berhasil ditambahkan');
                } else {
                    showToast('error', response.message || 'Gagal menambahkan reply');
                }
            }
        });
    });
    
    // Load forum replies
    function loadForumReplies(postId) {
        $.ajax({
            url: 'api/forum.php?action=get_replies',
            method: 'GET',
            data: { post_id: postId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderForumReplies(postId, response.data);
                }
            }
        });
    }
    
    function renderForumReplies(postId, replies) {
        const container = $(`#replies-${postId}`);
        container.empty();
        
        replies.forEach(function(reply) {
            const item = `
                <div class="reply-item ms-4 mb-3">
                    <div class="d-flex">
                        <img src="uploads/profiles/${reply.foto}" class="avatar me-3" alt="${reply.nama}">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>${reply.nama}</strong>
                                <small class="text-muted">${timeAgo(reply.created_at)}</small>
                            </div>
                            <p class="mb-0">${reply.konten}</p>
                        </div>
                    </div>
                </div>
            `;
            container.append(item);
        });
    }
    
    // Update gamifikasi poin real-time
    function updateGamifikasiPoin(userId) {
        $.ajax({
            url: 'api/gamifikasi.php?action=get_poin',
            method: 'GET',
            data: { user_id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#user-poin').text(response.poin);
                    
                    // Animate poin change
                    $('#user-poin').addClass('poin-updated');
                    setTimeout(function() {
                        $('#user-poin').removeClass('poin-updated');
                    }, 1000);
                }
            }
        });
    }
    
    // Leaderboard refresh
    if ($('#leaderboard-table').length > 0) {
        setInterval(function() {
            loadLeaderboard();
        }, 60000); // Refresh every minute
    }
    
    function loadLeaderboard() {
        const kelasId = $('#leaderboard-table').data('kelas-id');
        
        $.ajax({
            url: 'api/gamifikasi.php?action=leaderboard',
            method: 'GET',
            data: { kelas_id: kelasId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderLeaderboard(response.data);
                }
            }
        });
    }
    
    function renderLeaderboard(data) {
        const tbody = $('#leaderboard-table tbody');
        tbody.empty();
        
        data.forEach(function(item, index) {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <img src="uploads/profiles/${item.foto}" class="avatar-sm me-2" alt="${item.nama}">
                        ${item.nama}
                    </td>
                    <td><span class="badge bg-primary">${item.poin}</span></td>
                    <td>${renderBadges(item.badges)}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function renderBadges(badges) {
        if (!badges) return '-';
        
        const badgeArray = JSON.parse(badges);
        let html = '';
        
        badgeArray.forEach(function(badge) {
            html += `<span class="badge bg-success me-1">${badge}</span>`;
        });
        
        return html;
    }
    
    // Toast notification
    function showToast(type, message) {
        const toast = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        const container = $('#toast-container');
        if (container.length === 0) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        $('#toast-container').append(toast);
        const toastElement = $('.toast').last();
        const bsToast = new bootstrap.Toast(toastElement[0]);
        bsToast.show();
        
        // Remove after hidden
        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    // Time ago helper
    function timeAgo(datetime) {
        const seconds = Math.floor((new Date() - new Date(datetime)) / 1000);
        
        const intervals = {
            tahun: 31536000,
            bulan: 2592000,
            minggu: 604800,
            hari: 86400,
            jam: 3600,
            menit: 60,
            detik: 1
        };
        
        for (const [name, value] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / value);
            if (interval >= 1) {
                return interval + ' ' + name + ' yang lalu';
            }
        }
        
        return 'Baru saja';
    }
    
    // Chart.js Dashboard
    if ($('#statistikChart').length > 0) {
        renderStatistikChart();
    }
    
    function renderStatistikChart() {
        const ctx = document.getElementById('statistikChart').getContext('2d');
        
        // Get data from data attribute
        const chartData = $('#statistikChart').data('chart');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Nilai Rata-rata',
                    data: chartData.data,
                    backgroundColor: 'rgba(173, 216, 230, 0.2)',
                    borderColor: '#ADD8E6',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
    
    // Form auto-save (draft)
    let autoSaveTimer;
    $('.auto-save-form textarea, .auto-save-form input[type="text"]').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            saveDraft();
        }, 2000);
    });
    
    function saveDraft() {
        const formData = $('.auto-save-form').serialize();
        
        $.ajax({
            url: 'api/draft.php?action=save',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.draft-status').text('Draft disimpan').fadeIn().delay(2000).fadeOut();
                }
            }
        });
    }
    
    // Print functionality
    $('.btn-print').on('click', function() {
        window.print();
    });
    
    // Export to PDF (requires jsPDF library)
    $('.btn-export-pdf').on('click', function() {
        alert('Fitur export PDF akan segera hadir!');
    });
    
});

// Service Worker for PWA (optional)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(function(registration) {
        console.log('Service Worker registered:', registration);
    }).catch(function(error) {
        console.log('Service Worker registration failed:', error);
    });
}