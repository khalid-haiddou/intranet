<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin/dashboard.css') }}">
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
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-wallet"></i> Finances
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-users"></i> Membres
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-door-open"></i> Espaces
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-check"></i> Réservations
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-comments"></i> Chat Interne
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-poll"></i> Sondages
            </a>
            <a href="#" class="nav-link ">
                <i class="fas fa-calendar-star"></i> Événements
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-cog"></i> Déconnexion
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
                        <h2>Dashboard Admin</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <div class="user-avatar">
                            MA
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid loading">
            <div class="stats-card revenue">
                <div class="stats-header">
                    <div class="stats-icon revenue">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                </div>
                <div class="stats-number" id="revenue-number">12,450 MAD</div>
                <div class="stats-label">Revenus du mois</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-trending-up"></i>
                    <span>+12.5% vs mois dernier</span>
                </div>
            </div>
            
            <div class="stats-card members">
                <div class="stats-header">
                    <div class="stats-icon members">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stats-number" id="members-number">89</div>
                <div class="stats-label">Membres actifs</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-user-plus"></i>
                    <span>+5 nouveaux membres</span>
                </div>
            </div>
            <div class="stats-card alerts">
                <div class="stats-header">
                    <div class="stats-icon alerts">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stats-number" id="alerts-number">3</div>
                <div class="stats-label">Alertes en attente</div>
                <div class="stats-trend trend-negative">
                    <i class="fas fa-clock"></i>
                    <span>Action requise</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section loading">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-line text-success"></i> Évolution des revenus</h5>
                </div>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-pie" style="color: var(--primary-color);"></i> Répartition des espaces</h5>
                </div>
                <canvas id="occupationChart"></canvas>
            </div>
        </div>

        <!-- Information Section -->
        <div class="info-section loading">
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-exclamation-circle text-warning"></i>
                    <h5>Alertes prioritaires</h5>
                </div>
                <div class="alert-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color);">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="item-text">
                            <h6>3 paiements en retard</h6>
                            <small>Relances automatiques envoyées</small>
                        </div>
                    </div>
                </div>
                <div class="alert-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="item-text">
                            <h6>Maintenance programmée</h6>
                            <small>Salle A - Demain 14h00</small>
                        </div>
                    </div>
                </div>
                <div class="alert-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div class="item-text">
                            <h6>5 demandes d'adhésion</h6>
                            <small>En attente de validation</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-history text-info"></i>
                    <h5>Activité récente</h5>
                </div>
                <div class="activity-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="item-text">
                            <h6>Marie Dupont inscrite</h6>
                            <small>Plan Hot Desk - il y a 2h</small>
                        </div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="item-text">
                            <h6>Nouvelle réservation</h6>
                            <small>TechCorp - Salle B demain 10h</small>
                        </div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="item-content">
                        <div class="item-icon" style="background: rgba(255, 204, 1, 0.1); color: var(--primary-color);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="item-text">
                            <h6>Paiement reçu</h6>
                            <small>Jean Martin - 250€ - il y a 4h</small>
                        </div>
                    </div>
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
                    <i class="fas fa-user-plus"></i>
                    <span>Ajouter membre</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-file-invoice"></i>
                    <span>Créer facture</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Réservation</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-bullhorn"></i>
                    <span>Annonce</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Rapports</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/admin/dashboard.js') }}"></script>
</body>
</html>