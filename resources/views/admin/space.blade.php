<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Espaces - Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin/space.css') }}">
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
            <a href="/admin/dashboard" class="nav-link ">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="/admin/finances" class="nav-link">
                <i class="fas fa-wallet"></i> Finances
            </a>
            <a href="/dashboard/members" class="nav-link">
                <i class="fas fa-users"></i> Membres
            </a>
            <a href="/dashboard/espaces" class="nav-link active">
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
                        <h2>Gestion des Espaces</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="action-btn-sm me-3" onclick="showCreateSpaceModal()">
                            <i class="fas fa-plus"></i> Nouvel espace
                        </button>
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">{{ $stats['urgent_maintenance'] }}</span>
                        </button>
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->display_name ?? 'Admin', 0, 2)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="stats-grid loading">
            <div class="stats-card spaces">
                <div class="stats-header">
                    <div class="stats-icon spaces">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="stats-number" id="spaces-number">{{ $stats['total_spaces'] }}</div>
                <div class="stats-label">Espaces totaux</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-info-circle"></i>
                    <span>{{ $stats['available_spaces'] }} disponibles</span>
                </div>
            </div>
            
            <div class="stats-card occupation">
                <div class="stats-header">
                    <div class="stats-icon occupation">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="stats-number" id="occupation-number">{{ $stats['occupancy_rate'] }}%</div>
                <div class="stats-label">Taux d'occupation</div>
                <div class="stats-trend {{ $stats['occupancy_rate'] >= 70 ? 'trend-positive' : 'trend-neutral' }}">
                    <i class="fas fa-{{ $stats['occupancy_rate'] >= 70 ? 'trending-up' : 'minus' }}"></i>
                    <span>{{ $stats['occupied_spaces'] + $stats['reserved_spaces'] }}/{{ $stats['total_spaces'] }} occupés</span>
                </div>
            </div>
            
            <div class="stats-card reservations">
                <div class="stats-header">
                    <div class="stats-icon reservations">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stats-number" id="reservations-number">{{ $stats['today_reservations'] }}</div>
                <div class="stats-label">Réservations aujourd'hui</div>
                <div class="stats-trend {{ $stats['reservations_trend'] >= 0 ? 'trend-positive' : 'trend-negative' }}">
                    <i class="fas fa-{{ $stats['reservations_trend'] >= 0 ? 'calendar-plus' : 'calendar-minus' }}"></i>
                    <span>{{ $stats['reservations_trend'] > 0 ? '+' : '' }}{{ $stats['reservations_trend'] }}% vs hier</span>
                </div>
            </div>
            
            <div class="stats-card maintenance">
                <div class="stats-header">
                    <div class="stats-icon maintenance">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
                <div class="stats-number" id="maintenance-number">{{ $stats['pending_maintenance'] }}</div>
                <div class="stats-label">Maintenance en attente</div>
                <div class="stats-trend {{ $stats['urgent_maintenance'] > 0 ? 'trend-negative' : 'trend-neutral' }}">
                    <i class="fas fa-{{ $stats['urgent_maintenance'] > 0 ? 'exclamation-triangle' : 'wrench' }}"></i>
                    <span>{{ $stats['urgent_maintenance'] > 0 ? $stats['urgent_maintenance'] . ' urgente(s)' : 'Sous contrôle' }}</span>
                </div>
            </div>
        </div>

        <!-- Spaces Overview and IoT Monitoring -->
        <div class="section-grid loading">
            <div class="spaces-card">
                <div class="spaces-header">
                    <h5><i class="fas fa-map text-info"></i> Vue d'ensemble des espaces</h5>
                </div>
                
                <!-- Filters Form -->
                <form method="GET" action="{{ route('dashboard.spaces') }}" class="filter-section">
                    <div class="filter-row">
                        <div>
                            <label class="form-label">Type d'espace</label>
                            <select name="type" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="office" {{ request('type') == 'office' ? 'selected' : '' }}>Bureau privé</option>
                                <option value="meeting_room" {{ request('type') == 'meeting_room' ? 'selected' : '' }}>Salle de réunion</option>
                                <option value="open_space" {{ request('type') == 'open_space' ? 'selected' : '' }}>Espace ouvert</option>
                                <option value="phone_booth" {{ request('type') == 'phone_booth' ? 'selected' : '' }}>Cabine téléphonique</option>
                                <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                                <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupé</option>
                                <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Réservé</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>
                        <div>
                            <div class="input-group">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                                <a href="{{ route('dashboard.spaces') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Spaces List -->
                <div id="spacesList">
                    @forelse($spaces as $space)
                        <div class="space-item" data-space-id="{{ $space->id }}">
                            <div class="space-info">
                                <div class="space-details">
                                    <h6>
                                        {{ $space->full_name }} 
                                        <span class="iot-indicator {{ $space->iot_status == 'online' ? 'iot-online' : 'iot-offline' }}"></span>
                                    </h6>
                                    <small>Capacité: {{ $space->capacity }} personnes • {{ $space->area ? $space->area . 'm²' : 'Surface non définie' }}</small>
                                </div>
                                <div class="space-status">
                                    <span class="status-badge status-{{ $space->status }}">{{ $space->status_label }}</span>
                                </div>
                            </div>
                            
                            @if($space->features)
                                <div class="space-features">
                                    @foreach(array_slice($space->features, 0, 4) as $feature)
                                        <span class="feature-tag">{{ $feature }}</span>
                                    @endforeach
                                    @if(count($space->features) > 4)
                                        <span class="feature-tag">+{{ count($space->features) - 4 }} autres</span>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="space-capacity">
                                <small>Occupation: {{ $space->current_occupancy }}/{{ $space->capacity }}</small>
                                <div class="capacity-bar">
                                    <div class="capacity-fill" style="width: {{ $space->occupancy_rate }}%; 
                                        background: {{ $space->occupancy_rate >= 90 ? 'var(--danger-color)' : ($space->occupancy_rate >= 70 ? 'var(--warning-color)' : 'var(--success-color)') }};"></div>
                                </div>
                                <small>{{ $space->occupancy_rate }}%</small>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button class="action-btn-sm view-space" data-space-id="{{ $space->id }}" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn-sm edit-space" data-space-id="{{ $space->id }}" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($space->status == 'available')
                                    <button class="action-btn-sm reserve-space" data-space-id="{{ $space->id }}" title="Réserver">
                                        <i class="fas fa-calendar-plus"></i>
                                    </button>
                                @elseif($space->status == 'occupied')
                                    <button class="action-btn-sm" title="Liste d'attente" disabled>
                                        <i class="fas fa-list"></i>
                                    </button>
                                @elseif($space->status == 'maintenance')
                                    <button class="action-btn-sm maintenance-space" data-space-id="{{ $space->id }}" title="Voir maintenance">
                                        <i class="fas fa-tools"></i>
                                    </button>
                                @endif
                                <button class="action-btn-sm schedule-maintenance" data-space-id="{{ $space->id }}" title="Programmer maintenance">
                                    <i class="fas fa-calendar-alt"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun espace trouvé</h5>
                            <p class="text-muted">Créez votre premier espace pour commencer</p>
                            <button class="btn-primary" onclick="showCreateSpaceModal()">
                                <i class="fas fa-plus"></i> Créer un espace
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($spaces->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $spaces->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>

            <div class="equipment-card">
                <div class="spaces-header">
                    <h5><i class="fas fa-cogs text-warning"></i> Équipements & IoT</h5>
                </div>

                <!-- Simulated IoT monitoring - you can replace with real data -->
                <div class="equipment-item">
                    <div class="equipment-info">
                        <div class="equipment-icon" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">
                            <i class="fas fa-thermometer-half"></i>
                        </div>
                        <div class="equipment-text">
                            <h6>Température</h6>
                            <small id="temperature-status">22°C • Optimal</small>
                        </div>
                    </div>
                    <div class="iot-indicator iot-online"></div>
                </div>

                <div class="equipment-item">
                    <div class="equipment-info">
                        <div class="equipment-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <div class="equipment-text">
                            <h6>Connexion WiFi</h6>
                            <small id="wifi-status">98% • Excellent signal</small>
                        </div>
                    </div>
                    <div class="iot-indicator iot-online"></div>
                </div>

                <div class="equipment-item">
                    <div class="equipment-info">
                        <div class="equipment-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="equipment-text">
                            <h6>Éclairage intelligent</h6>
                            <small id="lighting-status">85% • Ajustement auto</small>
                        </div>
                    </div>
                    <div class="iot-indicator iot-online"></div>
                </div>

                <div class="equipment-item">
                    <div class="equipment-info">
                        <div class="equipment-icon" style="background: {{ $stats['urgent_maintenance'] > 0 ? 'rgba(231, 76, 60, 0.1)' : 'rgba(39, 174, 96, 0.1)' }}; color: {{ $stats['urgent_maintenance'] > 0 ? 'var(--danger-color)' : 'var(--success-color)' }};">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="equipment-text">
                            <h6>Système de sécurité</h6>
                            <small>{{ $stats['urgent_maintenance'] > 0 ? 'Maintenance requise' : 'Fonctionnel' }}</small>
                        </div>
                    </div>
                    <div class="iot-indicator {{ $stats['urgent_maintenance'] > 0 ? 'iot-offline' : 'iot-online' }}"></div>
                </div>

                <div class="equipment-item">
                    <div class="equipment-info">
                        <div class="equipment-icon" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">
                            <i class="fas fa-tv"></i>
                        </div>
                        <div class="equipment-text">
                            <h6>Écrans interactifs</h6>
                            <small id="screens-status">{{ $stats['total_spaces'] - 3 }}/{{ $stats['total_spaces'] }} fonctionnels</small>
                        </div>
                    </div>
                    <div class="iot-indicator iot-online"></div>
                </div>

                <div class="equipment-item">
                    <div class="equipment-info">
                        <div class="equipment-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ECC71;">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="equipment-text">
                            <h6>Qualité de l'air</h6>
                            <small id="air-quality">Excellente • CO2: 380ppm</small>
                        </div>
                    </div>
                    <div class="iot-indicator iot-online"></div>
                </div>
            </div>
        </div>

        <!-- Maintenance Schedule -->
        <div class="spaces-card loading">
            <div class="spaces-header">
                <h5><i class="fas fa-calendar-alt text-warning"></i> Planning de maintenance</h5>
                <button class="action-btn-sm" onclick="showScheduleMaintenanceModal()">
                    <i class="fas fa-plus"></i> Programmer maintenance
                </button>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Espace</th>
                            <th>Type de maintenance</th>
                            <th>Date prévue</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceSchedule as $maintenance)
                            <tr data-maintenance-id="{{ $maintenance->id }}">
                                <td><strong>{{ $maintenance->space->full_name }}</strong></td>
                                <td>{{ $maintenance->type_label }}</td>
                                <td>{{ $maintenance->scheduled_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="status-badge" style="background: rgba({{ $maintenance->priority == 'urgent' ? '231, 76, 60' : ($maintenance->priority == 'high' ? '243, 156, 18' : '39, 174, 96') }}, 0.1); color: var(--{{ $maintenance->priority_color }}-color);">
                                        {{ $maintenance->priority_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $maintenance->status == 'in_progress' ? 'occupied' : 'pending' }}">
                                        {{ $maintenance->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn-sm view-maintenance" data-maintenance-id="{{ $maintenance->id }}" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($maintenance->status == 'scheduled')
                                        <button class="action-btn-sm start-maintenance" data-maintenance-id="{{ $maintenance->id }}" title="Démarrer">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    @elseif($maintenance->status == 'in_progress')
                                        <button class="action-btn-sm complete-maintenance" data-maintenance-id="{{ $maintenance->id }}" title="Terminer">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-calendar-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Aucune maintenance programmée</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Space Details Modal -->
    <div class="modal fade" id="spaceModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-door-open me-2"></i>Détails de l'espace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="spaceModalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Space Modal -->
    <div class="modal fade" id="createSpaceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nouvel espace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createSpaceForm">
                    <div class="modal-body" id="createSpaceModalBody">
                        <!-- Form content will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Traitement en cours...</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/admin/space.js') }}"></script>

    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
    </style>
</body>
</html>