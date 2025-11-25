/**
 * Main JavaScript File
 * Handles AJAX, notifications, and interactive features
 */

$(document).ready(function() {
    // Load notifications
    loadNotifications();
    
    // Poll for new notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Mark notification as read on click
    $(document).on('click', '.notification-item', function() {
        const notifId = $(this).data('id');
        markAsRead(notifId);
    });
});

// Load notifications via AJAX
function loadNotifications() {
    $.ajax({
        url: 'api/notifications.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateNotificationBadge(response.unread_count);
                updateNotificationDropdown(response.notifications);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading notifications:', error);
        }
    });
}

// Update notification badge
function updateNotificationBadge(count) {
    const badge = $('#notifCount');
    if (count > 0) {
        badge.text(count).show();
    } else {
        badge.hide();
    }
}

// Update notification dropdown
function updateNotificationDropdown(notifications) {
    const dropdown = $('#notifDropdown');
    dropdown.empty();
    
    dropdown.append('<li><h6 class="dropdown-header">Notifikasi</h6></li>');
    dropdown.append('<li><hr class="dropdown-divider"></li>');
    
    if (notifications.length === 0) {
        dropdown.append('<li><a class="dropdown-item text-center" href="#">Tidak ada notifikasi baru</a></li>');
    } else {
        notifications.slice(0, 5).forEach(function(notif) {
            const item = `
                <li>
                    <a class="dropdown-item notification-item ${notif.read_status ? '' : 'unread'}" 
                       href="${notif.link || '#'}" 
                       data-id="${notif.id}">
                        <small class="text-muted">${timeAgo(notif.created_at)}</small><br>
                        ${escapeHtml(notif.pesan)}
                    </a>
                </li>
            `;
            dropdown.append(item);
        });
        
        dropdown.append('<li><hr class="dropdown-divider"></li>');
        dropdown.append('<li><a class="dropdown-item text-center text-primary" href="notifikasi.php">Lihat Semua</a></li>');
    }
}

// Mark notification as read
function markAsRead(notifId) {
    $.ajax({
        url: 'api/notifications.php',
        method: 'POST',
        data: { action: 'mark_read', id: notifId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadNotifications();
            }
        }
    });
}

// Time ago helper
function timeAgo(datetime) {
    const timestamp = new Date(datetime).getTime();
    const now = Date.now();
    const diff = Math.floor((now - timestamp) / 1000);
    
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return Math.floor(diff / 60) + ' menit yang lalu';
    if (diff < 86400) return Math.floor(diff / 3600) + ' jam yang lalu';
    if (diff < 604800) return Math.floor(diff / 86400) + ' hari yang lalu';
    
    return new Date(datetime).toLocaleDateString('id-ID');
}

// Escape HTML helper
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Forum functions
function likePost(postId) {
    $.ajax({
        url: 'api/forum.php',
        method: 'POST',
        data: { action: 'like', post_id: postId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $(`#likes-${postId}`).text(response.likes);
            }
        }
    });
}

// Add reply to forum
function addReply(postId) {
    const content = $(`#reply-content-${postId}`).val();
    
    if (!content.trim()) {
        alert('Konten reply tidak boleh kosong');
        return;
    }
    
    $.ajax({
        url: 'api/forum.php',
        method: 'POST',
        data: {
            action: 'reply',
            post_id: postId,
            content: content
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Terjadi kesalahan');
            }
        }
    });
}

// File upload preview
function previewFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileName = file.name;
        const fileSize = (file.size / 1024).toFixed(2);
        
        $(input).next('.file-preview').remove();
        $(input).after(`
            <div class="file-preview mt-2 p-2 bg-light rounded">
                <i class="fas fa-file"></i> ${fileName} (${fileSize} KB)
            </div>
        `);
    }
}

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Apakah Anda yakin ingin menghapus?');
}

// Show loading
function showLoading() {
    $('body').append(`
        <div class="loading-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);
}

// Hide loading
function hideLoading() {
    $('.loading-overlay').remove();
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    return true;
}

// Update points display
function updatePoints(points) {
    $.ajax({
        url: 'api/gamification.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('.points-display').text(response.points);
            }
        }
    });
}

// Search functionality
$('#searchInput').on('keyup', function() {
    const value = $(this).val().toLowerCase();
    $('.searchable').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

// Auto-resize textarea
$('textarea').on('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// Tooltip initialization
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Add loading overlay CSS if not exists
if ($('.loading-overlay-style').length === 0) {
    $('head').append(`
        <style class="loading-overlay-style">
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
        </style>
    `);
}