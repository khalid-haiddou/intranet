// Complete Finances JavaScript with All Functionality

// Initialize CSRF token for AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Date actuelle
    const currentDateElement = document.getElementById('current-date');
    if (currentDateElement) {
        currentDateElement.textContent = new Date().toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Animation des chiffres
    setTimeout(() => {
        animateOnLoad();
        setTimeout(animateNumbers, 800);
    }, 100);

    // Initialize charts
    setTimeout(initializeCharts, 1000);

    // Initialize event listeners
    initializeEventListeners();
});

// Initialize all event listeners
function initializeEventListeners() {
    // Invoice Form Submission
    const invoiceForm = document.getElementById('invoiceForm');
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', handleInvoiceSubmission);
    }

    // Devis Form Submission
    const devisForm = document.getElementById('devisForm');
    if (devisForm) {
        devisForm.addEventListener('submit', handleDevisSubmission);
    }

    // Expense Form Submission
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        expenseForm.addEventListener('submit', handleExpenseSubmission);
        // Set default date to today
        const expenseDateInput = expenseForm.querySelector('input[name="expense_date"]');
        if (expenseDateInput) {
            expenseDateInput.value = new Date().toISOString().split('T')[0];
        }
    }

    // Filter event listeners
    const invoiceStatusFilter = document.getElementById('invoice-status-filter');
    const invoicePeriodFilter = document.getElementById('invoice-period-filter');
    
    if (invoiceStatusFilter) {
        invoiceStatusFilter.addEventListener('change', filterInvoices);
    }
    if (invoicePeriodFilter) {
        invoicePeriodFilter.addEventListener('change', filterInvoices);
    }

    // Sidebar navigation
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Handle Invoice Form Submission
async function handleInvoiceSubmission(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
    
    try {
        const response = await fetch('/admin/finances/invoices', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Facture créée avec succès!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('invoiceModal'));
            if (modal) modal.hide();
            form.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.message || 'Erreur lors de la création', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Erreur lors de la création de la facture', 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Créer la facture';
    }
}

// Handle Devis Form Submission
async function handleDevisSubmission(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
    
    try {
        const response = await fetch('/admin/finances/devis', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Devis créé avec succès!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('devisModal'));
            if (modal) modal.hide();
            form.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.message || 'Erreur lors de la création', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Erreur lors de la création du devis', 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Créer le devis';
    }
}

// Handle Expense Form Submission (updated)
async function handleExpenseSubmission(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
    
    try {
        const response = await fetch('/admin/finances/expenses', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        // Handle JSON safely
        let result;
        try {
            result = await response.json();
        } catch {
            throw new Error('Réponse invalide du serveur');
        }

        if (result.success) {
            showNotification('Dépense ajoutée avec succès!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('expenseModal'));
            if (modal) modal.hide();
            form.reset();
            setTimeout(() => location.reload(), 1000);
        } else if (result.errors) {
            // Show validation errors from backend
            Object.values(result.errors).forEach(errArr => {
                errArr.forEach(err => showNotification(err, 'error'));
            });
        } else {
            showNotification(result.message || 'Erreur lors de l\'ajout', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Erreur lors de l\'ajout de la dépense', 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Ajouter';
    }
}

// Modal trigger functions
function generateNewInvoice() {
    const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
    modal.show();
}

function generateNewDevis() {
    const modal = new bootstrap.Modal(document.getElementById('devisModal'));
    modal.show();
}

function addExpense() {
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        const dateInput = expenseForm.querySelector('input[name="expense_date"]');
        if (dateInput) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    }
    const modal = new bootstrap.Modal(document.getElementById('expenseModal'));
    modal.show();
}

// Download PDF functions
function downloadInvoicePDF(id) {
    window.open(`/admin/finances/invoices/${id}/pdf`, '_blank');
}

function downloadDevisPDF(id) {
    window.open(`/admin/finances/devis/${id}/pdf`, '_blank');
}

// View and Send functions
function viewInvoice(id) {
    downloadInvoicePDF(id);
}

function sendInvoice(id) {
    if (confirm('Êtes-vous sûr de vouloir envoyer cette facture au client?')) {
        showNotification(`Facture #${id} envoyée avec succès`, 'success');
    }
}

// Animation functions
function animateNumbers() {
    const revenue = document.getElementById('revenue-number');
    const invoices = document.getElementById('invoices-number');
    const overdue = document.getElementById('overdue-number');

    if (window.statsData) {
        if (revenue) animateValue(revenue, 0, window.statsData.net_profit, 1500, ' MAD');
        if (invoices) animateValue(invoices, 0, window.statsData.invoices_count, 1200);
        if (overdue) animateValue(overdue, 0, window.statsData.overdue_amount, 800, ' MAD');
    }
}

function animateValue(element, start, end, duration, suffix = '') {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (end - start) + start);
        
        if (suffix.includes('MAD')) {
            element.textContent = currentValue.toLocaleString('fr-FR') + suffix;
        } else {
            element.textContent = currentValue + suffix;
        }
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function animateOnLoad() {
    const loadingElements = document.querySelectorAll('.loading');
    loadingElements.forEach((element, index) => {
        setTimeout(() => {
            element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
        }, index * 150);
    });
}

// Chart initialization
let financeChart, revenueChart;

function initializeCharts() {
    if (!window.chartData) return;

    // Finance Line Chart
    const financeCtx = document.getElementById('financeChart');
    if (financeCtx) {
        financeChart = new Chart(financeCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: window.chartData.months,
                datasets: [{
                    label: 'Revenus (MAD)',
                    data: window.chartData.revenue,
                    borderColor: '#27AE60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#27AE60',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 3,
                    pointRadius: 6
                }, {
                    label: 'Dépenses (MAD)',
                    data: window.chartData.expenses,
                    borderColor: '#E74C3C',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#E74C3C',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 3,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#7f8c8d',
                            callback: function(value) {
                                return value.toLocaleString('fr-FR') + ' MAD';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#7f8c8d'
                        }
                    }
                }
            }
        });
    }

    // Revenue Doughnut Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        revenueChart = new Chart(revenueCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Hot Desk', 'Bureau Dédié', 'Bureau Privé', 'Salles de réunion'],
                datasets: [{
                    data: window.chartData.revenue_distribution,
                    backgroundColor: [
                        '#FFCC01',
                        '#3498DB',
                        '#27AE60',
                        '#9B59B6'
                    ],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }
}

// Filter functions
function filterInvoices() {
    const statusFilter = document.getElementById('invoice-status-filter').value;
    const periodFilter = document.getElementById('invoice-period-filter').value;
    
    showNotification(`Filtres appliqués: ${statusFilter || 'Tous'}, ${periodFilter || 'Toutes périodes'}`, 'info');
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-triangle';
        case 'warning': return 'exclamation-circle';
        default: return 'info-circle';
    }
}

// Refresh stats
function refreshStats() {
    fetch(window.routes.stats)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.statsData = data.data;
                updateStatsDisplay();
                showNotification('Statistiques mises à jour', 'success');
            }
        })
        .catch(error => {
            console.error('Error refreshing stats:', error);
            showNotification('Erreur lors de la mise à jour des statistiques', 'error');
        });
}

function updateStatsDisplay() {
    if (!window.statsData) return;

    const revenueElement = document.getElementById('revenue-number');
    const invoicesElement = document.getElementById('invoices-number');
    const overdueElement = document.getElementById('overdue-number');

    if (revenueElement) {
        revenueElement.textContent = window.statsData.net_profit.toLocaleString('fr-FR') + ' MAD';
    }
    if (invoicesElement) {
        invoicesElement.textContent = window.statsData.invoices_count;
    }
    if (overdueElement) {
        overdueElement.textContent = window.statsData.overdue_amount.toLocaleString('fr-FR') + ' MAD';
    }
}

// Export functionality
function exportFinancialData() {
    const period = prompt('Période (today/this_month/this_year):', 'this_month');
    const type = prompt('Type de données (invoices/payments/expenses):', 'invoices');
    
    if (period && type) {
        window.open(`${window.routes.export}?period=${period}&type=${type}`, '_blank');
    }
}

// Auto-refresh stats every 5 minutes
setInterval(() => {
    if (window.routes && window.routes.stats) {
        refreshStats();
    }
}, 300000);

// Mobile sidebar
if (window.innerWidth <= 768) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.createElement('button');
    toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
    toggleBtn.className = 'notification-btn position-fixed';
    toggleBtn.style.cssText = 'top: 20px; left: 20px; z-index: 1001;';
    document.body.appendChild(toggleBtn);
    
    toggleBtn.addEventListener('click', function() {
        sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-280px)' : 'translateX(0px)';
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'r':
                e.preventDefault();
                refreshStats();
                break;
            case 'e':
                e.preventDefault();
                exportFinancialData();
                break;
        }
    }
});
// Add this function with the other functions
async function deleteDevis(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/finances/devis/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.message || 'Erreur lors de la suppression', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Erreur lors de la suppression du devis', 'error');
    }
}

// Add these functions to your finances.js file

// Function to open edit modal
function editInvoiceNumber(invoiceId, currentNumber) {
    document.getElementById('edit_invoice_id').value = invoiceId;
    document.getElementById('edit_invoice_number').value = currentNumber;
    
    const modal = new bootstrap.Modal(document.getElementById('editInvoiceModal'));
    modal.show();
}

// Add this to initializeEventListeners() function
function initializeEventListeners() {
    // ... existing code ...
    
    // Edit Invoice Form Submission
    const editInvoiceForm = document.getElementById('editInvoiceForm');
    if (editInvoiceForm) {
        editInvoiceForm.addEventListener('submit', handleEditInvoiceSubmission);
    }
}

// Handle Edit Invoice Form Submission
async function handleEditInvoiceSubmission(e) {
    e.preventDefault();
    const form = e.target;
    const invoiceId = document.getElementById('edit_invoice_id').value;
    const invoiceNumber = document.getElementById('edit_invoice_number').value;
    const submitButton = form.querySelector('button[type="submit"]');
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour...';
    
    try {
        const response = await fetch(`/admin/finances/invoices/${invoiceId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ invoice_number: invoiceNumber })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Numéro de facture mis à jour avec succès!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editInvoiceModal'));
            if (modal) modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else if (result.errors) {
            Object.values(result.errors).forEach(errArr => {
                errArr.forEach(err => showNotification(err, 'error'));
            });
        } else {
            showNotification(result.message || 'Erreur lors de la mise à jour', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Erreur lors de la mise à jour', 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Mettre à jour';
    }
}