<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscription - La Station Coworking</title>
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
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            background: rgba(255, 204, 1, 0.05);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 800px;
            width: 100%;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease forwards;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--info-color), var(--success-color));
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--secondary-color);
            box-shadow: 0 4px 15px rgba(255, 204, 1, 0.3);
        }

        .logo-text h2 {
            color: var(--secondary-color);
            font-weight: 700;
            margin: 0;
            font-size: 1.8rem;
        }

        .logo-text small {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .welcome-text h3 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: #7f8c8d;
            margin: 0;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .type-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .type-btn {
            background: rgba(248, 249, 250, 0.8);
            border: 2px solid rgba(0, 0, 0, 0.05);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .type-btn:hover {
            background: rgba(255, 204, 1, 0.05);
            border-color: rgba(255, 204, 1, 0.2);
        }

        .type-btn.active {
            background: rgba(255, 204, 1, 0.1);
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(255, 204, 1, 0.2);
        }

        .type-btn .type-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary-color);
            background: rgba(255, 204, 1, 0.1);
        }

        .type-btn h5 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .type-btn small {
            color: #7f8c8d;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            color: var(--secondary-color);
            font-weight: 500;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control, .form-select {
            background: rgba(248, 249, 250, 0.8);
            border: 2px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            padding: 15px 18px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: var(--secondary-color);
        }

        .form-control:focus, .form-select:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 204, 1, 0.15);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: var(--danger-color);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .membership-plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .plan-card {
            background: rgba(248, 249, 250, 0.8);
            border: 2px solid rgba(0, 0, 0, 0.05);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .plan-card:hover {
            background: rgba(255, 204, 1, 0.05);
            border-color: rgba(255, 204, 1, 0.2);
            transform: translateY(-3px);
        }

        .plan-card.active {
            background: rgba(255, 204, 1, 0.1);
            border-color: var(--primary-color);
            box-shadow: 0 6px 20px rgba(255, 204, 1, 0.2);
        }

        .plan-card .plan-icon {
            width: 45px;
            height: 45px;
            margin: 0 auto 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .plan-card.hot-desk .plan-icon {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info-color);
        }

        .plan-card.bureau-dedie .plan-icon {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .plan-card.bureau-prive .plan-icon {
            background: rgba(155, 89, 182, 0.1);
            color: #9B59B6;
        }

        .plan-card h6 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .plan-card .price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .plan-card small {
            color: #7f8c8d;
            line-height: 1.4;
        }

        .price-input-container {
            margin-top: 10px;
            display: none;
        }

        .price-input-container.active {
            display: block;
        }

        .price-input {
            text-align: center;
            font-weight: bold;
        }

        .billing-cycle {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .cycle-btn {
            background: rgba(248, 249, 250, 0.8);
            border: 2px solid rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            padding: 12px 15px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--secondary-color);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .cycle-btn:hover {
            background: rgba(255, 204, 1, 0.05);
            border-color: rgba(255, 204, 1, 0.2);
        }

        .cycle-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--secondary-color);
            font-weight: 600;
        }

        .submit-section {
            margin-top: 40px;
            text-align: center;
        }

        .btn-register {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--secondary-color);
            border: none;
            padding: 18px 40px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(255, 204, 1, 0.3);
            min-width: 200px;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 204, 1, 0.4);
            color: var(--secondary-color);
        }

        .btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            color: #7f8c8d;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-dark);
        }

        .required {
            color: var(--danger-color);
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .password-strength {
            margin-top: 8px;
        }

        .strength-bar {
            height: 4px;
            background: #ecf0f1;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: var(--danger-color); width: 25%; }
        .strength-fair { background: var(--warning-color); width: 50%; }
        .strength-good { background: var(--info-color); width: 75%; }
        .strength-strong { background: var(--success-color); width: 100%; }

        .strength-text {
            font-size: 0.8rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .register-card {
                padding: 25px;
                margin: 15px;
            }

            .type-buttons {
                grid-template-columns: 1fr;
            }

            .membership-plans {
                grid-template-columns: 1fr;
            }

            .billing-cycle {
                grid-template-columns: 1fr;
            }

            .logo-container {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 576px) {
            .register-container {
                padding: 15px;
            }

            .register-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-circle" style="width: 200px; height: 200px; top: 10%; left: 5%; animation-delay: 0s;"></div>
        <div class="floating-circle" style="width: 150px; height: 150px; top: 60%; right: 8%; animation-delay: 2s;"></div>
        <div class="floating-circle" style="width: 100px; height: 100px; top: 40%; left: 70%; animation-delay: 4s;"></div>
        <div class="floating-circle" style="width: 80px; height: 80px; top: 80%; left: 20%; animation-delay: 6s;"></div>
    </div>

    <div class="register-container">
        <div class="register-card">
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
                    <h3>Créer votre compte</h3>
                    <p>Rejoignez notre communauté de professionnels innovants</p>
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

            @if ($errors->has('registration'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ $errors->first('registration') }}
                </div>
            @endif

            <!-- Registration Form -->
            <form id="registerForm" method="POST" action="{{ route('register.submit') }}">
                @csrf
                
                <!-- Hidden fields for dynamic data -->
                <input type="hidden" name="account_type" id="accountTypeField">
                <input type="hidden" name="membership_plan" id="membershipPlanField">
                <input type="hidden" name="billing_cycle" id="billingCycleField">
                <input type="hidden" name="price" id="priceField">

                <!-- Account Type Selection -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user-tag text-primary"></i>
                        Type de compte
                    </div>
                    <div class="type-buttons">
                        <div class="type-btn" data-type="individual">
                            <div class="type-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Particulier</h5>
                            <small>Pour les freelances et entrepreneurs individuels</small>
                        </div>
                        <div class="type-btn" data-type="company">
                            <div class="type-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <h5>Entreprise</h5>
                            <small>Pour les sociétés et organisations</small>
                        </div>
                    </div>
                    @error('account_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Individual Form -->
                <div id="individualForm" class="account-form" style="display: none;">
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-id-card text-info"></i>
                            Informations personnelles
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Prénom <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                           name="prenom" value="{{ old('prenom') }}">
                                    @error('prenom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Nom <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                           name="nom" value="{{ old('nom') }}">
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-id-card-alt"></i>
                                CIN <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('cin') is-invalid @enderror" 
                                   name="cin" value="{{ old('cin') }}">
                            <div class="form-text">Numéro de carte d'identité nationale</div>
                            @error('cin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Company Form -->
                <div id="companyForm" class="account-form" style="display: none;">
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-building text-success"></i>
                            Informations entreprise
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-building"></i>
                                Nom de l'entreprise <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   name="company_name" value="{{ old('company_name') }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-certificate"></i>
                                        Registre de Commerce (RC) <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('rc') is-invalid @enderror" 
                                           name="rc" value="{{ old('rc') }}">
                                    @error('rc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-hashtag"></i>
                                        ICE <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('ice') is-invalid @enderror" 
                                           name="ice" value="{{ old('ice') }}">
                                    @error('ice')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tie"></i>
                                Nom du représentant légal <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('legal_representative') is-invalid @enderror" 
                                   name="legal_representative" value="{{ old('legal_representative') }}">
                            @error('legal_representative')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-address-book text-warning"></i>
                        Informations de contact
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email <span class="required">*</span>
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Téléphone <span class="required">*</span>
                                </label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Adresse
                        </label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  name="address" rows="3" placeholder="Adresse complète...">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Membership Plan -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-crown" style="color: var(--primary-color);"></i>
                        Plan d'abonnement
                    </div>
                    <div class="membership-plans">
                        <div class="plan-card hot-desk" data-plan="hot-desk">
                            <div class="plan-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <h6>Hot Desk</h6>
                            <div class="price">Prix négociable</div>
                            <small>Accès flexible aux espaces de travail partagés</small>
                            <div class="price-input-container" id="hot-desk-price">
                                <input type="number" class="form-control price-input" placeholder="Entrez le prix (MAD)" min="0" step="0.01">
                                <div class="form-text">Prix journalier en MAD</div>
                            </div>
                        </div>
                        <div class="plan-card bureau-dedie" data-plan="bureau-dedie">
                            <div class="plan-icon">
                                <i class="fas fa-chair"></i>
                            </div>
                            <h6>Bureau Dédié</h6>
                            <div class="price">Prix négociable</div>
                            <small>Votre propre bureau dans un espace partagé</small>
                            <div class="price-input-container" id="bureau-dedie-price">
                                <input type="number" class="form-control price-input" placeholder="Entrez le prix (MAD)" min="0" step="0.01">
                                <div class="form-text">Prix mensuel en MAD</div>
                            </div>
                        </div>
                        <div class="plan-card bureau-prive" data-plan="bureau-prive">
                            <div class="plan-icon">
                                <i class="fas fa-door-closed"></i>
                            </div>
                            <h6>Bureau Privé</h6>
                            <div class="price">Prix négociable</div>
                            <small>Bureau fermé privé avec toutes les commodités</small>
                            <div class="price-input-container" id="bureau-prive-price">
                                <input type="number" class="form-control price-input" placeholder="Entrez le prix (MAD)" min="0" step="0.01">
                                <div class="form-text">Prix mensuel en MAD</div>
                            </div>
                        </div>
                    </div>
                    @error('membership_plan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <!-- Billing Cycle -->
                    <div id="billingCycle" style="display: none;">
                        <label class="form-label mt-3">
                            <i class="fas fa-calendar-alt"></i>
                            Durée d'abonnement
                        </label>
                        <div class="billing-cycle">
                            <div class="cycle-btn" data-cycle="daily">
                                <i class="fas fa-sun"></i> 1 Jour
                            </div>
                            <div class="cycle-btn" data-cycle="weekly">
                                <i class="fas fa-calendar-week"></i> 1 Semaine
                            </div>
                            <div class="cycle-btn" data-cycle="biweekly">
                                <i class="fas fa-calendar-alt"></i> 2 Semaines
                            </div>
                            <div class="cycle-btn" data-cycle="monthly">
                                <i class="fas fa-calendar"></i> 1 Mois
                            </div>
                        </div>
                        @error('billing_cycle')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Password Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-lock text-danger"></i>
                        Sécurité du compte
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-key"></i>
                                    Mot de passe <span class="required">*</span>
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" id="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthBar"></div>
                                    </div>
                                    <div class="strength-text" id="strengthText">Saisissez votre mot de passe</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Confirmer le mot de passe <span class="required">*</span>
                                </label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       name="password_confirmation" id="confirmPassword">
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="passwordMatch"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="form-section">
                    <div class="form-check">
                        <input class="form-check-input @error('terms_accepted') is-invalid @enderror" 
                               type="checkbox" id="terms" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }}>
                        <label class="form-check-label" for="terms">
                            J'accepte les <a href="#" class="text-primary">conditions d'utilisation</a> et la <a href="#" class="text-primary">politique de confidentialité</a> <span class="required">*</span>
                        </label>
                        @error('terms_accepted')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" value="1" {{ old('newsletter') ? 'checked' : '' }}>
                        <label class="form-check-label" for="newsletter">
                            Je souhaite recevoir les actualités et offres promotionnelles
                        </label>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="submit-section">
                    <button type="submit" class="btn-register" id="submitBtn">
                        <i class="fas fa-user-plus"></i>
                        Créer mon compte
                    </button>
                    <div class="login-link">
                        Vous avez déjà un compte ? 
                        <a href="{{ route('login') }}">Se connecter</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>
</html>