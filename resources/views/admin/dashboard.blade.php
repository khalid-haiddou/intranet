<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <h2>Dashboard Admin</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="notification-btn me-3" id="refresh-btn" title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="alerts-badge">{{ $stats['total_alerts'] }}</span>
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
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stats-number" id="revenue-number" data-value="{{ $stats['revenue'] }}">
                    {{ number_format($stats['revenue'], 2) }} MAD
                </div>
                <div class="stats-label">Revenus du mois</div>
                <div class="stats-trend trend-{{ $stats['revenue_trend'] }}">
                    <i class="fas fa-trending-{{ $stats['revenue_trend'] === 'positive' ? 'up' : 'down' }}"></i>
                    <span id="revenue-growth">{{ $stats['revenue_growth'] > 0 ? '+' : '' }}{{ $stats['revenue_growth'] }}% vs mois dernier</span>
                </div>
            </div>
            
            <div class="stats-card members">
                <div class="stats-header">
                    <div class="stats-icon members">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stats-number" id="members-number" data-value="{{ $stats['active_members'] }}">
                    {{ $stats['active_members'] }}
                </div>
                <div class="stats-label">Membres actifs</div>
                <div class="stats-trend trend-{{ $stats['member_trend'] }}">
                    <i class="fas fa-user-plus"></i>
                    <span id="members-growth">+{{ $stats['new_members'] }} nouveaux membres</span>
                </div>
            </div>

            <div class="stats-card occupation">
                <div class="stats-header">
                    <div class="stats-icon occupation">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="stats-number" id="occupation-number" data-value="{{ $stats['occupation_rate'] }}">
                    {{ $stats['occupation_rate'] }}%
                </div>
                <div class="stats-label">Taux d'occupation</div>
                <div class="stats-trend trend-neutral">
                    <i class="fas fa-calendar"></i>
                    <span id="reservations-today">{{ $stats['today_reservations'] }} réservations aujourd'hui</span>
                </div>
            </div>
            
            <div class="stats-card alerts">
                <div class="stats-header">
                    <div class="stats-icon alerts">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stats-number" id="alerts-number" data-value="{{ $stats['total_alerts'] }}">
                    {{ $stats['total_alerts'] }}
                </div>
                <div class="stats-label">Alertes en attente</div>
                <div class="stats-trend trend-{{ $stats['total_alerts'] > 0 ? 'negative' : 'positive' }}">
                    <i class="fas fa-{{ $stats['total_alerts'] > 0 ? 'clock' : 'check-circle' }}"></i>
                    <span>{{ $stats['total_alerts'] > 0 ? 'Action requise' : 'Tout est OK' }}</span>
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
                <div id="alerts-container">
                    @forelse($alerts as $alert)
                    <div class="alert-item">
                        <div class="item-content">
                            <div class="item-icon" style="background: {{ $alert['icon_bg'] }}; color: {{ $alert['icon_color'] }};">
                                <i class="{{ $alert['icon'] }}"></i>
                            </div>
                            <div class="item-text">
                                <h6>{{ $alert['title'] }}</h6>
                                <small>{{ $alert['description'] }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Aucune alerte</p>
                    @endforelse
                </div>
            </div>

            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-history text-info"></i>
                    <h5>Activité récente</h5>
                </div>
                <div id="activity-container">
                    @forelse($recentActivity as $activity)
                    <div class="activity-item">
                        <div class="item-content">
                            <div class="item-icon" style="background: {{ $activity['icon_bg'] }}; color: {{ $activity['icon_color'] }};">
                                <i class="{{ $activity['icon'] }}"></i>
                            </div>
                            <div class="item-text">
                                <h6>{{ $activity['title'] }}</h6>
                                <small>{{ $activity['description'] }} - {{ $activity['time'] }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Aucune activité récente</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Data (hidden) -->
    <script type="application/json" id="chart-data">
        {!! json_encode($chartData) !!}
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/admin/dashboard.js') }}"></script>
</body>
</html>