/* Role Mismatch Error Page JavaScript */

function goBack() {
    // Try to go back in history, fallback to dashboard or login
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Fallback URLs will be set by blade template
        if (window.roleBasedRedirect) {
            window.location.href = window.roleBasedRedirect;
        } else {
            window.location.href = '/login';
        }
    }
}

function showLogoutConfirm() {
    const modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
    modal.show();
}

// Auto-focus on the logout button after page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const logoutBtn = document.querySelector('.btn-logout');
        if (logoutBtn) {
            logoutBtn.focus();
        }
    }, 500);
});

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        goBack();
    } else if (e.key === 'Enter') {
        showLogoutConfirm();
    }
});
