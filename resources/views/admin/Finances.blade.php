<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Financière - Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin/Finances.css') }}">
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
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="#" class="nav-link active">
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
                        <h2>Gestion Financière</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">5</span>
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
                <div class="stats-number" id="revenue-number">67,850 MAD</div>
                <div class="stats-label">Revenus totaux</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-trending-up"></i>
                    <span>+15.2% ce mois</span>
                </div>
            </div>
            
            <div class="stats-card invoices">
                <div class="stats-header">
                    <div class="stats-icon invoices">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
                <div class="stats-number" id="invoices-number">24</div>
                <div class="stats-label">Factures émises</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-plus"></i>
                    <span>+3 cette semaine</span>
                </div>
            </div>
            
            <div class="stats-card overdue">
                <div class="stats-header">
                    <div class="stats-icon overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stats-number" id="overdue-number">2,350 MAD</div>
                <div class="stats-label">Paiements en retard</div>
                <div class="stats-trend trend-negative">
                    <i class="fas fa-exclamation"></i>
                    <span>3 clients</span>
                </div>
            </div>
        </div>
        <!-- Factures and Paiements Section -->
        <div class="section-grid loading">
            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-file-invoice text-info"></i> Factures récentes</h5>
                    <a href="#" class="action-btn-sm">
                        <i class="fas fa-plus"></i> Nouvelle facture
                    </a>
                </div>
                
                <div class="filter-section">
                    <div class="filter-row">
                        <div>
                            <label class="form-label">Statut</label>
                            <select class="form-select">
                                <option>Tous les statuts</option>
                                <option>Payée</option>
                                <option>En attente</option>
                                <option>En retard</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Période</label>
                            <select class="form-select">
                                <option>Ce mois</option>
                                <option>3 derniers mois</option>
                                <option>Cette année</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#INV-001</strong></td>
                                <td>TechCorp SARL</td>
                                <td><strong>2,500 MAD</strong></td>
                                <td><span class="status-badge status-paid">Payée</span></td>
                                <td>
                                    <a href="#" class="action-btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#INV-002</strong></td>
                                <td>Marie Dupont</td>
                                <td><strong>800 MAD</strong></td>
                                <td><span class="status-badge status-pending">En attente</span></td>
                                <td>
                                    <a href="#" class="action-btn-sm">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#INV-003</strong></td>
                                <td>Jean Martin</td>
                                <td><strong>1,200 MAD</strong></td>
                                <td><span class="status-badge status-overdue">En retard</span></td>
                                <td>
                                    <a href="#" class="action-btn-sm">
                                        <i class="fas fa-exclamation"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#INV-004</strong></td>
                                <td>StartupX</td>
                                <td><strong>3,200 MAD</strong></td>
                                <td><span class="status-badge status-paid">Payée</span></td>
                                <td>
                                    <a href="#" class="action-btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-credit-card text-success"></i> Paiements récents</h5>
                    <a href="#" class="action-btn-sm">
                        <i class="fas fa-plus"></i> Enregistrer paiement
                    </a>
                </div>

                <div class="filter-section">
                    <div class="filter-row">
                        <div>
                            <label class="form-label">Méthode</label>
                            <select class="form-select">
                                <option>Toutes les méthodes</option>
                                <option>CMI</option>
                                <option>Espèces</option>
                                <option>Virement</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Recherche</label>
                            <input type="text" class="form-control" placeholder="Nom du client...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>09/09/2025</td>
                                <td>TechCorp SARL</td>
                                <td><strong>2,500 MAD</strong></td>
                                <td>
                                    <div class="payment-method">
                                        <div class="method-icon method-cmi">CMI</div>
                                        <span>Carte bancaire</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>08/09/2025</td>
                                <td>StartupX</td>
                                <td><strong>3,200 MAD</strong></td>
                                <td>
                                    <div class="payment-method">
                                        <div class="method-icon method-bank">VIR</div>
                                        <span>Virement</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>07/09/2025</td>
                                <td>Ahmed Benali</td>
                                <td><strong>450 MAD</strong></td>
                                <td>
                                    <div class="payment-method">
                                        <div class="method-icon method-cash">€</div>
                                        <span>Espèces</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>06/09/2025</td>
                                <td>Sarah Cohen</td>
                                <td><strong>1,100 MAD</strong></td>
                                <td>
                                    <div class="payment-method">
                                        <div class="method-icon method-cmi">CMI</div>
                                        <span>Carte bancaire</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Abonnements and Dépenses Section -->
        <div class="section-grid loading">
            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-sync-alt" style="color: var(--primary-color);"></i> Abonnements actifs</h5>
                    <a href="#" class="action-btn-sm">
                        <i class="fas fa-cog"></i> Gérer
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Membre</th>
                                <th>Plan</th>
                                <th>Montant</th>
                                <th>Prochaine facture</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Marie Dupont</td>
                                <td><span class="status-badge" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">Hot Desk</span></td>
                                <td><strong>800 MAD/mois</strong></td>
                                <td>15/09/2025</td>
                            </tr>
                            <tr>
                                <td>TechCorp SARL</td>
                                <td><span class="status-badge" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">Bureau Privé</span></td>
                                <td><strong>2,500 MAD/mois</strong></td>
                                <td>20/09/2025</td>
                            </tr>
                            <tr>
                                <td>Jean Martin</td>
                                <td><span class="status-badge" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">Bureau Dédié</span></td>
                                <td><strong>1,200 MAD/mois</strong></td>
                                <td>25/09/2025</td>
                            </tr>
                            <tr>
                                <td>StartupX</td>
                                <td><span class="status-badge" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">Bureau Privé</span></td>
                                <td><strong>3,200 MAD/mois</strong></td>
                                <td>01/10/2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-receipt text-warning"></i> Dépenses récentes</h5>
                    <a href="#" class="action-btn-sm">
                        <i class="fas fa-plus"></i> Ajouter dépense
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Catégorie</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>09/09/2025</td>
                                <td>Électricité</td>
                                <td><span class="status-badge" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">Charges</span></td>
                                <td><strong>1,450 MAD</strong></td>
                            </tr>
                            <tr>
                                <td>08/09/2025</td>
                                <td>Café et fournitures</td>
                                <td><span class="status-badge" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">Fournitures</span></td>
                                <td><strong>680 MAD</strong></td>
                            </tr>
                            <tr>
                                <td>05/09/2025</td>
                                <td>Maintenance climatisation</td>
                                <td><span class="status-badge" style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color);">Maintenance</span></td>
                                <td><strong>2,200 MAD</strong></td>
                            </tr>
                            <tr>
                                <td>01/09/2025</td>
                                <td>Internet fibre</td>
                                <td><span class="status-badge" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">Charges</span></td>
                                <td><strong>890 MAD</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
   <script src="{{ asset('assets/js/admin/finances.js') }}"></script>
</body>
</html>