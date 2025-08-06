/**
 * App Layout JavaScript
 * Contains global functionality for the application layout
 */

$(document).ready(function() {
    // Initialize Bootstrap tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Ensure dropdown menus work properly
    $('.dropdown-toggle').dropdown();
    
    // Global AJAX error handler for role mismatch
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.role_mismatch) {
            const response = xhr.responseJSON;
            showRoleMismatchModal(response.current_role, response.required_role);
        }
    });
});

/**
 * Show role mismatch modal for AJAX requests
 * @param {string} currentRole - User's current role
 * @param {string} requiredRole - Required role for the action
 */
function showRoleMismatchModal(currentRole, requiredRole) {
    const modalHtml = `
        <div class="modal fade" id="roleMismatchModal" tabindex="-1" aria-labelledby="roleMismatchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="roleMismatchModalLabel">
                            <i class="bi bi-shield-exclamation me-2"></i>
                            Access Denied - Role Mismatch
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 mb-3">Permission Required</h5>
                            <p class="text-muted">
                                You are logged in as <strong>${currentRole.toUpperCase()}</strong> but this action requires 
                                <strong>${requiredRole.toUpperCase()}</strong> privileges.
                            </p>
                            <p class="text-muted">
                                Would you like to logout and login with the correct role?
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="logoutAndSwitch()">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Logout & Switch
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#roleMismatchModal').remove();
    
    // Add new modal to body and show
    $('body').append(modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('roleMismatchModal'));
    modal.show();
}

/**
 * Logout and redirect to login page
 */
function logoutAndSwitch() {
    // Create and submit logout form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.logoutRoute || '/logout';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
}
