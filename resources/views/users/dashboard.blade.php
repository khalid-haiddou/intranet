<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/users/dashboard.css') }}">
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
            <a href="#" class="nav-link active">
                <i class="fas fa-home"></i> Mon Espace
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-check"></i> Réservations
            </a>
            <a href="#" class="nav-link">
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
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="welcome-text">
                        <h2>Bonjour, Marie Dupont</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">2</span>
                        </button>
                        <div class="user-avatar">
                            MD
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid loading">
            <div class="stats-card subscription">
                <div class="stats-header">
                    <div class="stats-icon subscription">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                <div class="stats-number">27 jours</div>
                <div class="stats-label">Abonnement restant</div>
                <div class="stats-progress">
                    <div class="progress-bar subscription" style="width: 75%;"></div>
                </div>
            </div>
            
            <div class="stats-card usage">
                <div class="stats-header">
                    <div class="stats-icon usage">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stats-number">142h</div>
                <div class="stats-label">Temps passé ce mois</div>
                <div class="stats-progress">
                    <div class="progress-bar usage" style="width: 60%;"></div>
                </div>
            </div>

            <div class="stats-card reservations">
                <div class="stats-header">
                    <div class="stats-icon reservations">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stats-number">8</div>
                <div class="stats-label">Réservations actives</div>
                <div class="stats-progress">
                    <div class="progress-bar reservations" style="width: 80%;"></div>
                </div>
            </div>

            <div class="stats-card credits">
                <div class="stats-header">
                    <div class="stats-icon credits">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <div class="stats-number">15</div>
                <div class="stats-label">Crédits réunion</div>
                <div class="stats-progress">
                    <div class="progress-bar credits" style="width: 50%;"></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions loading">
            <div class="info-header">
                <i class="fas fa-bolt" style="color: var(--primary-color);"></i>
                <h5>Actions rapides</h5>
            </div>
            <div class="actions-grid">
                <a href="#" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Nouvelle réservation</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-door-open"></i>
                    <span>Check-in mobile</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-users"></i>
                    <span>Inviter un collègue</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-headset"></i>
                    <span>Contacter support</span>
                </a>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid loading">
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-calendar-check text-success"></i>
                    <h5>Mes prochaines réservations</h5>
                </div>
                <div class="reservation-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="item-text">
                            <h6>Hot Desk - Zone A</h6>
                            <small>Aujourd'hui, 9h00 - 17h00</small>
                        </div>
                        <span class="status-badge status-confirmed">Confirmé</span>
                    </div>
                </div>
                <div class="reservation-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="item-text">
                            <h6>Salle de réunion B</h6>
                            <small>Demain, 14h00 - 16h00</small>
                        </div>
                        <span class="status-badge status-confirmed">Confirmé</span>
                    </div>
                </div>
                <div class="reservation-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">
                            <i class="fas fa-chair"></i>
                        </div>
                        <div class="item-text">
                            <h6>Bureau Dédié #12</h6>
                            <small>Vendredi, 8h00 - 18h00</small>
                        </div>
                        <span class="status-badge status-pending">En attente</span>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-calendar-star text-warning"></i>
                    <h5>Événements à venir</h5>
                </div>
                <div class="event-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                            <i class="fas fa-coffee"></i>
                        </div>
                        <div class="item-text">
                            <h6>Networking Breakfast</h6>
                            <small>Lundi 8h30 - 10h00</small>
                        </div>
                    </div>
                </div>
                <div class="event-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="item-text">
                            <h6>Workshop Innovation</h6>
                            <small>Mercredi 14h00 - 17h00</small>
                        </div>
                    </div>
                </div>
                <div class="event-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color);">
                            <i class="fas fa-glass-cheers"></i>
                        </div>
                        <div class="item-text">
                            <h6>After-work Friday</h6>
                            <small>Vendredi 18h00 - 21h00</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/users/dashboard.js') }}"></script>
</body>
</html>