<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Membres - Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin/members.css') }}">
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
                        <h2>Gestion des Membres</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <a href="{{ route('members.export', request()->query()) }}" class="action-btn-sm me-3" title="Exporter les données">
                            <i class="fas fa-download"></i> Exporter
                        </a>
                        <a href="{{ route('register') }}" class="action-btn-sm me-3" title="Nouveau membre">
                            <i class="fas fa-user-plus"></i> Nouveau
                        </a>
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">{{ $stats['pending'] }}</span>
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
            <div class="stats-card total">
                <div class="stats-header">
                    <div class="stats-icon total">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stats-number" id="total-number">{{ $stats['total'] }}</div>
                <div class="stats-label">Total membres</div>
                <div class="stats-trend {{ $stats['growth_percentage'] >= 0 ? 'trend-positive' : 'trend-negative' }}">
                    <i class="fas fa-{{ $stats['growth_percentage'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                    <span>{{ $stats['growth_percentage'] > 0 ? '+' : '' }}{{ $stats['growth_percentage'] }}% ce mois</span>
                </div>
            </div>
            
            <div class="stats-card active">
                <div class="stats-header">
                    <div class="stats-icon active">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="stats-number" id="active-number">{{ $stats['active'] }}</div>
                <div class="stats-label">Membres actifs</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-check"></i>
                    <span>{{ $stats['active_percentage'] }}% du total</span>
                </div>
            </div>
            
            <div class="stats-card new">
                <div class="stats-header">
                    <div class="stats-icon new">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
                <div class="stats-number" id="new-number">{{ $stats['inactive'] }}</div>
                <div class="stats-label">Membres inactifs</div>
                <div class="stats-trend {{ $stats['inactive'] > 0 ? 'trend-negative' : 'trend-positive' }}">
                    <i class="fas fa-{{ $stats['inactive'] > 0 ? 'exclamation-triangle' : 'check' }}"></i>
                    <span>{{ $stats['inactive'] > 0 ? 'Action requise' : 'Tout va bien' }}</span>
                </div>
            </div>
            
            <div class="stats-card pending">
                <div class="stats-header">
                    <div class="stats-icon pending">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stats-number" id="pending-number">{{ $stats['pending'] }}</div>
                <div class="stats-label">En attente validation</div>
                <div class="stats-trend trend-neutral">
                    <i class="fas fa-clock"></i>
                    <span>{{ $stats['pending'] > 0 ? 'Action requise' : 'À jour' }}</span>
                </div>
            </div>
        </div>

        <!-- Members List and Recent Activity -->
        <div class="section-grid loading">
            <div class="members-card">
                <div class="members-header">
                    <h5><i class="fas fa-users text-info"></i> Liste des membres</h5>
                </div>
                
                <!-- Filters Form -->
                <form method="GET" action="{{ route('dashboard.members') }}" class="filter-section">
                    <div class="filter-row">
                        <div>
                            <label class="form-label">Plan</label>
                            <select name="plan" class="form-select">
                                <option value="">Tous les plans</option>
                                <option value="hot-desk" {{ request('plan') == 'hot-desk' ? 'selected' : '' }}>Hot Desk</option>
                                <option value="bureau-dedie" {{ request('plan') == 'bureau-dedie' ? 'selected' : '' }}>Bureau Dédié</option>
                                <option value="bureau-prive" {{ request('plan') == 'bureau-prive' ? 'selected' : '' }}>Bureau Privé</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Type</label>
                            <select name="account_type" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="individual" {{ request('account_type') == 'individual' ? 'selected' : '' }}>Particulier</option>
                                <option value="company" {{ request('account_type') == 'company' ? 'selected' : '' }}>Entreprise</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Members Table -->
                <div class="table-responsive">
                    <table class="table" id="membersTable">
                        <thead>
                            <tr>
                                <th>Membre</th>
                                <th>Plan</th>
                                <th>Statut</th>
                                <th>Type</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $member)
                                <tr data-member-id="{{ $member->id }}">
                                    <td>
                                        <div class="member-info">
                                            <div class="member-avatar">
                                                {{ strtoupper(substr($member->display_name, 0, 2)) }}
                                            </div>
                                            <div class="member-details">
                                                <h6>{{ $member->display_name }}</h6>
                                                <small>{{ $member->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="plan-badge plan-{{ str_replace('-', '', $member->membership_plan) }}">
                                            {{ $member->membership_plan_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $member->is_active ? 'active' : ($member->email_verified_at ? 'inactive' : 'pending') }}">
                                            @if($member->is_active)
                                                Actif
                                            @elseif($member->email_verified_at)
                                                Inactif
                                            @else
                                                En attente
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $member->isIndividual() ? 'Particulier' : 'Entreprise' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $member->price_description }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="action-btn-sm view-member" data-member-id="{{ $member->id }}" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if(!$member->is_active && !$member->email_verified_at)
                                                <button class="action-btn-sm approve-member" data-member-id="{{ $member->id }}" title="Approuver">
                                                    <i class="fas fa-check" style="color: green;"></i>
                                                </button>
                                                <button class="action-btn-sm reject-member" data-member-id="{{ $member->id }}" title="Rejeter">
                                                    <i class="fas fa-times" style="color: red;"></i>
                                                </button>
                                            @else
                                                <button class="action-btn-sm edit-member" data-member-id="{{ $member->id }}" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn-sm toggle-status" data-member-id="{{ $member->id }}" title="{{ $member->is_active ? 'Désactiver' : 'Activer' }}">
                                                    <i class="fas fa-{{ $member->is_active ? 'user-times' : 'user-check' }}"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-users-slash fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">Aucun membre trouvé</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($members->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $members->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>

            <!-- Recent Activity & Pending Requests -->
            <div class="members-card">
                <div class="members-header">
                    <h5><i class="fas fa-history text-success"></i> Activité récente</h5>
                </div>
                
                @forelse($recentActivity as $activity)
                    <div class="activity-item">
                        <div class="item-content">
                            <div class="item-icon" style="background: rgba({{ $activity['color'] == 'success' ? '39, 174, 96' : ($activity['color'] == 'info' ? '52, 152, 219' : '255, 204, 1') }}, 0.1); color: var(--{{ $activity['color'] }}-color);">
                                <i class="{{ $activity['icon'] }}"></i>
                            </div>
                            <div class="item-text">
                                <h6>{{ $activity['message'] }}</h6>
                                <small>{{ $activity['details'] }}</small>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $activity['time'] }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Aucune activité récente</p>
                    </div>
                @endforelse

                @if(count($pendingRequests) > 0)
                    <div class="members-header mt-4">
                        <h5><i class="fas fa-user-clock text-warning"></i> Demandes en attente</h5>
                    </div>
                    
                    @foreach($pendingRequests as $request)
                        <div class="activity-item">
                            <div class="item-content">
                                <div class="item-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                                    <i class="{{ $request['icon'] }}"></i>
                                </div>
                                <div class="item-text">
                                    <h6>{{ $request['message'] }}</h6>
                                    <small>{{ $request['details'] }}</small>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $request['time'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Member Details Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>Détails du membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="memberModalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="editMemberBtn">Modifier</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier le membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMemberForm">
                    <div class="modal-body" id="editMemberModalBody">
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
    <script src="{{ asset('assets/js/admin/members.js') }}"></script>
    
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
        
        .btn-group .action-btn-sm {
            margin: 0 1px;
        }
    </style>
</body>
</html>