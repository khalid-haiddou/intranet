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
            const invoices = document.getElementById('invoices-number');
            const pending = document.getElementById('pending-number');
            const overdue = document.getElementById('overdue-number');

            animateValue(revenue, 0, 67850, 1500, ' MAD');
            animateValue(invoices, 0, 24, 1200);
            animateValue(pending, 0, 8450, 1000, ' MAD');
            animateValue(overdue, 0, 2350, 800, ' MAD');
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

        // Graphique des finances
        const financeCtx = document.getElementById('financeChart').getContext('2d');
        const financeChart = new Chart(financeCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep'],
                datasets: [{
                    label: 'Revenus (MAD)',
                    data: [45000, 52000, 48000, 58000, 61000, 59000, 65000, 63000, 67850],
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
                    data: [15000, 18000, 16000, 19000, 17000, 20000, 18500, 19500, 21000],
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

        // Graphique des revenus
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hot Desk', 'Bureau Dédié', 'Bureau Privé', 'Salles de réunion', 'Événements'],
                datasets: [{
                    data: [30, 25, 35, 7, 3],
                    backgroundColor: [
                        '#FFCC01',
                        '#3498DB',
                        '#27AE60',
                        '#9B59B6',
                        '#F39C12'
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

        // Animation d'apparition des éléments
        function animateOnLoad() {
            const loadingElements = document.querySelectorAll('.loading');
            loadingElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
                }, index * 150);
            });
        }

        // Gestion des clics sur la sidebar
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
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
        });
  