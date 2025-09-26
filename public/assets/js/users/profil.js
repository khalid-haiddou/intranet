        // Avatar upload functionality
        function uploadAvatar() {
            document.getElementById('avatarInput').click();
        }

        document.getElementById('avatarInput').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    alert('Photo de profil mise à jour avec succès !');
                };
                reader.readAsDataURL(e.target.files[0]);
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
                return;
            }
            
            switch (strength) {
                case 1:
                    strengthBar.classList.add('strength-weak');
                    strengthText.textContent = 'Faible';
                    strengthText.style.color = '#E74C3C';
                    break;
                case 2:
                    strengthBar.classList.add('strength-fair');
                    strengthText.textContent = 'Moyen';
                    strengthText.style.color = '#F39C12';
                    break;
                case 3:
                    strengthBar.classList.add('strength-good');
                    strengthText.textContent = 'Bon';
                    strengthText.style.color = '#3498DB';
                    break;
                case 4:
                    strengthBar.classList.add('strength-strong');
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

        // Form submissions
        document.getElementById('personalInfoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showSuccessAlert('Informations personnelles mises à jour avec succès !');
        });

        document.getElementById('securityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                alert('Les mots de passe ne correspondent pas');
                return;
            }
            
            if (calculatePasswordStrength(password) < 2) {
                alert('Le mot de passe doit être plus fort');
                return;
            }
            
            showSuccessAlert('Mot de passe modifié avec succès !');
            this.reset();
            strengthBar.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
            strengthText.textContent = 'Saisissez votre nouveau mot de passe';
            strengthText.style.color = '#6c757d';
            passwordMatch.textContent = '';
        });

        // Document downloads
        function downloadDocument(docType) {
            alert(`Téléchargement du document: ${docType}`);
        }

        // Danger zone actions
        function deactivateAccount() {
            if (confirm('Êtes-vous sûr de vouloir désactiver temporairement votre compte ?')) {
                alert('Demande de désactivation envoyée. Vous recevrez un email de confirmation.');
            }
        }

        function deleteAccount() {
            const confirmation = prompt('Pour supprimer définitivement votre compte, tapez "SUPPRIMER" :');
            if (confirmation === 'SUPPRIMER') {
                alert('Demande de suppression envoyée. Un email de confirmation vous sera envoyé.');
            } else if (confirmation !== null) {
                alert('Confirmation incorrecte. Suppression annulée.');
            }
        }

        // Success alert
        function showSuccessAlert(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-success';
            alert.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
            
            const container = document.querySelector('.main-content');
            container.insertBefore(alert, container.children[1]);
            
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Animation on load
        function animateOnLoad() {
            const loadingElements = document.querySelectorAll('.loading');
            loadingElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
                }, index * 150);
            });
        }

        // Sidebar navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
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

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            animateOnLoad();
        });
 