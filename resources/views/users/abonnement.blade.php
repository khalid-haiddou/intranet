<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Abonnement - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/users/abonnement.css') }}">
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
            <a href="#" class="nav-link">
                <i class="fas fa-user"></i> Mon Profil
            </a>
            <a href="#" class="nav-link active">
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
                <h2>Mon Abonnement</h2>
                <p>Gérez votre plan, consultez votre utilisation et historique de facturation</p>
            </div>
        </div>

        <!-- Subscription Overview -->
        <div class="subscription-overview loading">
            <div class="plan-header">
                <div class="plan-info">
                    <h3>Bureau Dédié</h3>
                    <p>Plan mensuel actif depuis mars 2023</p>
                </div>
                <div class="plan-status">
                    <i class="fas fa-check-circle"></i>
                    Actif
                </div>
            </div>
            <div class="subscription-details">
                <div class="detail-item">
                    <div class="detail-number">27</div>
                    <div class="detail-label">Jours restants</div>
                </div>
                <div class="detail-item">
                    <div class="detail-number">2,500</div>
                    <div class="detail-label">MAD/mois</div>
                </div>
                <div class="detail-item">
                    <div class="detail-number">15 Jan</div>
                    <div class="detail-label">Prochaine facture</div>
                </div>
                <div class="detail-item">
                    <div class="detail-number">Auto</div>
                    <div class="detail-label">Renouvellement</div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid loading">
            <!-- Usage Analytics -->
            <div class="analytics-section">
                <div class="section-header">
                    <i class="fas fa-chart-line" style="color: var(--info-color);"></i>
                    <h5>Utilisation ce mois</h5>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="progress-ring">
                            <svg width="120" height="120">
                                <circle class="bg" cx="60" cy="60" r="50"></circle>
                                <circle class="progress" cx="60" cy="60" r="50" id="usageCircle"></circle>
                            </svg>
                            <div class="progress-text">
                                <div class="progress-percentage">75%</div>
                                <div class="progress-label">Utilisation</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="usage-stats">
                            <div class="usage-item">
                                <div class="usage-number">142h</div>
                                <div class="usage-label">Temps passé</div>
                            </div>
                            <div class="usage-item">
                                <div class="usage-number">28</div>
                                <div class="usage-label">Jours utilisés</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-header mt-4">
                    <i class="fas fa-history" style="color: var(--success-color);"></i>
                    <h5>Historique des factures</h5>
                </div>

                <div class="invoice-item">
                    <div class="invoice-info">
                        <h6>Décembre 2024</h6>
                        <small>Facturé le 1 déc 2024 • Payé</small>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="invoice-amount">2,500 MAD</span>
                        <button class="download-btn" onclick="downloadInvoice('dec-2024')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>

                <div class="invoice-item">
                    <div class="invoice-info">
                        <h6>Novembre 2024</h6>
                        <small>Facturé le 1 nov 2024 • Payé</small>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="invoice-amount">2,500 MAD</span>
                        <button class="download-btn" onclick="downloadInvoice('nov-2024')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>

                <div class="invoice-item">
                    <div class="invoice-info">
                        <h6>Octobre 2024</h6>
                        <small>Facturé le 1 oct 2024 • Payé</small>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="invoice-amount">2,500 MAD</span>
                        <button class="download-btn" onclick="downloadInvoice('oct-2024')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="billing-section">
                <div class="section-header">
                    <i class="fas fa-credit-card" style="color: var(--warning-color);"></i>
                    <h5>Informations de facturation</h5>
                </div>

                <div class="billing-info">
                    <div class="billing-item">
                        <span class="billing-label">Plan actuel</span>
                        <span class="billing-value">Bureau Dédié</span>
                    </div>
                    <div class="billing-item">
                        <span class="billing-label">Tarif mensuel</span>
                        <span class="billing-value">2,500 MAD</span>
                    </div>
                    <div class="billing-item">
                        <span class="billing-label">Taxes (TVA 20%)</span>
                        <span class="billing-value">500 MAD</span>
                    </div>
                    <div class="billing-item">
                        <span class="billing-label">Total mensuel</span>
                        <span class="billing-value">3,000 MAD</span>
                    </div>
                </div>
                
                <div class="section-header mt-4">
                    <i class="fas fa-cog" style="color: var(--info-color);"></i>
                    <h5>Paramètres</h5>
                </div>

                <div class="billing-info">
                    <div class="billing-item">
                        <span class="billing-label">Renouvellement automatique</span>
                        <span class="billing-value" style="color: var(--success-color);">Activé</span>
                    </div>
                    <div class="billing-item">
                        <span class="billing-label">Prochaine facturation</span>
                        <span class="billing-value">15 janvier 2025</span>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-outline-danger" onclick="cancelSubscription()">
                        <i class="fas fa-times"></i>
                        Annuler l'abonnement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/users/abonnement.js') }}"></script>
</body>
</html>