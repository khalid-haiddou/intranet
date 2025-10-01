// Set CSRF token for all AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Animation on load
function animateOnLoad() {
    const loadingElements = document.querySelectorAll('.loading');
    loadingElements.forEach((element, index) => {
        setTimeout(() => {
            element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
        }, index * 150);
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    animateOnLoad();
});

// Avatar upload functionality - COMPLETE VERSION WITH AJAX
function uploadAvatar() {
    document.getElementById('avatarInput').click();
}

document.getElementById('avatarInput').addEventListener('change', async function(e) {
    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        
        // Validate file size (max 2MB)
        if (file.size > 2048 * 1024) {
            showErrorAlert('L\'image ne doit pas dépasser 2 MB');
            return;
        }
        
        // Validate file type
        if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type)) {
            showErrorAlert('Format non supporté. Utilisez JPG, PNG ou GIF');
            return;
        }
        
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', csrfToken);
        
        try {
            // Preview image immediately for better UX
            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarImg = document.getElementById('avatarImage');
                const avatarInitials = document.getElementById('avatarInitials');
                
                if (avatarImg) {
                    // Update existing image
                    avatarImg.src = e.target.result;
                } else if (avatarInitials) {
                    // Replace initials with image
                    const avatarContainer = avatarInitials.parentElement;
                    avatarInitials.remove();
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'avatar-img-photo';
                    img.id = 'avatarImage';
                    avatarContainer.insertBefore(img, avatarContainer.firstChild);
                }
            };
            reader.readAsDataURL(file);
            
            // Upload to server
            const response = await fetch('/user/profil/avatar', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccessAlert(data.message);
                // Update with server URL if different
                if (data.data && data.data.avatar_url) {
                    const avatarImg = document.getElementById('avatarImage');
                    if (avatarImg) {
                        avatarImg.src = data.data.avatar_url;
                    }
                }
            } else {
                showErrorAlert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showErrorAlert('Erreur lors de l\'upload de l\'image');
        }
    }
});

// Password strength checker
const newPasswordInput = document.getElementById('newPassword');
const confirmPasswordInput = document.getElementById('confirmPassword');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');
const passwordMatch = document.getElementById('passwordMatch');

newPasswordInput.addEventListener('input', function() {
    const password = this.value;
    const strength = calculatePasswordStrength(password);
    
    strengthBar.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
    
    if (password.length === 0) {
        strengthText.textContent = 'Saisissez votre nouveau mot de passe';
        strengthText.style.color = '#6c757d';
        strengthBar.style.width = '0%';
        return;
    }
    
    switch (strength) {
        case 1:
            strengthBar.classList.add('strength-weak');
            strengthBar.style.width = '25%';
            strengthText.textContent = 'Faible';
            strengthText.style.color = '#E74C3C';
            break;
        case 2:
            strengthBar.classList.add('strength-fair');
            strengthBar.style.width = '50%';
            strengthText.textContent = 'Moyen';
            strengthText.style.color = '#F39C12';
            break;
        case 3:
            strengthBar.classList.add('strength-good');
            strengthBar.style.width = '75%';
            strengthText.textContent = 'Bon';
            strengthText.style.color = '#3498DB';
            break;
        case 4:
            strengthBar.classList.add('strength-strong');
            strengthBar.style.width = '100%';
            strengthText.textContent = 'Fort';
            strengthText.style.color = '#27AE60';
            break;
    }
    checkPasswordMatch();
});

confirmPasswordInput.addEventListener('input', checkPasswordMatch);

function calculatePasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return Math.min(strength, 4);
}

function checkPasswordMatch() {
    const password = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    if (confirmPassword.length === 0) {
        passwordMatch.textContent = '';
        return;
    }
    
    if (password === confirmPassword) {
        passwordMatch.textContent = 'Les mots de passe correspondent ✓';
        passwordMatch.style.color = '#27AE60';
    } else {
        passwordMatch.textContent = 'Les mots de passe ne correspondent pas';
        passwordMatch.style.color = '#E74C3C';
    }
}

// Personal Info Form - AJAX Submission
document.getElementById('personalInfoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Clear previous errors
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
    
    try {
        const response = await fetch('/user/profil/update', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessAlert(data.message);
            
            // Update profile header if data changed
            if (data.data && data.data.user) {
                const profileName = document.querySelector('.profile-details h3');
                if (profileName) {
                    profileName.textContent = data.data.user.display_name;
                }
                
                // Update profession badge if changed
                if (data.data.user.profession) {
                    let professionBadge = document.querySelector('.profession-badge');
                    if (professionBadge) {
                        professionBadge.innerHTML = `<i class="fas fa-briefcase me-2"></i>${data.data.user.profession}`;
                    } else {
                        // Create profession badge if it doesn't exist
                        const profileDetails = document.querySelector('.profile-details');
                        const h3 = profileDetails.querySelector('h3');
                        professionBadge = document.createElement('p');
                        professionBadge.className = 'profession-badge';
                        professionBadge.innerHTML = `<i class="fas fa-briefcase me-2"></i>${data.data.user.profession}`;
                        h3.insertAdjacentElement('afterend', professionBadge);
                    }
                } else {
                    // Remove profession badge if empty
                    const professionBadge = document.querySelector('.profession-badge');
                    if (professionBadge) {
                        professionBadge.remove();
                    }
                }
            }
        } else {
            showErrorAlert(data.message);
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = data.errors[key][0];
                        input.parentNode.appendChild(errorDiv);
                    }
                });
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorAlert('Une erreur est survenue lors de la mise à jour');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Security Form - AJAX Submission
document.getElementById('securityForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const password = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    // Validation
    if (password !== confirmPassword) {
        showErrorAlert('Les mots de passe ne correspondent pas');
        return;
    }
    
    if (calculatePasswordStrength(password) < 2) {
        showErrorAlert('Le mot de passe doit être plus fort (minimum: moyen)');
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Modification...';
    
    try {
        const response = await fetch('/user/profil/password', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessAlert(data.message);
            this.reset();
            strengthBar.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
            strengthBar.style.width = '0%';
            strengthText.textContent = 'Saisissez votre nouveau mot de passe';
            strengthText.style.color = '#6c757d';
            passwordMatch.textContent = '';
        } else {
            showErrorAlert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorAlert('Une erreur est survenue');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Update notifications - AJAX
async function updateNotifications() {
    const newsletter = document.getElementById('newsletter').checked;
    
    try {
        const response = await fetch('/user/profil/notifications', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ newsletter })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessAlert(data.message);
        } else {
            showErrorAlert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorAlert('Une erreur est survenue');
    }
}

// Document downloads
function downloadDocument(docType) {
    showSuccessAlert(`Téléchargement du document: ${docType}`);
    // TODO: Implement actual download functionality
}

// Deactivate account - AJAX
async function deactivateAccount() {
    if (!confirm('Êtes-vous sûr de vouloir désactiver temporairement votre compte ? Vos réservations futures seront annulées.')) {
        return;
    }
    
    try {
        const response = await fetch('/user/profil/deactivate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessAlert(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showErrorAlert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorAlert('Une erreur est survenue');
    }
}

// Delete account - AJAX
async function deleteAccount() {
    const confirmation = prompt('Pour supprimer définitivement votre compte, tapez "SUPPRIMER" :');
    
    if (confirmation !== 'SUPPRIMER') {
        if (confirmation !== null) {
            showErrorAlert('Confirmation incorrecte. Suppression annulée.');
        }
        return;
    }
    
    if (!confirm('ATTENTION : Cette action est irréversible. Toutes vos données seront supprimées. Continuer ?')) {
        return;
    }
    
    try {
        const response = await fetch('/user/profil/delete', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessAlert('Compte supprimé. Redirection...');
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        } else {
            showErrorAlert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorAlert('Une erreur est survenue');
    }
}

// Success alert
function showSuccessAlert(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.style.cssText = 'position: relative; animation: slideDown 0.3s ease-out;';
    alert.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
    
    const container = document.querySelector('.main-content');
    container.insertBefore(alert, container.children[1]);
    
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// Error alert
function showErrorAlert(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger';
    alert.style.cssText = 'position: relative; animation: slideDown 0.3s ease-out;';
    alert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${message}`;
    
    const container = document.querySelector('.main-content');
    container.insertBefore(alert, container.children[1]);
    
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 4000);
}

// Sidebar navigation
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!this.getAttribute('href') || this.getAttribute('href') === '#') {
            e.preventDefault();
        }
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Mobile menu
if (window.innerWidth <= 768) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.createElement('button');
    toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
    toggleBtn.className = 'btn-primary position-fixed';
    toggleBtn.style.cssText = 'top: 20px; left: 20px; z-index: 1001; width: 50px; height: 50px; border-radius: 50%;';
    document.body.appendChild(toggleBtn);
    
    toggleBtn.addEventListener('click', function() {
        sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-280px)' : 'translateX(0px)';
    });
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .is-invalid {
        border-color: #E74C3C !important;
    }
    
    .invalid-feedback {
        color: #E74C3C;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .avatar-img-photo {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .profession-badge {
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 10px;
    }
`;
document.head.appendChild(style);