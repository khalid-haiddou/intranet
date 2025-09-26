<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/users/evenement.css') }}">
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
            <a href="#" class="nav-link">
                <i class="fas fa-credit-card"></i> Mon Abonnement
            </a>
            <a href="#" class="nav-link active">
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
                        <h2>Événements</h2>
                        <p>Découvrez les ateliers, conférences et événements de networking de La Station</p>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn-primary" onclick="createEvent()">
                        <i class="fas fa-plus"></i>
                        Créer un événement
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section loading">
            <div class="filter-tabs">
                <button class="tab-btn active" data-filter="all">
                    <i class="fas fa-list me-2"></i>Tous les événements
                </button>
                <button class="tab-btn" data-filter="upcoming">
                    <i class="fas fa-clock me-2"></i>À venir
                </button>
                <button class="tab-btn" data-filter="today">
                    <i class="fas fa-calendar-day me-2"></i>Aujourd'hui
                </button>
                <button class="tab-btn" data-filter="my-events">
                    <i class="fas fa-user me-2"></i>Mes événements
                </button>
                <button class="tab-btn" data-filter="past">
                    <i class="fas fa-history me-2"></i>Passés
                </button>
            </div>

            <div class="filter-controls">
                <div class="form-group">
                    <label>Catégorie</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">Toutes les catégories</option>
                        <option value="networking">Networking</option>
                        <option value="workshop">Atelier</option>
                        <option value="social">Social</option>
                        <option value="business">Business</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" id="dateFilter">
                </div>
                <div class="form-group">
                    <label>Recherche</label>
                    <input type="text" class="form-control" placeholder="Titre, organisateur..." id="searchFilter">
                </div>
                <div class="form-group">
                    <button class="btn-primary" onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                        Filtrer
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Layout -->
        <div class="content-layout loading">
            <!-- Events Grid -->
            <div class="events-grid">
                <!-- Featured Event -->
                <div class="event-card networking">
                    <div class="event-status status-today">Aujourd'hui</div>
                    <div class="event-header">
                        <div class="event-date">
                            <div class="event-day">18</div>
                            <div class="event-month">DÉC</div>
                        </div>
                        <div class="event-info">
                            <span class="event-category category-networking">Networking</span>
                            <h5 class="event-title">Networking Breakfast</h5>
                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="fas fa-clock"></i>
                                    8h30 - 10h00
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Espace Détente
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-users"></i>
                                    30 places
                                </div>
                            </div>
                            <p class="event-description">
                                Commencez votre journée en rencontrant d'autres entrepreneurs et professionnels. Petit-déjeuner offert, échanges informels et opportunités de collaboration.
                            </p>
                        </div>
                    </div>
                    <div class="event-footer">
                        <div class="event-attendees">
                            <div class="attendee-avatars">
                                <div class="attendee-avatar" style="background: var(--info-color);">SA</div>
                                <div class="attendee-avatar" style="background: var(--success-color);">KR</div>
                                <div class="attendee-avatar" style="background: var(--warning-color);">LM</div>
                                <div class="attendee-avatar" style="background: var(--danger-color);">+12</div>
                            </div>
                            <span class="attendee-count">15 participants inscrits</span>
                        </div>
                        <div class="event-actions">
                            <button class="btn-outline-primary registered">
                                <i class="fas fa-check"></i>
                                Inscrit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Workshop Event -->
                <div class="event-card workshop">
                    <div class="event-status status-upcoming">À venir</div>
                    <div class="event-header">
                        <div class="event-date">
                            <div class="event-day">20</div>
                            <div class="event-month">DÉC</div>
                        </div>
                        <div class="event-info">
                            <span class="event-category category-workshop">Atelier</span>
                            <h5 class="event-title">Workshop IA & Business</h5>
                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="fas fa-clock"></i>
                                    14h00 - 17h00
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Salle de Conférence
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-users"></i>
                                    20 places
                                </div>
                            </div>
                            <p class="event-description">
                                Découvrez comment intégrer l'Intelligence Artificielle dans votre stratégie business. Présentations, cas pratiques et sessions de brainstorming.
                            </p>
                        </div>
                    </div>
                    <div class="event-footer">
                        <div class="event-attendees">
                            <div class="attendee-avatars">
                                <div class="attendee-avatar" style="background: #9B59B6;">YB</div>
                                <div class="attendee-avatar" style="background: var(--info-color);">AS</div>
                                <div class="attendee-avatar" style="background: var(--success-color);">+6</div>
                            </div>
                            <span class="attendee-count">8 participants inscrits</span>
                        </div>
                        <div class="event-actions">
                            <button class="btn-outline-primary" onclick="registerEvent(this, 'workshop-ia')">
                                <i class="fas fa-plus"></i>
                                S'inscrire
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Social Event -->
                <div class="event-card social">
                    <div class="event-header">
                        <div class="event-date">
                            <div class="event-day">22</div>
                            <div class="event-month">DÉC</div>
                        </div>
                        <div class="event-info">
                            <span class="event-category category-social">Social</span>
                            <h5 class="event-title">After-work Friday</h5>
                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="fas fa-clock"></i>
                                    18h00 - 21h00
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Terrasse
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-users"></i>
                                    50 places
                                </div>
                            </div>
                            <p class="event-description">
                                Terminez la semaine en beauté ! Cocktails, musique et networking dans une ambiance détendue. Parfait pour créer des liens avec la communauté.
                            </p>
                        </div>
                    </div>
                    <div class="event-footer">
                        <div class="event-attendees">
                            <div class="attendee-avatars">
                                <div class="attendee-avatar" style="background: var(--warning-color);">MD</div>
                                <div class="attendee-avatar" style="background: var(--danger-color);">FZ</div>
                                <div class="attendee-avatar" style="background: var(--info-color);">OI</div>
                                <div class="attendee-avatar" style="background: var(--success-color);">+18</div>
                            </div>
                            <span class="attendee-count">21 participants inscrits</span>
                        </div>
                        <div class="event-actions">
                            <button class="btn-outline-primary" onclick="registerEvent(this, 'afterwork-friday')">
                                <i class="fas fa-plus"></i>
                                S'inscrire
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Business Event -->
                <div class="event-card business">
                    <div class="event-header">
                        <div class="event-date">
                            <div class="event-day">25</div>
                            <div class="event-month">DÉC</div>
                        </div>
                        <div class="event-info">
                            <span class="event-category category-business">Business</span>
                            <h5 class="event-title">Pitch Session Startups</h5>
                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="fas fa-clock"></i>
                                    16h00 - 19h00
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Auditorium
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-users"></i>
                                    40 places
                                </div>
                            </div>
                            <p class="event-description">
                                Session de pitch pour les startups membres. Présentez votre projet, obtenez des feedbacks et rencontrez des investisseurs potentiels.
                            </p>
                        </div>
                    </div>
                    <div class="event-footer">
                        <div class="event-attendees">
                            <div class="attendee-avatars">
                                <div class="attendee-avatar" style="background: #9B59B6;">LM</div>
                                <div class="attendee-avatar" style="background: var(--info-color);">+3</div>
                            </div>
                            <span class="attendee-count">4 participants inscrits</span>
                        </div>
                        <div class="event-actions">
                            <button class="btn-outline-primary" onclick="registerEvent(this, 'pitch-session')">
                                <i class="fas fa-plus"></i>
                                S'inscrire
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sidebar -->
            <div class="sidebar-content">
                <!-- My Events -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <i class="fas fa-user-calendar" style="color: var(--success-color);"></i>
                        <h6>Mes prochains événements</h6>
                    </div>
                    <div class="my-event-item">
                        <div class="my-event-time">AUJ 8h30</div>
                        <div class="my-event-title">Networking Breakfast</div>
                        <div class="my-event-location">Espace Détente</div>
                    </div>
                    <div class="my-event-item">
                        <div class="my-event-time">VEN 18h00</div>
                        <div class="my-event-title">After-work Friday</div>
                        <div class="my-event-location">Terrasse</div>
                    </div>
                </div>

                <!-- Popular Events -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <i class="fas fa-fire" style="color: var(--danger-color);"></i>
                        <h6>Événements populaires</h6>
                    </div>
                    <div class="popular-event" onclick="viewEvent('workshop-ia')">
                        <div class="popular-event-icon" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="popular-event-info">
                            <h6>Workshop IA & Business</h6>
                            <small>20 déc • 8 inscrits</small>
                        </div>
                    </div>
                    <div class="popular-event" onclick="viewEvent('pitch-session')">
                        <div class="popular-event-icon" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">
                            <i class="fas fa-microphone"></i>
                        </div>
                        <div class="popular-event-info">
                            <h6>Pitch Session Startups</h6>
                            <small>25 déc • 4 inscrits</small>
                        </div>
                    </div>
                    <div class="popular-event" onclick="viewEvent('book-club')">
                        <div class="popular-event-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="popular-event-info">
                            <h6>Club de Lecture Business</h6>
                            <small>27 déc • 12 inscrits</small>
                        </div>
                    </div>
                </div>

                <!-- Create Event Quick Access -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <i class="fas fa-plus-circle" style="color: var(--warning-color);"></i>
                        <h6>Actions rapides</h6>
                    </div>
                    <button class="btn-primary w-100 mb-3" onclick="createEvent()">
                        <i class="fas fa-plus"></i>
                        Créer un événement
                    </button>
                    <button class="btn-outline-primary w-100" onclick="suggestEvent()">
                        <i class="fas fa-lightbulb"></i>
                        Suggérer un événement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/users/evenement.js') }}"></script>
</body>
</html>