<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FFCC01;
            --primary-dark: #E6B800;
            --secondary-color: #2C3E50;
            --success-color: #27AE60;
            --warning-color: #F39C12;
            --danger-color: #E74C3C;
            --info-color: #3498DB;
            --light-bg: #F8F9FA;
            --dark-text: #2C3E50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #ffffff;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 204, 1, 0.12), rgba(255, 204, 1, 0.06));
            animation: float 8s ease-in-out infinite;
            box-shadow: 0 0 30px rgba(255, 204, 1, 0.15);
            border: 1px solid rgba(255, 204, 1, 0.1);
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg) scale(1); 
                opacity: 0.6;
            }
            50% { 
                transform: translateY(-40px) rotate(180deg) scale(1.2); 
                opacity: 0.9;
            }
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 8px 25px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 450px;
            width: 100%;
            position: relative;
            overflow: hidden;
            animation: slideUp 1s ease forwards;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--info-color), var(--success-color), var(--primary-color));
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--secondary-color);
            box-shadow: 
                0 8px 25px rgba(255, 204, 1, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .logo-icon:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 12px 30px rgba(255, 204, 1, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .logo-text h2 {
            color: var(--secondary-color);
            font-weight: 700;
            margin: 0;
            font-size: 2rem;
            letter-spacing: -1px;
        }

        .logo-text small {
            color: #7f8c8d;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .welcome-text h3 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1.6rem;
        }

        .welcome-text p {
            color: #7f8c8d;
            margin: 0;
            font-size: 1.1rem;
        }

        .login-form {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .form-control {
            background: rgba(248, 249, 250, 0.9);
            border: 2px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 18px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: var(--secondary-color);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .form-control:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 
                0 0 0 0.25rem rgba(255, 204, 1, 0.15),
                inset 0 2px 4px rgba(0, 0, 0, 0.04);
            outline: none;
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: #95a5a6;
            font-weight: 400;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #7f8c8d;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            background: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #ddd;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .form-check-input:checked {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--dark-text);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--secondary-color);
            border: none;
            padding: 20px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 
                0 8px 20px rgba(255, 204, 1, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 12px 30px rgba(255, 204, 1, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            color: var(--secondary-color);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 4px 15px rgba(255, 204, 1, 0.2);
        }

        .btn-login:disabled:hover {
            transform: none;
        }

        .register-link {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            color: #7f8c8d;
            font-size: 1rem;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .register-link a:hover {
            color: var(--primary-dark);
            background: rgba(255, 204, 1, 0.1);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            font-weight: 500;
            border-left: 4px solid;
            animation: slideInDown 0.5s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border-left-color: var(--success-color);
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border-left-color: var(--danger-color);
        }

        .alert-info {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info-color);
            border-left-color: var(--info-color);
        }

        /* Loading State */
        .btn-login.loading {
            position: relative;
            color: transparent;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Form Validation States */
        .form-control.is-invalid {
            border-color: var(--danger-color);
            background: rgba(231, 76, 60, 0.05);
        }

        .form-control.is-valid {
            border-color: var(--success-color);
            background: rgba(39, 174, 96, 0.05);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 5px;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .login-container {
                padding: 20px 15px;
            }

            .login-card {
                padding: 35px 25px;
                margin: 10px;
                border-radius: 20px;
            }

            .logo-container {
                flex-direction: column;
                gap: 12px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }

            .logo-text h2 {
                font-size: 1.75rem;
            }

            .welcome-text h3 {
                font-size: 1.4rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .btn-login {
                padding: 18px;
                font-size: 1rem;
            }
        }

        @media (max-width: 400px) {
            .login-card {
                padding: 30px 20px;
            }
            
            .form-control {
                padding: 16px 18px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-circle" style="width: 200px; height: 200px; top: 5%; left: -5%; animation-delay: 0s;"></div>
        <div class="floating-circle" style="width: 150px; height: 150px; top: 65%; right: -5%; animation-delay: 2s;"></div>
        <div class="floating-circle" style="width: 100px; height: 100px; top: 30%; left: 75%; animation-delay: 4s;"></div>
        <div class="floating-circle" style="width: 80px; height: 80px; top: 85%; left: 15%; animation-delay: 6s;"></div>
        <div class="floating-circle" style="width: 120px; height: 120px; top: 15%; right: 20%; animation-delay: 3s;"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="logo-text">
                        <h2>La Station</h2>
                        <small>Co-working Space</small>
                    </div>
                </div>
                <div class="welcome-text">
                    <h3>Bienvenue !</h3>
                    <p>Connectez-vous à votre espace de travail</p>
                </div>
            </div>

            <!-- Display Success/Error Messages -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->has('email'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ $errors->first('email') }}
                </div>
            @endif

            <!-- Alert Messages Container for JavaScript -->
            <div id="alertContainer"></div>

            <!-- Login Form -->
            <form class="login-form" id="loginForm" method="POST" action="{{ route('login.submit') }}" novalidate>
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i>
                        Adresse email
                    </label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="votre@email.com" 
                           required 
                           autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i>
                        Mot de passe
                    </label>
                    <div class="password-field">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••" 
                               required 
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" aria-label="Afficher/masquer le mot de passe">
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Se souvenir de moi
                        </label>
                    </div>
                    <a href="#" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>

            <!-- Register Link -->
            <div class="register-link">
                Vous n'avez pas encore de compte ? 
                <a href="/register" id="registerLink">Créer un compte</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get form elements
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const loginBtn = document.getElementById('loginBtn');
            const passwordToggle = document.querySelector('.password-toggle');
            const passwordToggleIcon = document.getElementById('passwordToggleIcon');
            const alertContainer = document.getElementById('alertContainer');

            // Set CSRF token for AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Initialize
            init();

            function init() {
                setupEventListeners();
                emailInput.focus();
            }

            function setupEventListeners() {
                // Form submission
                loginForm.addEventListener('submit', handleFormSubmit);
                
                // Password toggle
                passwordToggle.addEventListener('click', togglePassword);
                
                // Input validation on blur
                emailInput.addEventListener('blur', validateEmail);
                passwordInput.addEventListener('blur', validatePassword);
                
                // Clear validation on input
                emailInput.addEventListener('input', clearValidation);
                passwordInput.addEventListener('input', clearValidation);
            }

            async function handleFormSubmit(e) {
                e.preventDefault();
                
                if (!validateForm()) {
                    return;
                }

                const formData = new FormData(loginForm);
                const loginData = {
                    email: formData.get('email'),
                    password: formData.get('password'),
                    remember: formData.get('remember') ? 1 : 0,
                    _token: csrfToken
                };

                try {
                    setLoadingState(true);
                    clearAlerts();
                    
                    const response = await fetch('/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(loginData)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showAlert('success', data.message || 'Connexion réussie !');
                        
                        // Redirect based on user role
                        setTimeout(() => {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                // Fallback redirect logic
                                window.location.href = data.user?.role === 'admin' ? '/admin/dashboard' : '/dashboard';
                            }
                        }, 1000);

                    } else {
                        // Handle validation errors
                        if (data.errors) {
                            handleValidationErrors(data.errors);
                        } else {
                            showAlert('danger', data.message || 'Erreur de connexion');
                        }
                    }

                } catch (error) {
                    console.error('Login error:', error);
                    showAlert('danger', 'Erreur de connexion. Veuillez réessayer.');
                } finally {
                    setLoadingState(false);
                }
            }

            function validateForm() {
                let isValid = true;
                
                if (!validateEmail()) {
                    isValid = false;
                }
                
                if (!validatePassword()) {
                    isValid = false;
                }
                
                return isValid;
            }

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!email) {
                    setFieldError(emailInput, 'L\'adresse email est requise');
                    return false;
                }
                
                if (!emailRegex.test(email)) {
                    setFieldError(emailInput, 'Veuillez saisir une adresse email valide');
                    return false;
                }
                
                setFieldSuccess(emailInput);
                return true;
            }

            function validatePassword() {
                const password = passwordInput.value;
                
                if (!password) {
                    setFieldError(passwordInput, 'Le mot de passe est requis');
                    return false;
                }
                
                if (password.length < 6) {
                    setFieldError(passwordInput, 'Le mot de passe doit contenir au moins 6 caractères');
                    return false;
                }
                
                setFieldSuccess(passwordInput);
                return true;
            }

            function setFieldError(field, message) {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
                
                // Remove existing error message
                const existingError = field.parentNode.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }
                
                // Add error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = message;
                field.parentNode.appendChild(errorDiv);
            }

            function setFieldSuccess(field) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                
                // Remove error message
                const existingError = field.parentNode.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }
            }

            function clearValidation() {
                this.classList.remove('is-invalid', 'is-valid');
                const existingError = this.parentNode.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }
            }

            function handleValidationErrors(errors) {
                Object.keys(errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        setFieldError(input, errors[field][0]);
                    }
                });

                // Show general error message if no field-specific errors
                if (!errors.email && !errors.password) {
                    showAlert('danger', 'Veuillez vérifier vos informations de connexion');
                }
            }

            function togglePassword() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                
                // Toggle icon
                if (type === 'password') {
                    passwordToggleIcon.classList.remove('fa-eye-slash');
                    passwordToggleIcon.classList.add('fa-eye');
                    passwordToggle.setAttribute('aria-label', 'Afficher le mot de passe');
                } else {
                    passwordToggleIcon.classList.remove('fa-eye');
                    passwordToggleIcon.classList.add('fa-eye-slash');
                    passwordToggle.setAttribute('aria-label', 'Masquer le mot de passe');
                }
            }

            function setLoadingState(loading) {
                if (loading) {
                    loginBtn.disabled = true;
                    loginBtn.classList.add('loading');
                } else {
                    loginBtn.disabled = false;
                    loginBtn.classList.remove('loading');
                }
            }

            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = `
                    <i class="fas ${getAlertIcon(type)} me-2"></i>
                    ${message}
                `;
                
                alertContainer.appendChild(alertDiv);
                
                // Auto-remove success alerts after 5 seconds
                if (type === 'success') {
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                }
            }

            function getAlertIcon(type) {
                switch (type) {
                    case 'success': return 'fa-check-circle';
                    case 'danger': return 'fa-exclamation-circle';
                    case 'warning': return 'fa-exclamation-triangle';
                    case 'info': return 'fa-info-circle';
                    default: return 'fa-info-circle';
                }
            }

            function clearAlerts() {
                alertContainer.innerHTML = '';
            }
        });
    </script>
</body>
</html>