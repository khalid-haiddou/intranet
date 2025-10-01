<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Événements - Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin/evenements.css') }}">
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
            <a href="/admin/dashboard" class="nav-link active">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="/admin/finances" class="nav-link">
                <i class="fas fa-wallet"></i> Finances
            </a>
            <a href="/dashboard/members" class="nav-link">
                <i class="fas fa-users"></i> Membres
            </a>
            <a href="/dashboard/espaces" class="nav-link">
                <i class="fas fa-door-open"></i> Espaces
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-check"></i> Réservations
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-comments"></i> Chat Interne
            </a>
            <a href="/dashboard/sondages" class="nav-link">
                <i class="fas fa-poll"></i> Sondages
            </a>
            <a href="/dashboard/evenements" class="nav-link">
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
                        <h2>Gestion des Événements</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">4</span>
                        </button>
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->display_name, 0, 2)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid loading">
            <div class="stats-card events">
                <div class="stats-header">
                    <div class="stats-icon events">
                        <i class="fas fa-calendar-star"></i>
                    </div>
                </div>
                <div class="stats-number" id="events-number">{{ $stats['total_events'] }}</div>
                <div class="stats-label">Événements créés</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-plus"></i>
                    <span>+{{ $stats['events_this_month'] ?? 5 }} ce mois</span>
                </div>
            </div>
            
            <div class="stats-card participants">
                <div class="stats-header">
                    <div class="stats-icon participants">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stats-number" id="participants-number">{{ $stats['total_participants'] }}</div>
                <div class="stats-label">Total participants</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-trending-up"></i>
                    <span>+68 cette semaine</span>
                </div>
            </div>
            
            <div class="stats-card upcoming">
                <div class="stats-header">
                    <div class="stats-icon upcoming">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stats-number" id="upcoming-number">{{ $stats['upcoming_events'] }}</div>
                <div class="stats-label">À venir</div>
                <div class="stats-trend trend-neutral">
                    <i class="fas fa-calendar-day"></i>
                    <span>Prochains 30 jours</span>
                </div>
            </div>
            
            <div class="stats-card satisfaction">
                <div class="stats-header">
                    <div class="stats-icon satisfaction">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stats-number" id="satisfaction-number">{{ number_format($stats['average_rating'], 1) }}</div>
                <div class="stats-label">Note moyenne</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-thumbs-up"></i>
                    <span>+0.3 vs mois dernier</span>
                </div>
            </div>
        </div>

        <!-- Event Creator and Calendar -->
        <div class="section-grid loading">
            <div class="events-card">
                <div class="events-header">
                    <h5><i class="fas fa-plus-circle text-success"></i> Créer un nouvel événement</h5>
                </div>
                
                <div class="event-creator" id="eventCreator">
                    <div class="creator-header">
                        <i class="fas fa-calendar-plus text-primary"></i>
                        <h6>Nouvel Événement</h6>
                    </div>
                    
                    <form id="eventForm">
                        @csrf
                        <div class="templates-grid">
                            <div class="template-btn" onclick="useEventTemplate('networking')">
                                <i class="fas fa-handshake template-icon" style="color: var(--info-color);"></i>
                                <div class="template-name">Networking</div>
                            </div>
                            <div class="template-btn" onclick="useEventTemplate('workshop')">
                                <i class="fas fa-tools template-icon" style="color: var(--success-color);"></i>
                                <div class="template-name">Workshop</div>
                            </div>
                            <div class="template-btn" onclick="useEventTemplate('conference')">
                                <i class="fas fa-microphone template-icon" style="color: #9B59B6;"></i>
                                <div class="template-name">Conférence</div>
                            </div>
                            <div class="template-btn" onclick="useEventTemplate('social')">
                                <i class="fas fa-coffee template-icon" style="color: var(--warning-color);"></i>
                                <div class="template-name">Social</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="eventTitle">Titre de l'événement *</label>
                                    <input type="text" id="eventTitle" name="title" class="form-control" placeholder="Ex: Networking Breakfast" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="eventType">Type *</label>
                                    <select id="eventType" name="type" class="form-select" required>
                                        @foreach(\App\Models\Event::getAvailableTypes() as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="eventDescription">Description *</label>
                            <textarea id="eventDescription" name="description" class="form-textarea" placeholder="Décrivez votre événement en détail..." required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="eventDate">Date *</label>
                                    <input type="date" id="eventDate" name="date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="eventTime">Heure *</label>
                                    <input type="time" id="eventTime" name="time" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="eventDuration">Durée (min) *</label>
                                    <input type="number" id="eventDuration" name="duration" class="form-control" placeholder="120" value="120" min="15" max="480" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="eventCapacity">Capacité *</label>
                                    <input type="number" id="eventCapacity" name="capacity" class="form-control" placeholder="20" value="20" min="1" max="500" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventLocation">Lieu *</label>
                                    <select id="eventLocation" name="location" class="form-select" required>
                                        @foreach(\App\Models\Event::getAvailableLocations() as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventPrice">Prix (MAD)</label>
                                    <input type="number" id="eventPrice" name="price" class="form-control" placeholder="0" value="0" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        
                        <div class="creator-actions">
                            <button type="submit" class="btn-primary" id="createEventBtn">
                                <i class="fas fa-rocket"></i>
                                <span>Publier l'événement</span>
                            </button>
                            <button type="button" class="btn-secondary" onclick="saveDraft()">
                                <i class="fas fa-save"></i>
                                Sauvegarder brouillon
                            </button>
                            <button type="button" class="btn-secondary" onclick="resetForm()">
                                <i class="fas fa-times"></i>
                                Réinitialiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="calendar-card">
                <div class="events-header">
                    <h5><i class="fas fa-calendar text-primary"></i> Calendrier des événements</h5>
                </div>
                
                <div class="calendar-nav">
                    <button onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                    <h6 id="currentMonth">{{ now()->translatedFormat('F Y') }}</h6>
                    <button onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
                
                <div class="calendar-grid" id="calendarGrid">
                    <div class="day-header">Lun</div>
                    <div class="day-header">Mar</div>
                    <div class="day-header">Mer</div>
                    <div class="day-header">Jeu</div>
                    <div class="day-header">Ven</div>
                    <div class="day-header">Sam</div>
                    <div class="day-header">Dim</div>
                    
                    <!-- Calendar days will be generated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Events List -->
        <div class="events-card loading">
            <div class="events-header">
                <h5><i class="fas fa-list text-info"></i> Tous les événements</h5>
                <div class="d-flex gap-2">
                    <button class="action-btn-sm" onclick="toggleCreator()">
                        <i class="fas fa-plus"></i> Nouveau
                    </button>
                    <button class="action-btn-sm" onclick="refreshEvents()">
                        <i class="fas fa-sync"></i> Actualiser
                    </button>
                </div>
            </div>
            
            <div class="filter-section">
                <div class="filter-row">
                    <div>
                        <select class="form-select" id="statusFilter">
                            <option value="">Tous les événements</option>
                            <option value="upcoming">À venir</option>
                            <option value="published">Publiés</option>
                            <option value="completed">Terminés</option>
                            <option value="cancelled">Annulés</option>
                        </select>
                    </div>
                    <div>
                        <select class="form-select" id="typeFilter">
                            <option value="">Tous les types</option>
                            @foreach(\App\Models\Event::getAvailableTypes() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" class="form-control" id="searchFilter" placeholder="Rechercher un événement...">
                    </div>
                </div>
            </div>

            <div id="eventsContainer">
                <!-- Events will be loaded here dynamically -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: var(--secondary-color);">
                <h5 class="modal-title" id="editEventModalLabel">
                    <i class="fas fa-edit me-2"></i>Modifier l'événement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEventForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editEventId" name="event_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="editEventTitle">Titre de l'événement *</label>
                                <input type="text" id="editEventTitle" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEventType">Type *</label>
                                <select id="editEventType" name="type" class="form-select" required>
                                    @foreach(\App\Models\Event::getAvailableTypes() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEventDescription">Description *</label>
                        <textarea id="editEventDescription" name="description" class="form-textarea" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="editEventDate">Date *</label>
                                <input type="date" id="editEventDate" name="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="editEventTime">Heure *</label>
                                <input type="time" id="editEventTime" name="time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="editEventDuration">Durée (min) *</label>
                                <input type="number" id="editEventDuration" name="duration" class="form-control" min="15" max="480" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="editEventCapacity">Capacité *</label>
                                <input type="number" id="editEventCapacity" name="capacity" class="form-control" min="1" max="500" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEventLocation">Lieu *</label>
                                <select id="editEventLocation" name="location" class="form-select" required>
                                    @foreach(\App\Models\Event::getAvailableLocations() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEventPrice">Prix (MAD)</label>
                                <input type="number" id="editEventPrice" name="price" class="form-control" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEventStatus">Statut</label>
                                <select id="editEventStatus" name="status" class="form-select">
                                    <option value="draft">Brouillon</option>
                                    <option value="published">Publié</option>
                                    <option value="cancelled">Annulé</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="button" class="btn-primary" id="updateEventBtn">
                    <i class="fas fa-save"></i> <span>Sauvegarder</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Event Details Modal -->
<div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--info-color), #5dade2); color: white;">
                <h5 class="modal-title" id="viewEventModalLabel">
                    <i class="fas fa-eye me-2"></i>Détails de l'événement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewEventContent">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Fermer
                </button>
                <button type="button" class="btn-primary" id="editFromViewBtn">
                    <i class="fas fa-edit"></i> Modifier
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- Success/Error Messages -->
    <div id="alertContainer"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        window.eventsData = {
            statsUrl: "{{ route('api.events.stats') }}",
            eventsUrl: "{{ route('api.events.index') }}",
            createUrl: "{{ route('api.events.store') }}",
            calendarUrl: "{{ route('api.events.calendar') }}",
            csrfToken: "{{ csrf_token() }}",
            currentUser: {
                id: {{ auth()->id() }},
                name: "{{ auth()->user()->display_name }}",
                initials: "{{ strtoupper(substr(auth()->user()->display_name, 0, 2)) }}"
            }
        };
    </script>
    <script src="{{ asset('assets/js/admin/evenements.js') }}"></script>
</body>
</html>