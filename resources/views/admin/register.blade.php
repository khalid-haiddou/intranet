<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscription - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/register.css') }}">
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
    <script src="{{ asset('assets/js/admin/register.js') }}"></script>
</body>
</html>