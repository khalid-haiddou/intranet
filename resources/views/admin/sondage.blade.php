<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Sondages - Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin/sondage.css') }}">
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
                        <h2>Gestion des Sondages</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="action-btn-sm me-3" onclick="toggleCreator()">
                            <i class="fas fa-plus"></i> Nouveau sondage
                        </button>
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">{{ $stats['active_polls'] }}</span>
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
            <div class="stats-card polls">
                <div class="stats-header">
                    <div class="stats-icon polls">
                        <i class="fas fa-poll"></i>
                    </div>
                </div>
                <div class="stats-number" id="polls-number">{{ $stats['total_polls'] }}</div>
                <div class="stats-label">Sondages créés</div>
                <div class="stats-trend {{ $stats['new_polls_this_month'] > 0 ? 'trend-positive' : 'trend-neutral' }}">
                    <i class="fas fa-{{ $stats['new_polls_this_month'] > 0 ? 'plus' : 'equals' }}"></i>
                    <span>{{ $stats['new_polls_this_month'] > 0 ? '+' : '' }}{{ $stats['new_polls_this_month'] }} ce mois</span>
                </div>
            </div>
            
            <div class="stats-card responses">
                <div class="stats-header">
                    <div class="stats-icon responses">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stats-number" id="responses-number">{{ $stats['total_votes'] }}</div>
                <div class="stats-label">Réponses totales</div>
                <div class="stats-trend {{ $stats['votes_this_week'] > 0 ? 'trend-positive' : 'trend-neutral' }}">
                    <i class="fas fa-{{ $stats['votes_this_week'] > 0 ? 'trending-up' : 'minus' }}"></i>
                    <span>{{ $stats['votes_this_week'] > 0 ? '+' : '' }}{{ $stats['votes_this_week'] }} cette semaine</span>
                </div>
            </div>
            
            <div class="stats-card active">
                <div class="stats-header">
                    <div class="stats-icon active">
                        <i class="fas fa-play-circle"></i>
                    </div>
                </div>
                <div class="stats-number" id="active-number">{{ $stats['active_polls'] }}</div>
                <div class="stats-label">Sondages actifs</div>
                <div class="stats-trend trend-neutral">
                    <i class="fas fa-clock"></i>
                    <span>En cours</span>
                </div>
            </div>
            
            <div class="stats-card engagement">
                <div class="stats-header">
                    <div class="stats-icon engagement">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stats-number" id="engagement-number">{{ $stats['engagement_rate'] }}%</div>
                <div class="stats-label">Taux de participation</div>
                <div class="stats-trend {{ $stats['engagement_rate'] >= 50 ? 'trend-positive' : 'trend-negative' }}">
                    <i class="fas fa-{{ $stats['engagement_rate'] >= 50 ? 'thumbs-up' : 'thumbs-down' }}"></i>
                    <span>{{ $stats['engagement_rate'] >= 50 ? 'Bon' : 'À améliorer' }}</span>
                </div>
            </div>
        </div>

        <!-- Poll Creator and Templates -->
        <div class="section-grid loading" id="creatorSection" style="display: none;">
            <div class="polls-card">
                <div class="polls-header">
                    <h5><i class="fas fa-plus-circle text-success"></i> Créer un nouveau sondage</h5>
                    <button class="action-btn-sm" onclick="toggleCreator()">
                        <i class="fas fa-times"></i> Fermer
                    </button>
                </div>
                
                <form id="pollCreatorForm" class="poll-creator">
                    <div class="creator-header">
                        <i class="fas fa-edit text-primary"></i>
                        <h6>Nouveau Sondage</h6>
                    </div>
                    
                    <div class="form-group">
                        <label for="pollTitle">Titre du sondage *</label>
                        <input type="text" id="pollTitle" name="title" class="form-control" placeholder="Ex: Quel événement préférez-vous ?" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pollDescription">Description (optionnelle)</label>
                        <textarea id="pollDescription" name="description" class="form-textarea" placeholder="Ajoutez des détails sur votre sondage..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Options de réponse *</label>
                        <div class="options-container" id="optionsContainer">
                            <div class="option-item">
                                <div class="option-number">1</div>
                                <input type="text" class="option-input" name="options[]" placeholder="Première option" required>
                                <button type="button" class="remove-option" style="display: none;"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="option-item">
                                <div class="option-number">2</div>
                                <input type="text" class="option-input" name="options[]" placeholder="Deuxième option" required>
                                <button type="button" class="remove-option" style="display: none;"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <button type="button" class="add-option" id="addNewOption">
                            <i class="fas fa-plus"></i>
                            <span>Ajouter une option</span>
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pollDuration">Durée *</label>
                                <select id="pollDuration" name="duration_days" class="form-select" required>
                                    <option value="1">1 jour</option>
                                    <option value="3">3 jours</option>
                                    <option value="7" selected>1 semaine</option>
                                    <option value="14">2 semaines</option>
                                    <option value="30">1 mois</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pollVisibility">Visibilité *</label>
                                <select id="pollVisibility" name="visibility" class="form-select" required>
                                    <option value="all">Tous les membres</option>
                                    <option value="active">Membres actifs uniquement</option>
                                    <option value="plan">Par plan d'abonnement</option>
                                    <option value="custom">Sélection personnalisée</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allowMultiple" name="allow_multiple_choices">
                                    <label class="form-check-label" for="allowMultiple">
                                        Autoriser plusieurs choix
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="anonymousVoting" name="anonymous_voting">
                                    <label class="form-check-label" for="anonymousVoting">
                                        Vote anonyme
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="creator-actions">
                        <button type="submit" class="btn-primary" data-action="publish">
                            <i class="fas fa-rocket"></i>
                            Publier le sondage
                        </button>
                        <button type="submit" class="btn-secondary" data-action="draft">
                            <i class="fas fa-save"></i>
                            Sauvegarder en brouillon
                        </button>
                    </div>
                </form>
            </div>

            <div class="templates-card">
                <div class="polls-header">
                    <h5><i class="fas fa-templates text-warning"></i> Templates rapides</h5>
                </div>

                <div class="template-item" onclick="useTemplate('satisfaction')">
                    <div class="template-content">
                        <div class="template-info">
                            <h6>Satisfaction des services</h6>
                            <small>Évaluez la qualité de nos services</small>
                        </div>
                        <div class="template-icon" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>

                <div class="template-item" onclick="useTemplate('events')">
                    <div class="template-content">
                        <div class="template-info">
                            <h6>Événements souhaités</h6>
                            <small>Quels événements organiser ?</small>
                        </div>
                        <div class="template-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>

                <div class="template-item" onclick="useTemplate('improvements')">
                    <div class="template-content">
                        <div class="template-info">
                            <h6>Améliorations des espaces</h6>
                            <small>Que faut-il améliorer ?</small>
                        </div>
                        <div class="template-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>

                <div class="template-item" onclick="useTemplate('feedback')">
                    <div class="template-content">
                        <div class="template-info">
                            <h6>Feedback général</h6>
                            <small>Recueillir les avis membres</small>
                        </div>
                        <div class="template-icon" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Polls List -->
        <div class="polls-card loading">
            <div class="polls-header">
                <h5><i class="fas fa-list text-info"></i> Tous les sondages</h5>
                <div class="d-flex gap-2">
                    <button class="action-btn-sm" onclick="toggleCreator()">
                        <i class="fas fa-plus"></i> Nouveau
                    </button>
                </div>
            </div>
            
            <!-- Filters Form -->
            <form method="GET" action="{{ route('dashboard.polls') }}" class="filter-section">
                <div class="filter-row">
                    <div>
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                            <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Terminés</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillons</option>
                        </select>
                    </div>
                    <div>
                        <select name="period" class="form-select">
                            <option value="">Toutes les périodes</option>
                            <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                            <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                            <option value="3months" {{ request('period') == '3months' ? 'selected' : '' }}>3 derniers mois</option>
                        </select>
                    </div>
                    <div>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Rechercher un sondage..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Polls List -->
            <div id="pollsList">
                @forelse($polls as $poll)
                    <div class="poll-item" data-poll-id="{{ $poll->id }}">
                        <div class="poll-item-header">
                            <div>
                                <h6 class="poll-title">{{ $poll->title }}</h6>
                                <div class="poll-meta">
                                    Créé {{ $poll->created_at->diffForHumans() }}
                                    @if($poll->status === 'active')
                                        • Se termine {{ $poll->time_remaining }}
                                        • {{ $poll->total_votes }} participants sur {{ $stats['total_members'] }} membres ({{ $poll->participation_rate }}%)
                                    @elseif($poll->status === 'ended')
                                        • Terminé
                                        • {{ $poll->total_votes }} participants sur {{ $stats['total_members'] }} membres ({{ $poll->participation_rate }}%)
                                    @else
                                        • {{ $poll->description ?? 'Brouillon' }}
                                    @endif
                                </div>
                            </div>
                            <span class="poll-status status-{{ $poll->status === 'active' ? 'active' : ($poll->status === 'ended' ? 'ended' : 'draft') }}">
                                {{ $poll->status_label }}
                            </span>
                        </div>
                        
                        @if($poll->status !== 'draft')
                            <div class="poll-options-preview">
                                @foreach($poll->vote_results as $index => $result)
                                    <div class="option-preview">
                                        <span class="option-text">{{ $result['option'] }}</span>
                                        <div class="option-votes">
                                            <span>{{ $result['votes'] }} votes</span>
                                            <div class="vote-bar">
                                                <div class="vote-fill" style="width: {{ $result['percentage'] }}%; background: {{ ['var(--primary-color)', 'var(--info-color)', 'var(--success-color)', 'var(--warning-color)', 'var(--danger-color)'][$index % 5] }};"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="poll-actions">
                            <button class="action-btn-sm view-poll" data-poll-id="{{ $poll->id }}" title="Voir détails">
                                <i class="fas fa-eye"></i> Détails
                            </button>
                            
                            @if($poll->status === 'draft')
                                <button class="action-btn-sm publish-poll" data-poll-id="{{ $poll->id }}" title="Publier">
                                    <i class="fas fa-play"></i> Publier
                                </button>
                                <button class="action-btn-sm edit-poll" data-poll-id="{{ $poll->id }}" title="Modifier">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="action-btn-sm delete-poll" data-poll-id="{{ $poll->id }}" title="Supprimer">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            @elseif($poll->status === 'active')
                                <button class="action-btn-sm end-poll" data-poll-id="{{ $poll->id }}" title="Terminer">
                                    <i class="fas fa-stop"></i> Terminer
                                </button>
                            @else
                                <a href="{{ route('polls.export', $poll) }}" class="action-btn-sm" title="Exporter">
                                    <i class="fas fa-download"></i> Exporter
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-poll fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun sondage trouvé</h5>
                        <p class="text-muted">Créez votre premier sondage pour commencer</p>
                        <button class="btn-primary" onclick="toggleCreator()">
                            <i class="fas fa-plus"></i> Créer un sondage
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($polls->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $polls->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Poll Details Modal -->
    <div class="modal fade" id="pollModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-poll me-2"></i>Détails du sondage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pollModalBody">
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
    <script src="{{ asset('assets/js/admin/sondage.js') }}"></script>

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