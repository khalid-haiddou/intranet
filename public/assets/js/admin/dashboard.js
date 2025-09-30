// Get CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Date actuelle
document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

// Animation des chiffres
function animateNumbers() {
    const revenue = document.getElementById('revenue-number');
    const members = document.getElementById('members-number');
    const occupation = document.getElementById('occupation-number');
    const alerts = document.getElementById('alerts-number');

    const revenueValue = parseFloat(revenue.dataset.value) || 0;
    const membersValue = parseInt(members.dataset.value) || 0;
    const occupationValue = parseFloat(occupation.dataset.value) || 0;
    const alertsValue = parseInt(alerts.dataset.value) || 0;

    animateValue(revenue, 0, revenueValue, 1500, ' MAD', true);
    animateValue(members, 0, membersValue, 1200, '', false);
    animateValue(occupation, 0, occupationValue, 1000, '%', false);
    animateValue(alerts, 0, alertsValue, 800, '', false);
}

function animateValue(element, start, end, duration, suffix = '', isCurrency = false) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = progress * (end - start) + start;
        
        if (isCurrency) {
            element.textContent = currentValue.toLocaleString('fr-FR', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            }) + suffix;
        } else if (suffix === '%') {
            element.textContent = Math.floor(currentValue) + suffix;
        } else {
            element.textContent = Math.floor(currentValue) + suffix;
        }
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Get chart data from hidden script tag
const chartDataElement = document.getElementById('chart-data');
const chartData = chartDataElement ? JSON.parse(chartDataElement.textContent) : null;

// Graphique des revenus
if (chartData && chartData.revenue) {
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: chartData.revenue.labels,
            datasets: [{
                label: 'Revenus (MAD)',
                data: chartData.revenue.data,
                borderColor: '#27AE60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#27AE60',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toLocaleString('fr-FR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' MAD';
                        }
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
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Graphique d'occupation
if (chartData && chartData.occupation) {
    const occupationCtx = document.getElementById('occupationChart').getContext('2d');
    const occupationChart = new Chart(occupationCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.occupation.labels,
            datasets: [{
                data: chartData.occupation.data,
                backgroundColor: [
                    '#FFCC01',
                    '#3498DB',
                    '#27AE60',
                    '#ECF0F1'
                ],
                borderWidth: 0,
                cutout: '75%'
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
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return label + ': ' + value + ' espace' + (value > 1 ? 's' : '');
                        }
                    }
                }
            }
        }
    });
}

// Animation d'apparition des éléments
function animateOnLoad() {
    const loadingElements = document.querySelectorAll('.loading');
    loadingElements.forEach((element, index) => {
        setTimeout(() => {
            element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
        }, index * 150);
    });
}

// Refresh button functionality
const refreshBtn = document.getElementById('refresh-btn');
if (refreshBtn) {
    refreshBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        icon.classList.add('fa-spin');
        
        fetch('/admin/dashboard/refresh', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats
                updateStats(data.data.stats);
                
                // Update alerts
                updateAlerts(data.data.alerts);
                
                // Update activity
                updateActivity(data.data.activity);
                
                // Show success message (optional)
                console.log('Dashboard actualisé avec succès');
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'actualisation:', error);
        })
        .finally(() => {
            icon.classList.remove('fa-spin');
        });
    });
}

// Update stats function
function updateStats(stats) {
    // Update revenue
    const revenueEl = document.getElementById('revenue-number');
    if (revenueEl) {
        revenueEl.dataset.value = stats.revenue;
        revenueEl.textContent = stats.revenue.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' MAD';
    }
    
    // Update members
    const membersEl = document.getElementById('members-number');
    if (membersEl) {
        membersEl.dataset.value = stats.active_members;
        membersEl.textContent = stats.active_members;
    }
    
    // Update occupation
    const occupationEl = document.getElementById('occupation-number');
    if (occupationEl) {
        occupationEl.dataset.value = stats.occupation_rate;
        occupationEl.textContent = stats.occupation_rate + '%';
    }
    
    // Update alerts
    const alertsEl = document.getElementById('alerts-number');
    const alertsBadge = document.getElementById('alerts-badge');
    if (alertsEl) {
        alertsEl.dataset.value = stats.total_alerts;
        alertsEl.textContent = stats.total_alerts;
    }
    if (alertsBadge) {
        alertsBadge.textContent = stats.total_alerts;
    }
    
    // Update growth indicators
    const revenueGrowth = document.getElementById('revenue-growth');
    if (revenueGrowth) {
        revenueGrowth.textContent = (stats.revenue_growth > 0 ? '+' : '') + stats.revenue_growth + '% vs mois dernier';
    }
    
    const membersGrowth = document.getElementById('members-growth');
    if (membersGrowth) {
        membersGrowth.textContent = '+' + stats.new_members + ' nouveaux membres';
    }
    
    const reservationsToday = document.getElementById('reservations-today');
    if (reservationsToday) {
        reservationsToday.textContent = stats.today_reservations + ' réservations aujourd\'hui';
    }
}

// Update alerts function
function updateAlerts(alerts) {
    const container = document.getElementById('alerts-container');
    if (!container) return;
    
    if (alerts.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-3">Aucune alerte</p>';
        return;
    }
    
    container.innerHTML = alerts.map(alert => `
        <div class="alert-item">
            <div class="item-content">
                <div class="item-icon" style="background: ${alert.icon_bg}; color: ${alert.icon_color};">
                    <i class="${alert.icon}"></i>
                </div>
                <div class="item-text">
                    <h6>${alert.title}</h6>
                    <small>${alert.description}</small>
                </div>
            </div>
        </div>
    `).join('');
}

// Update activity function
function updateActivity(activities) {
    const container = document.getElementById('activity-container');
    if (!container) return;
    
    if (activities.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-3">Aucune activité récente</p>';
        return;
    }
    
    container.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <div class="item-content">
                <div class="item-icon" style="background: ${activity.icon_bg}; color: ${activity.icon_color};">
                    <i class="${activity.icon}"></i>
                </div>
                <div class="item-text">
                    <h6>${activity.title}</h6>
                    <small>${activity.description} - ${activity.time}</small>
                </div>
            </div>
        </div>
    `).join('');
}

// Gestion des clics sur la sidebar
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.getAttribute('href') === '#') {
            e.preventDefault();
        }
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Menu mobile
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

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    animateOnLoad();
    setTimeout(animateNumbers, 800);
    
    // Auto-refresh every 5 minutes
    setInterval(() => {
        if (refreshBtn) {
            refreshBtn.click();
        }
    }, 300000);
});