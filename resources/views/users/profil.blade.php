<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mon Compte - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/users/profil.css') }}">
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-circle" style="width: 200px; height: 200px; top: 10%; left: 5%; animation-delay: 0s;"></div>
        <div class="floating-circle" style="width: 150px; height: 150px; top: 60%; right: 8%; animation-delay: 2s;"></div>
        <div class="floating-circle" style="width: 100px; height: 100px; top: 40%; left: 70%; animation-delay: 4s;"></div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="logo-text">
                    <h3>La Station</h3>
                    <small>Co-working Space</small>
                </div>
            </div>
        </div>
        <nav class="sidebar-menu">
            <a href="#" class="nav-link">
                <i class="fas fa-home"></i> Mon Espace
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-check"></i> Réservations
            </a>
            <a href="{{ route('user.profil') }}" class="nav-link active">
                <i class="fas fa-user"></i> Mon Profil
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-credit-card"></i> Mon Abonnement
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-star"></i> Événements
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-comments"></i> Messages
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-headset"></i> Support
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header Section -->
        <div class="header-section loading">
            <div class="page-title">
                <h2>Mon Compte</h2>
                <p>Gérez vos informations personnelles et paramètres de compte</p>
            </div>
        </div>

        <!-- Profile Header -->
        <div class="profile-header loading">
            <div class="profile-info">
                <div class="profile-avatar">
                    @if($user->avatar)
                        <img src="{{ $user->avatar_url }}" alt="Avatar" class="avatar-img-photo" id="avatarImage">
                    @else
                        <div class="avatar-img" id="avatarInitials">{{ $user->initials }}</div>
                    @endif
                    <div class="avatar-upload" onclick="uploadAvatar()">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <div class="profile-details">
                    <h3>{{ $user->display_name }}</h3>
                    @if($user->profession)
                        <p class="profession-badge"><i class="fas fa-briefcase me-2"></i>{{ $user->profession }}</p>
                    @endif
                    <p><i class="fas fa-envelope me-2"></i>{{ $user->email }}</p>
                    <p><i class="fas fa-phone me-2"></i>{{ $user->phone ?? 'Non renseigné' }}</p>
                    <div class="profile-badges">
                        <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $user->is_active ? 'check-circle' : 'times-circle' }} me-1"></i>
                            {{ $user->is_active ? 'Compte Actif' : 'Compte Inactif' }}
                        </span>
                        <span class="badge badge-primary">
                            <i class="fas fa-crown me-1"></i>{{ $user->membership_plan_label }}
                        </span>
                        <span class="badge badge-info">
                            <i class="fas fa-calendar me-1"></i>Membre depuis {{ $user->created_at->year }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Stats -->
        <div class="form-section loading">
            <div class="section-header">
                <i class="fas fa-chart-bar" style="color: var(--info-color);"></i>
                <h5>Statistiques du compte</h5>
            </div>
            <div class="account-stats">
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['days_active'] }}</div>
                    <div class="stat-label">Jours actifs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['reservations'] }}</div>
                    <div class="stat-label">Réservations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['events'] }}</div>
                    <div class="stat-label">Événements</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['connections'] }}</div>
                    <div class="stat-label">Connexions</div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid loading">
            <!-- Personal Information -->
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-user-edit" style="color: var(--primary-color);"></i>
                    <h5>Informations personnelles</h5>
                </div>
                <form id="personalInfoForm">
                    @csrf
                    <div class="row">
                        @if($user->isIndividual())
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" class="form-control" value="{{ $user->prenom }}" name="prenom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control" value="{{ $user->nom }}" name="nom" required>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nom de l'entreprise</label>
                                    <input type="text" class="form-control" value="{{ $user->company_name }}" name="company_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Représentant légal</label>
                                    <input type="text" class="form-control" value="{{ $user->legal_representative }}" name="legal_representative" required>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" name="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" value="{{ $user->phone }}" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Profession / Titre</label>
                        <input type="text" class="form-control" value="{{ $user->profession }}" name="profession" 
                               placeholder="Ex: Designer UX, Développeur Web, Graphiste...">
                        <small class="form-text text-muted">Votre titre professionnel qui sera affiché sur votre profil</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <input type="text" class="form-control" value="{{ $user->address }}" name="address">
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Sauvegarder
                    </button>
                </form>
            </div>

            <!-- Security Settings -->
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-shield-alt" style="color: var(--success-color);"></i>
                    <h5>Sécurité</h5>
                </div>
                <form id="securityForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" name="current_password" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="new_password" id="newPassword" placeholder="••••••••" required>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthBar"></div>
                            </div>
                            <div class="strength-text" id="strengthText">Saisissez votre nouveau mot de passe</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" name="new_password_confirmation" id="confirmPassword" placeholder="••••••••" required>
                        <div class="form-text" id="passwordMatch"></div>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-key"></i>
                        Changer le mot de passe
                    </button>
                </form>
            </div>

            <!-- Notification Settings -->
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-bell" style="color: var(--warning-color);"></i>
                    <h5>Notifications</h5>
                </div>
                <div class="settings-item">
                    <div class="settings-info">
                        <h6>Notifications email</h6>
                        <small>Recevoir les notifications importantes par email</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked disabled>
                    </div>
                </div>
                <div class="settings-item">
                    <div class="settings-info">
                        <h6>Rappels de réservation</h6>
                        <small>Rappels 24h avant vos réservations</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="bookingReminders" checked disabled>
                    </div>
                </div>
                <div class="settings-item">
                    <div class="settings-info">
                        <h6>Événements communautaires</h6>
                        <small>Notifications pour les nouveaux événements</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="eventNotifications" disabled>
                    </div>
                </div>
                <div class="settings-item">
                    <div class="settings-info">
                        <h6>Newsletter mensuelle</h6>
                        <small>Actualités et conseils coworking</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="newsletter" 
                               {{ $user->newsletter ? 'checked' : '' }}
                               onchange="updateNotifications()">
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-file-alt" style="color: var(--info-color);"></i>
                    <h5>Mes documents</h5>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    La section documents sera disponible prochainement.
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="form-section loading">
            <div class="section-header">
                <i class="fas fa-exclamation-triangle" style="color: var(--danger-color);"></i>
                <h5>Zone de danger</h5>
            </div>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                Ces actions sont irréversibles. Assurez-vous de bien comprendre les conséquences avant de continuer.
            </div>
            <div class="settings-item">
                <div class="settings-info">
                    <h6>Désactiver temporairement le compte</h6>
                    <small>Votre compte sera suspendu et vos réservations annulées</small>
                </div>
                <button class="btn-outline-danger" onclick="deactivateAccount()">
                    <i class="fas fa-pause"></i>
                    Désactiver
                </button>
            </div>
            <div class="settings-item">
                <div class="settings-info">
                    <h6>Supprimer définitivement le compte</h6>
                    <small>Toutes vos données seront supprimées de façon permanente</small>
                </div>
                <button class="btn-outline-danger" onclick="deleteAccount()">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden file input for avatar upload -->
    <input type="file" id="avatarInput" accept="image/*" style="display: none;">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/users/profil.js') }}"></script>
</body>
</html>