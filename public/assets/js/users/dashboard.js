        // Date actuelle
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
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

        // Animation des barres de progression
        function animateProgressBars() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
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

        // Gestion des notifications
        document.querySelector('.notification-btn').addEventListener('click', function() {
            alert('Notifications:\n• Nouvelle réservation confirmée\n• Événement Networking Breakfast rappel');
            const badge = this.querySelector('.notification-badge');
            badge.style.display = 'none';
        });

        // Actualisation automatique des données
        function refreshData() {
            // Simulation de mise à jour des stats
            const statsNumbers = document.querySelectorAll('.stats-number');
            statsNumbers.forEach(stat => {
                const currentValue = parseInt(stat.textContent);
                if (stat.textContent.includes('jours')) {
                    stat.textContent = Math.max(0, currentValue - 1) + ' jours';
                } else if (stat.textContent.includes('h')) {
                    stat.textContent = (currentValue + Math.floor(Math.random() * 5)) + 'h';
                }
            });
        }

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
            setTimeout(animateProgressBars, 800);
            setInterval(refreshData, 60000); // Mise à jour toutes les minutes
        });
