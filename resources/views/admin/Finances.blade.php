<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <a href="/admin/dashboard" class="nav-link ">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="/admin/finances" class="nav-link active">
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
                        <h2>Gestion Financière</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">5</span>
                        </button>
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->display_name ?? 'MA', 0, 2)) }}
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
                <div class="stats-number" id="revenue-number">{{ number_format($stats['net_profit'], 0, ',', ' ') }} MAD</div>
                <div class="stats-label">Bénéfice Net (Revenus - Dépenses)</div>
                <div class="stats-trend {{ $stats['revenue_growth'] >= 0 ? 'trend-positive' : 'trend-negative' }}">
                    <i class="fas fa-{{ $stats['revenue_growth'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                    <span>{{ $stats['revenue_growth'] > 0 ? '+' : '' }}{{ $stats['revenue_growth'] }}% ce mois</span>
                </div>
            </div>
            
            <div class="stats-card invoices">
                <div class="stats-header">
                    <div class="stats-icon invoices">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
                <div class="stats-number" id="invoices-number">{{ $stats['invoices_count'] }}</div>
                <div class="stats-label">Factures émises</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-plus"></i>
                    <span>+{{ $stats['new_invoices_week'] }} cette semaine</span>
                </div>
            </div>
            
            <div class="stats-card overdue">
                <div class="stats-header">
                    <div class="stats-icon overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stats-number" id="overdue-number">{{ number_format($stats['overdue_amount'], 0, ',', ' ') }} MAD</div>
                <div class="stats-label">Paiements en retard</div>
                <div class="stats-trend trend-negative">
                    <i class="fas fa-exclamation"></i>
                    <span>{{ $stats['overdue_count'] }} client{{ $stats['overdue_count'] > 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section loading">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-line text-primary"></i> Évolution des revenus</h5>
                </div>
                <canvas id="financeChart"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-pie text-info"></i> Répartition des revenus</h5>
                </div>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Factures and Paiements Section -->
        <div class="section-grid loading">
            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-file-invoice text-info"></i> Factures récentes</h5>
                    
                </div>
                
                <div class="filter-section">
                    <div class="filter-row">
                        <div>
                            <label class="form-label">Statut</label>
                            <select class="form-select" id="invoice-status-filter">
                                <option>Tous les statuts</option>
                                <option value="paid">Payée</option>
                                <option value="pending">En attente</option>
                                <option value="overdue">En retard</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Période</label>
                            <select class="form-select" id="invoice-period-filter">
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
                            @forelse($recentInvoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice['invoice_number'] }}</strong></td>
                                <td>{{ $invoice['client_name'] }}</td>
                                <td><strong>{{ number_format($invoice['amount'], 0, ',', ' ') }} MAD</strong></td>
                                <td>
                                    <span class="status-badge status-{{ $invoice['status'] }}">
                                        {{ $invoice['status_label'] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="action-btn-sm" onclick="downloadInvoicePDF({{ $invoice['id'] }}); return false;" title="Télécharger PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="#" class="action-btn-sm" style="background: linear-gradient(135deg, #3498db, #2980b9);" onclick="editInvoiceNumber({{ $invoice['id'] }}, '{{ $invoice['invoice_number'] }}'); return false;" title="Modifier N°">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune facture récente</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-sync-alt" style="color: var(--primary-color);"></i> Abonnements actifs</h5>
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
                            @forelse($activeSubscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription['member_name'] }}</td>
                                <td>
                                    <span class="status-badge" style="background: rgba(52, 152, 219, 0.1); color: var(--info-color);">
                                        {{ $subscription['plan_label'] }}
                                    </span>
                                </td>
                                <td><strong>{{ number_format($subscription['amount'], 0, ',', ' ') }} MAD/{{ strtolower($subscription['billing_cycle']) }}</strong></td>
                                <td>{{ $subscription['next_billing_date'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucun abonnement actif</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>

        <!-- Abonnements and Dépenses Section -->
        <div class="section-grid loading">
            

            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-receipt text-warning"></i> Dépenses récentes</h5>
                    <a href="#" class="action-btn-sm" onclick="addExpense()">
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
                            @forelse($recentExpenses as $expense)
                            <tr>
                                <td>{{ $expense['date'] }}</td>
                                <td>{{ $expense['description'] }}</td>
                                <td>
                                    <span class="status-badge" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                                        {{ $expense['category'] }}
                                    </span>
                                </td>
                                <td><strong>{{ number_format($expense['amount'], 0, ',', ' ') }} MAD</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune dépense récente</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Add this right after the invoices finance-card -->
            <div class="finance-card">
                <div class="finance-header">
                    <h5><i class="fas fa-file-contract text-warning"></i> Devis récents</h5>
                    <a href="#" class="action-btn-sm" onclick="generateNewDevis(); return false;">
                        <i class="fas fa-plus"></i> Nouveau devis
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Validité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDevis as $devis)
                            <tr>
                                <td><strong>{{ $devis['devis_number'] }}</strong></td>
                                <td>{{ $devis['client_name'] }}</td>
                                <td><strong>{{ number_format($devis['amount'], 0, ',', ' ') }} MAD</strong></td>
                                <td>
                                    <span class="status-badge status-{{ $devis['status'] }}">
                                        {{ $devis['status_label'] }}
                                    </span>
                                </td>
                                <td><small>{{ $devis['valid_until'] }}</small></td>
                                <td>
                                    <a href="#" class="action-btn-sm" onclick="downloadDevisPDF({{ $devis['id'] }}); return false;" title="Télécharger PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="#" class="action-btn-sm bg-danger" onclick="deleteDevis({{ $devis['id'] }}); return false;" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun devis récent</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="invoiceForm">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Client *</label>
                                <select class="form-select" name="user_id" required>
                                    <option value="">Sélectionner un client</option>
                                    @foreach($users ?? [] as $user)
                                        <option value="{{ $user->id }}">{{ $user->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Montant HT *</label>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">TVA</label>
                                <input type="number" class="form-control" name="tax_amount" step="0.01" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Échéance (jours) *</label>
                                <input type="number" class="form-control" name="due_days" value="30" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer la facture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Devis Modal -->
    <div class="modal fade" id="devisModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Devis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="devisForm">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">N° Devis *</label>
                                <input type="text" class="form-control" name="devis_number" placeholder="Ex: D250930" required>
                                <small class="text-muted">Format suggéré: D + année + mois + jour + numéro</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom du Client *</label>
                                <input type="text" class="form-control" name="client_name" placeholder="Ex: David BenHaim" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant HT *</label>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">TVA</label>
                                <input type="number" class="form-control" name="tax_amount" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Validité (jours) *</label>
                            <input type="number" class="form-control" name="valid_days" value="30" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Conditions</label>
                            <textarea class="form-control" name="terms" rows="2" placeholder="Modalités de paiement, livraison, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer le devis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Expense Modal -->
    <div class="modal fade" id="expenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Dépense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="expenseForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Titre *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant *</label>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catégorie *</label>
                                <select class="form-select" name="category" required>
                                    <option value="rent">Loyer</option>
                                    <option value="utilities">Charges</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="supplies">Fournitures</option>
                                    <option value="equipment">Équipement</option>
                                    <option value="salaries">Salaires</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="insurance">Assurance</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fournisseur</label>
                                <input type="text" class="form-control" name="vendor">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" name="expense_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Invoice Number Modal -->
    <div class="modal fade" id="editInvoiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le numéro de facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editInvoiceForm">
                    <input type="hidden" id="edit_invoice_id" name="invoice_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Numéro de facture *</label>
                            <input type="text" class="form-control" id="edit_invoice_number" name="invoice_number" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script src="{{ asset('assets/js/admin/finances.js') }}"></script>
</body>
</html>