<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/users/Reservations.css') }}">
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
            <a href="#" class="nav-link active">
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
                <div class="col-md-8">
                    <div class="page-title">
                        <h2>Mes Réservations</h2>
                        <p>Gérez vos réservations d'espaces de travail et salles de réunion</p>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn-primary" onclick="showNewBooking()">
                        <i class="fas fa-plus"></i>
                        Nouvelle réservation
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section loading">
            <div class="filter-tabs">
                <button class="tab-btn active" data-filter="all">
                    <i class="fas fa-list me-2"></i>Toutes
                </button>
                <button class="tab-btn" data-filter="upcoming">
                    <i class="fas fa-clock me-2"></i>À venir
                </button>
                <button class="tab-btn" data-filter="active">
                    <i class="fas fa-play me-2"></i>En cours
                </button>
                <button class="tab-btn" data-filter="completed">
                    <i class="fas fa-check me-2"></i>Terminées
                </button>
                <button class="tab-btn" data-filter="cancelled">
                    <i class="fas fa-times me-2"></i>Annulées
                </button>
            </div>

            <div class="filter-controls">
                <div class="form-group">
                    <label>Type d'espace</label>
                    <select class="form-select" id="spaceType">
                        <option value="">Tous les types</option>
                        <option value="hot-desk">Hot Desk</option>
                        <option value="bureau-dedie">Bureau Dédié</option>
                        <option value="bureau-prive">Bureau Privé</option>
                        <option value="salle-reunion">Salle de Réunion</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" class="form-control" id="startDate">
                </div>
                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" class="form-control" id="endDate">
                </div>
                <div class="form-group">
                    <button class="btn-primary" onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                        Rechercher
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid loading">
            <!-- Calendar Section -->
            <div class="calendar-section">
                <div class="calendar-header">
                    <h5 style="color: var(--secondary-color); font-weight: 600; margin: 0;">
                        <i class="fas fa-calendar-alt me-2" style="color: var(--primary-color);"></i>
                        Calendrier des réservations
                    </h5>
                    <div class="calendar-nav">
                        <button class="nav-btn" onclick="previousMonth()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="calendar-month" id="currentMonth">Décembre 2024</span>
                        <button class="nav-btn" onclick="nextMonth()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="calendar-grid">
                    <div class="calendar-day-header">Lun</div>
                    <div class="calendar-day-header">Mar</div>
                    <div class="calendar-day-header">Mer</div>
                    <div class="calendar-day-header">Jeu</div>
                    <div class="calendar-day-header">Ven</div>
                    <div class="calendar-day-header">Sam</div>
                    <div class="calendar-day-header">Dim</div>
                    
                    <!-- Calendar days will be generated by JavaScript -->
                    <div id="calendarDays"></div>
                </div>

                <!-- Reservations List -->
                <div class="mt-4">
                    <h6 style="color: var(--secondary-color); font-weight: 600; margin-bottom: 15px;">
                        Réservations du jour sélectionné
                    </h6>
                    <div id="dailyReservations">
                        <div class="reservation-item">
                            <div class="reservation-header">
                                <div>
                                    <div class="reservation-title">Hot Desk - Zone A</div>
                                    <div class="reservation-time">09:00 - 17:00</div>
                                </div>
                                <span class="status-badge status-confirmed">Confirmé</span>
                            </div>
                            <div class="reservation-details">
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Étage 1
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-euro-sign"></i>
                                    150 MAD
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservations List -->
            <div class="reservations-list">
                <div class="list-header">
                    <h5>
                        <i class="fas fa-list" style="color: var(--info-color);"></i>
                        Mes réservations
                    </h5>
                </div>

                <!-- Quick Book Section -->
                <div class="quick-book">
                    <h6><i class="fas fa-bolt me-2"></i>Réservation rapide</h6>
                    <div class="quick-actions">
                        <button class="quick-btn" onclick="quickBook('hot-desk')">
                            <i class="fas fa-laptop"></i>
                            Hot Desk - Aujourd'hui
                        </button>
                        <button class="quick-btn" onclick="quickBook('meeting')">
                            <i class="fas fa-users"></i>
                            Salle réunion - 2h
                        </button>
                        <button class="quick-btn" onclick="quickBook('private')">
                            <i class="fas fa-door-closed"></i>
                            Bureau privé - Demain
                        </button>
                    </div>
                </div>

                <!-- Available Spaces -->
                <h6 style="color: var(--secondary-color); font-weight: 600; margin-bottom: 15px;">
                    <i class="fas fa-clock me-2" style="color: var(--success-color);"></i>
                    Disponible maintenant
                </h6>
                
                <div class="available-spaces">
                    <div class="space-item" onclick="bookSpace('hot-desk-a')">
                        <div class="space-header">
                            <span class="space-name">Hot Desk A</span>
                            <span class="space-price">150 MAD/jour</span>
                        </div>
                        <div class="space-type">Espace partagé • WiFi • Café inclus</div>
                    </div>
                    
                    <div class="space-item" onclick="bookSpace('meeting-b')">
                        <div class="space-header">
                            <span class="space-name">Salle Réunion B</span>
                            <span class="space-price">80 MAD/h</span>
                        </div>
                        <div class="space-type">6 personnes • Écran • Tableau</div>
                    </div>
                    
                    <div class="space-item" onclick="bookSpace('bureau-5')">
                        <div class="space-header">
                            <span class="space-name">Bureau Dédié #5</span>
                            <span class="space-price">2500 MAD/mois</span>
                        </div>
                        <div class="space-type">Bureau privé • Rangement • 24h/24</div>
                    </div>
                </div>

                <!-- Upcoming Reservations -->
                <h6 style="color: var(--secondary-color); font-weight: 600; margin: 20px 0 15px 0;">
                    <i class="fas fa-calendar-check me-2" style="color: var(--warning-color);"></i>
                    Prochaines réservations
                </h6>

                <div class="reservation-item">
                    <div class="reservation-header">
                        <div>
                            <div class="reservation-title">Salle de Réunion B</div>
                            <div class="reservation-time">Demain, 14:00 - 16:00</div>
                        </div>
                        <span class="status-badge status-confirmed">Confirmé</span>
                    </div>
                    <div class="reservation-details">
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            6 personnes max
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-euro-sign"></i>
                            160 MAD
                        </div>
                    </div>
                    <div class="reservation-actions">
                        <button class="btn-sm btn-outline-primary" onclick="modifyReservation('res-2')">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button class="btn-sm btn-outline-danger" onclick="cancelReservation('res-2')">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </div>

                <div class="reservation-item">
                    <div class="reservation-header">
                        <div>
                            <div class="reservation-title">Bureau Dédié #12</div>
                            <div class="reservation-time">Vendredi, 08:00 - 18:00</div>
                        </div>
                        <span class="status-badge status-pending">En attente</span>
                    </div>
                    <div class="reservation-details">
                        <div class="detail-item">
                            <i class="fas fa-chair"></i>
                            Bureau individuel
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-euro-sign"></i>
                            300 MAD
                        </div>
                    </div>
                    <div class="reservation-actions">
                        <button class="btn-sm btn-outline-primary" onclick="modifyReservation('res-3')">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button class="btn-sm btn-outline-danger" onclick="cancelReservation('res-3')">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/users/Reservations.js') }}"></script>
</body>
</html>