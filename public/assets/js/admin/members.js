// Set CSRF token for all AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Configure jQuery AJAX defaults if available, otherwise use fetch
if (typeof $ !== 'undefined') {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });
}

// Date actuelle
document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function showToast(message, type = 'success') {
    // Create a simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 10000; max-width: 350px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// API helper function
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Une erreur est survenue');
        }
        
        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

// View member details
async function viewMemberDetails(memberId) {
    try {
        showLoading();
        
        const data = await apiRequest(`/dashboard/members/${memberId}`);
        
        if (data.success) {
            displayMemberDetails(data.data);
            const modal = new bootstrap.Modal(document.getElementById('memberModal'));
            modal.show();
        }
    } catch (error) {
        showToast('Erreur lors du chargement des détails: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Display member details in modal
function displayMemberDetails(member) {
    const modalBody = document.getElementById('memberModalBody');
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="member-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 24px; line-height: 80px;">
                    ${member.display_name.substring(0, 2).toUpperCase()}
                </div>
                <h5>${member.display_name}</h5>
                <span class="badge bg-${member.is_active ? 'success' : 'secondary'}">${member.status_label}</span>
            </div>
            <div class="col-md-8">
                <h6><i class="fas fa-info-circle text-primary me-2"></i>Informations générales</h6>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Email:</strong><br>
                        <small class="text-muted">${member.email}</small>
                    </div>
                    <div class="col-sm-6">
                        <strong>Téléphone:</strong><br>
                        <small class="text-muted">${member.phone || 'Non renseigné'}</small>
                    </div>
                </div>
                
                ${member.address ? `
                    <div class="mb-3">
                        <strong>Adresse:</strong><br>
                        <small class="text-muted">${member.address}</small>
                    </div>
                ` : ''}
                
                <h6><i class="fas fa-id-card text-info me-2"></i>Type de compte: ${member.account_type_label}</h6>
                <div class="row mb-3">
                    ${member.account_type === 'individual' ? `
                        <div class="col-sm-6">
                            <strong>Prénom:</strong><br>
                            <small class="text-muted">${member.prenom}</small>
                        </div>
                        <div class="col-sm-6">
                            <strong>Nom:</strong><br>
                            <small class="text-muted">${member.nom}</small>
                        </div>
                        <div class="col-sm-6 mt-2">
                            <strong>CIN:</strong><br>
                            <small class="text-muted">${member.cin}</small>
                        </div>
                    ` : `
                        <div class="col-sm-12">
                            <strong>Nom de l'entreprise:</strong><br>
                            <small class="text-muted">${member.company_name}</small>
                        </div>
                        <div class="col-sm-6 mt-2">
                            <strong>RC:</strong><br>
                            <small class="text-muted">${member.rc}</small>
                        </div>
                        <div class="col-sm-6 mt-2">
                            <strong>ICE:</strong><br>
                            <small class="text-muted">${member.ice}</small>
                        </div>
                        <div class="col-sm-12 mt-2">
                            <strong>Représentant légal:</strong><br>
                            <small class="text-muted">${member.legal_representative}</small>
                        </div>
                    `}
                </div>
                
                <h6><i class="fas fa-crown text-warning me-2"></i>Abonnement</h6>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Plan:</strong><br>
                        <small class="text-muted">${member.membership_plan_label}</small>
                    </div>
                    <div class="col-sm-6">
                        <strong>Cycle:</strong><br>
                        <small class="text-muted">${member.billing_cycle_label}</small>
                    </div>
                    <div class="col-sm-6 mt-2">
                        <strong>Prix:</strong><br>
                        <small class="text-muted">${member.price_description}</small>
                    </div>
                </div>
                
                <h6><i class="fas fa-clock text-secondary me-2"></i>Activité</h6>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Inscription:</strong><br>
                        <small class="text-muted">${member.created_at}</small>
                    </div>
                    <div class="col-sm-6">
                        <strong>Dernière connexion:</strong><br>
                        <small class="text-muted">${member.last_login_at}</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Store member ID for edit functionality
    document.getElementById('editMemberBtn').dataset.memberId = member.id;
}

// Edit member functionality
function initializeEditForm(memberId) {
    // Fetch member data and populate edit form
    apiRequest(`/dashboard/members/${memberId}`)
        .then(data => {
            if (data.success) {
                populateEditForm(data.data);
                const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
                modal.show();
            }
        })
        .catch(error => {
            showToast('Erreur lors du chargement du formulaire: ' + error.message, 'danger');
        });
}

// Populate edit form with member data
function populateEditForm(member) {
    const modalBody = document.getElementById('editMemberModalBody');
    
    modalBody.innerHTML = `
        <input type="hidden" name="member_id" value="${member.id}">
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="${member.email}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Téléphone *</label>
                    <input type="tel" name="phone" class="form-control" value="${member.phone}" required>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <textarea name="address" class="form-control" rows="3">${member.address || ''}</textarea>
        </div>
        
        ${member.account_type === 'individual' ? `
            <h6 class="mb-3"><i class="fas fa-user me-2"></i>Informations personnelles</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="prenom" class="form-control" value="${member.prenom}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" value="${member.nom}" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">CIN *</label>
                <input type="text" name="cin" class="form-control" value="${member.cin}" required>
            </div>
        ` : `
            <h6 class="mb-3"><i class="fas fa-building me-2"></i>Informations entreprise</h6>
            <div class="mb-3">
                <label class="form-label">Nom de l'entreprise *</label>
                <input type="text" name="company_name" class="form-control" value="${member.company_name}" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">RC *</label>
                        <input type="text" name="rc" class="form-control" value="${member.rc}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ICE *</label>
                        <input type="text" name="ice" class="form-control" value="${member.ice}" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Représentant légal *</label>
                <input type="text" name="legal_representative" class="form-control" value="${member.legal_representative}" required>
            </div>
        `}
        
        <h6 class="mb-3"><i class="fas fa-crown me-2"></i>Abonnement</h6>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Plan *</label>
                    <select name="membership_plan" class="form-select" required>
                        <option value="hot-desk" ${member.membership_plan === 'hot-desk' ? 'selected' : ''}>Hot Desk</option>
                        <option value="bureau-dedie" ${member.membership_plan === 'bureau-dedie' ? 'selected' : ''}>Bureau Dédié</option>
                        <option value="bureau-prive" ${member.membership_plan === 'bureau-prive' ? 'selected' : ''}>Bureau Privé</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Cycle *</label>
                    <select name="billing_cycle" class="form-select" required>
                        <option value="daily" ${member.billing_cycle === 'daily' ? 'selected' : ''}>1 Jour</option>
                        <option value="weekly" ${member.billing_cycle === 'weekly' ? 'selected' : ''}>1 Semaine</option>
                        <option value="biweekly" ${member.billing_cycle === 'biweekly' ? 'selected' : ''}>2 Semaines</option>
                        <option value="monthly" ${member.billing_cycle === 'monthly' ? 'selected' : ''}>1 Mois</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Prix (MAD) *</label>
                    <input type="number" name="price" class="form-control" value="${member.price}" min="0" step="0.01" required>
                </div>
            </div>
        </div>
    `;
}

// Handle edit form submission
async function handleEditFormSubmission(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const memberId = formData.get('member_id');
    
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'member_id') {
            data[key] = value;
        }
    }
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/members/${memberId}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
        
        if (result.success) {
            showToast('Membre mis à jour avec succès!');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editMemberModal'));
            modal.hide();
            
            // Refresh the page or update the table row
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    } catch (error) {
        showToast('Erreur lors de la mise à jour: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Toggle member status
async function toggleMemberStatus(memberId) {
    if (!confirm('Êtes-vous sûr de vouloir changer le statut de ce membre?')) {
        return;
    }
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/members/${memberId}/toggle-status`, {
            method: 'POST'
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Update the table row
            updateTableRow(memberId, result.data);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Approve member
async function approveMember(memberId) {
    if (!confirm('Êtes-vous sûr de vouloir approuver ce membre?')) {
        return;
    }
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/members/${memberId}/approve`, {
            method: 'POST'
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Refresh page to update stats and activity
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Reject member
async function rejectMember(memberId) {
    if (!confirm('Êtes-vous sûr de vouloir rejeter ce membre? Cette action est irréversible.')) {
        return;
    }
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/members/${memberId}/reject`, {
            method: 'POST'
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Remove the table row
            const row = document.querySelector(`tr[data-member-id="${memberId}"]`);
            if (row) {
                row.remove();
            }
            
            // Refresh page to update stats
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Update table row after status change
function updateTableRow(memberId, data) {
    const row = document.querySelector(`tr[data-member-id="${memberId}"]`);
    if (row) {
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.className = `status-badge status-${data.is_active ? 'active' : 'inactive'}`;
            statusBadge.textContent = data.status_label;
        }
        
        // Update action buttons
        const toggleBtn = row.querySelector('.toggle-status');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            icon.className = `fas fa-${data.is_active ? 'user-times' : 'user-check'}`;
            toggleBtn.title = data.is_active ? 'Désactiver' : 'Activer';
        }
    }
}

// Animation des chiffres (keep from original)
function animateNumbers() {
    const numbers = document.querySelectorAll('[id$="-number"]');
    numbers.forEach(element => {
        const target = parseInt(element.textContent);
        animateValue(element, 0, target, 1000);
    });
}

function animateValue(element, start, end, duration, suffix = '') {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (end - start) + start);
        
        element.textContent = currentValue + suffix;
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animation d'apparition des éléments
function animateOnLoad() {
    const loadingElements = document.querySelectorAll('.loading');
    loadingElements.forEach((element, index) => {
        setTimeout(() => {
            element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
        }, index * 150);
    });
}

// Gestion des clics sur la sidebar
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        // Only prevent default for links without href or with # href
        if (!this.href || this.href.endsWith('#')) {
            e.preventDefault();
        }
        
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Menu mobile
if (window.innerWidth <= 768) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.createElement('button');
    toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
    toggleBtn.className = 'notification-btn position-fixed';
    toggleBtn.style.cssText = 'top: 20px; left: 20px; z-index: 1001;';
    document.body.appendChild(toggleBtn);
    
    toggleBtn.addEventListener('click', function() {
        sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-280px)' : 'translateX(0px)';
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    animateOnLoad();
    setTimeout(animateNumbers, 800);
    
    // View member details
    document.querySelectorAll('.view-member').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            viewMemberDetails(memberId);
        });
    });
    
    // Edit member
    document.querySelectorAll('.edit-member').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            initializeEditForm(memberId);
        });
    });
    
    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            toggleMemberStatus(memberId);
        });
    });
    
    // Approve member
    document.querySelectorAll('.approve-member').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            approveMember(memberId);
        });
    });
    
    // Reject member
    document.querySelectorAll('.reject-member').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            rejectMember(memberId);
        });
    });
    
    // Edit member button in details modal
    document.getElementById('editMemberBtn').addEventListener('click', function() {
        const memberId = this.dataset.memberId;
        if (memberId) {
            // Close the details modal first
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('memberModal'));
            detailsModal.hide();
            
            // Open edit modal
            setTimeout(() => {
                initializeEditForm(memberId);
            }, 300);
        }
    });
    
    // Edit form submission
    document.getElementById('editMemberForm').addEventListener('submit', handleEditFormSubmission);
});

// Refresh stats periodically (every 5 minutes)
setInterval(async function() {
    try {
        const data = await apiRequest('/dashboard/members/stats');
        if (data.success) {
            // Update stats numbers
            document.getElementById('total-number').textContent = data.data.total;
            document.getElementById('active-number').textContent = data.data.active;
            document.getElementById('new-number').textContent = data.data.inactive;
            document.getElementById('pending-number').textContent = data.data.pending;
            
            // Update notification badge
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                badge.textContent = data.data.pending;
            }
        }
    } catch (error) {
        console.error('Failed to refresh stats:', error);
    }
}, 300000); // 5 minutes