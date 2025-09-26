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

            animateValue(revenue, 0, 12450, 1500, '€');
            animateValue(members, 0, 89, 1200);
            animateValue(occupation, 0, 76, 1000, '%');
            animateValue(alerts, 0, 3, 800);
        }

        function animateValue(element, start, end, duration, suffix = '') {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const currentValue = Math.floor(progress * (end - start) + start);
                
                if (suffix === '€') {
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

        // Graphique des revenus
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep'],
                datasets: [{
                    label: 'Revenus (€)',
                    data: [8500, 9200, 8800, 10200, 11100, 10800, 12100, 11800, 12450],
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
                                return value.toLocaleString('fr-FR') + '€';
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

        // Graphique d'occupation
        const occupationCtx = document.getElementById('occupationChart').getContext('2d');
        const occupationChart = new Chart(occupationCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hot Desk', 'Bureau Dédié', 'Bureau Privé', 'Libre'],
                datasets: [{
                    data: [35, 25, 16, 24],
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

        // Actualisation automatique des données
        function refreshData() {
            const revenue = Math.floor(Math.random() * 1000) + 12000;
            const members = Math.floor(Math.random() * 10) + 85;
            const occupation = Math.floor(Math.random() * 20) + 70;
            
            document.getElementById('revenue-number').textContent = revenue.toLocaleString('fr-FR') + '€';
            document.getElementById('members-number').textContent = members;
            document.getElementById('occupation-number').textContent = occupation + '%';
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
            setInterval(refreshData, 30000);
        });
