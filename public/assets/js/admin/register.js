        // Set CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Account type selection
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update hidden field
                const type = this.dataset.type;
                document.getElementById('accountTypeField').value = type;
                
                // Show/hide appropriate form
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
                
                // Update hidden field
                const plan = this.dataset.plan;
                document.getElementById('membershipPlanField').value = plan;
                
                // Show price input for selected plan
                document.querySelectorAll('.price-input-container').forEach(container => {
                    container.classList.remove('active');
                });
                document.getElementById(`${plan}-price`).classList.add('active');
                
                // Show billing cycle options for ALL plans
                const billingCycle = document.getElementById('billingCycle');
                const cycleButtons = document.querySelectorAll('.cycle-btn');
                
                // Reset all cycle buttons
                cycleButtons.forEach(btn => btn.classList.remove('active'));
                
                // Always show billing cycle section for all plans
                billingCycle.style.display = 'block';
                
                // Set default billing cycle based on plan type
                let defaultCycle = 'daily'; // Default for all plans
                
                if (plan === 'hot-desk') {
                    defaultCycle = 'daily'; // Hot desk default: 1 jour
                } else if (plan === 'bureau-dedie') {
                    defaultCycle = 'weekly'; // Bureau dédié default: 1 semaine
                } else if (plan === 'bureau-prive') {
                    defaultCycle = 'monthly'; // Bureau privé default: 1 mois
                }
                
                // Set the default active cycle
                const defaultBtn = document.querySelector(`[data-cycle="${defaultCycle}"]`);
                if (defaultBtn) {
                    defaultBtn.classList.add('active');
                    document.getElementById('billingCycleField').value = defaultCycle;
                }
                
                // Update price label based on plan
                updatePriceLabel(plan);
            });
        });

        // Function to update price label based on selected plan
        function updatePriceLabel(plan) {
            const priceTexts = {
                'hot-desk': 'Prix pour la durée choisie (MAD)',
                'bureau-dedie': 'Prix pour la durée choisie (MAD)',
                'bureau-prive': 'Prix pour la durée choisie (MAD)'
            };
            
            const priceContainer = document.getElementById(`${plan}-price`);
            const priceText = priceContainer.querySelector('.form-text');
            if (priceText && priceTexts[plan]) {
                priceText.textContent = priceTexts[plan];
            }
        }

        // Billing cycle selection
        document.querySelectorAll('.cycle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cycle-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Update hidden field
                document.getElementById('billingCycleField').value = this.dataset.cycle;
            });
        });

        // Price input handling
        document.querySelectorAll('.price-input').forEach(input => {
            input.addEventListener('input', function() {
                document.getElementById('priceField').value = this.value;
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

        // Form submission handling
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Disable submit button to prevent double submission
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
            submitBtn.disabled = true;
            
            // Re-enable button after 3 seconds in case of error
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }, 3000);
        });

        // Restore form state from old input
        document.addEventListener('DOMContentLoaded', function() {
            // Restore account type selection
            const oldAccountType = '{{ old("account_type") }}';
            if (oldAccountType) {
                const accountTypeBtn = document.querySelector(`[data-type="${oldAccountType}"]`);
                if (accountTypeBtn) {
                    accountTypeBtn.click();
                }
            }
            
            // Restore membership plan selection
            const oldMembershipPlan = '{{ old("membership_plan") }}';
            if (oldMembershipPlan) {
                const planCard = document.querySelector(`[data-plan="${oldMembershipPlan}"]`);
                if (planCard) {
                    planCard.click();
                }
            }
            
            // Restore billing cycle selection - now works for all plans
            const oldBillingCycle = '{{ old("billing_cycle") }}';
            if (oldBillingCycle && oldMembershipPlan) {
                setTimeout(() => {
                    const cycleBtn = document.querySelector(`[data-cycle="${oldBillingCycle}"]`);
                    if (cycleBtn) {
                        cycleBtn.click();
                    }
                }, 100);
            }
            
            // Restore price
            const oldPrice = '{{ old("price") }}';
            if (oldPrice && oldMembershipPlan) {
                setTimeout(() => {
                    const priceInput = document.querySelector(`#${oldMembershipPlan}-price .price-input`);
                    if (priceInput) {
                        priceInput.value = oldPrice;
                        document.getElementById('priceField').value = oldPrice;
                    }
                }, 100);
            }
        });