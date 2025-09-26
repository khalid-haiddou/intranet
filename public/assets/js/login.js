        class LoginManager {
            constructor() {
                this.form = document.getElementById('loginForm');
                this.emailInput = document.getElementById('email');
                this.passwordInput = document.getElementById('password');
                this.loginBtn = document.getElementById('loginBtn');
                this.alertContainer = document.getElementById('alertContainer');
                this.passwordToggle = document.querySelector('.password-toggle');
                this.passwordToggleIcon = document.getElementById('passwordToggleIcon');
                
                this.init();
            }

            init() {
                this.bindEvents();
                this.emailInput.focus();
            }

            bindEvents() {
                // Form submission
                this.form.addEventListener('submit', (e) => this.handleSubmit(e));
                
                // Password toggle
                this.passwordToggle.addEventListener('click', () => this.togglePassword());
                
                // Real-time validation
                this.emailInput.addEventListener('blur', () => this.validateEmail());
                this.emailInput.addEventListener('input', () => this.clearValidationStyles(this.emailInput));
                
                this.passwordInput.addEventListener('input', () => {
                    this.validatePassword();
                    this.clearValidationStyles(this.passwordInput);
                });
                
                // Keyboard navigation
                this.emailInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.passwordInput.focus();
                    }
                });
                
                this.passwordInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.form.requestSubmit();
                    }
                });
                
                // Forgot password
                document.querySelector('.forgot-password').addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleForgotPassword();
                });
            }

            handleSubmit(e) {
                e.preventDefault();
                
                const email = this.emailInput.value.trim();
                const password = this.passwordInput.value;
                
                // Validation
                if (!this.validateForm(email, password)) return;
                
                this.setLoadingState(true);
                
                // Simulate API call
                setTimeout(() => {
                    if (password.length >= 6) {
                        this.showAlert('Connexion réussie ! Redirection...', 'success');
                        
                        setTimeout(() => {
                            const redirectUrl = email.includes('admin') ? 'dashboard.html' : 'membre-dashboard.html';
                            const message = email.includes('admin') ? 
                                'Redirection vers le dashboard administrateur' : 
                                'Redirection vers l\'espace membre';
                            
                            alert(message);
                            // window.location.href = redirectUrl;
                        }, 1500);
                    } else {
                        this.showAlert('Email ou mot de passe incorrect', 'danger');
                        this.setLoadingState(false);
                    }
                }, 1500);
            }

            validateForm(email, password) {
                let isValid = true;
                
                if (!email || !password) {
                    this.showAlert('Veuillez remplir tous les champs', 'danger');
                    return false;
                }
                
                if (!this.isValidEmail(email)) {
                    this.showAlert('Veuillez saisir une adresse email valide', 'danger');
                    this.emailInput.classList.add('error');
                    this.emailInput.focus();
                    return false;
                }
                
                return isValid;
            }

            validateEmail() {
                const email = this.emailInput.value.trim();
                if (email && !this.isValidEmail(email)) {
                    this.emailInput.classList.add('error');
                } else {
                    this.emailInput.classList.remove('error');
                }
            }

            validatePassword() {
                const password = this.passwordInput.value;
                if (password.length > 0 && password.length < 6) {
                    this.passwordInput.classList.add('warning');
                } else {
                    this.passwordInput.classList.remove('warning');
                }
            }

            clearValidationStyles(input) {
                input.classList.remove('error', 'warning');
            }

            isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            togglePassword() {
                const isPassword = this.passwordInput.type === 'password';
                
                this.passwordInput.type = isPassword ? 'text' : 'password';
                this.passwordToggleIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
            }

            setLoadingState(loading) {
                if (loading) {
                    this.loginBtn.disabled = true;
                    this.loginBtn.innerHTML = '<div class="loading-spinner"></div> Connexion en cours...';
                } else {
                    this.loginBtn.disabled = false;
                    this.loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
                }
            }

            handleForgotPassword() {
                const email = this.emailInput.value.trim();
                
                if (!email) {
                    this.showAlert('Veuillez saisir votre adresse email d\'abord', 'danger');
                    this.emailInput.focus();
                } else if (!this.isValidEmail(email)) {
                    this.showAlert('Veuillez saisir une adresse email valide', 'danger');
                    this.emailInput.focus();
                } else {
                    this.showAlert(`Instructions de réinitialisation envoyées à ${email}`, 'success');
                }
            }

            showAlert(message, type) {
                const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = `
                    <i class="fas ${iconClass}"></i>
                    <span>${message}</span>
                `;
                
                this.alertContainer.innerHTML = '';
                this.alertContainer.appendChild(alertDiv);
                
                // Auto remove after 4 seconds
                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    alertDiv.style.transform = 'translateY(-10px)';
                    setTimeout(() => alertDiv.remove(), 300);
                }, 4000);
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new LoginManager();
        });
   