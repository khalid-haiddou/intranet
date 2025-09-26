
        // Account type selection
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Show/hide appropriate form
                const type = this.dataset.type;
                document.querySelectorAll('.account-form').forEach(form => {
                    form.style.display = 'none';
                });
                
                if (type === 'individual') {
                    document.getElementById('individualForm').style.display = 'block';
                } else if (type === 'company') {
                    document.getElementById('companyForm').style.display = 'block';
                }
            });
        });

        // Membership plan selection
        document.querySelectorAll('.plan-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove active class from all cards
                document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked card
                this.classList.add('active');
                
                // Show price input for selected plan
                const plan = this.dataset.plan;
                document.querySelectorAll('.price-input-container').forEach(container => {
                    container.classList.remove('active');
                });
                document.getElementById(`${plan}-price`).classList.add('active');
                
                // Show billing cycle options for dedicated and private offices
                const billingCycle = document.getElementById('billingCycle');
                
                if (plan === 'bureau-dedie' || plan === 'bureau-prive') {
                    billingCycle.style.display = 'block';
                } else {
                    billingCycle.style.display = 'none';
                }
            });
        });

        // Billing cycle selection
        document.querySelectorAll('.cycle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cycle-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // Remove all strength classes
            strengthBar.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
            
            if (password.length === 0) {
                strengthText.textContent = 'Saisissez votre mot de passe';
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
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return Math.min(strength, 4);
        }

        // Password confirmation check
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordMatch = document.getElementById('passwordMatch');

        function checkPasswordMatch() {
            const password = passwordInput.value;
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

        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        // Form validation and submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if account type is selected
            const selectedType = document.querySelector('.type-btn.active');
            if (!selectedType) {
                alert('Veuillez sélectionner un type de compte');
                return;
            }
            
            // Check if membership plan is selected
            const selectedPlan = document.querySelector('.plan-card.active');
            if (!selectedPlan) {
                alert('Veuillez sélectionner un plan d\'abonnement');
                return;
            }
            
            // Check if price is entered for selected plan
            const planType = selectedPlan.dataset.plan;
            const priceInput = document.querySelector(`#${planType}-price input`);
            if (!priceInput.value || parseFloat(priceInput.value) <= 0) {
                alert('Veuillez saisir un tarif valide pour le plan sélectionné');
                priceInput.focus();
                return;
            }
            
            // Check password match
            if (passwordInput.value !== confirmPasswordInput.value) {
                alert('Les mots de passe ne correspondent pas');
                return;
            }
            
            // Check terms acceptance
            if (!document.getElementById('terms').checked) {
                alert('Veuillez accepter les conditions d\'utilisation');
                return;
            }
            
            // Simulate form submission
            const submitButton = document.querySelector('.btn-register');
            const originalText = submitButton.innerHTML;
            
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
            submitButton.disabled = true;
            
            setTimeout(() => {
                alert('Compte créé avec succès ! Un email de confirmation vous a été envoyé.');
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }, 2000);
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
 