        // Initialize usage circle animation
        function initUsageCircle() {
            const circle = document.getElementById('usageCircle');
            const percentage = 75; // 75% usage
            const offset = 314 - (314 * percentage / 100);
            
            setTimeout(() => {
                circle.style.strokeDashoffset = offset;
            }, 500);
        }

        // Plan change functionality
        function changePlan(planType) {
            const planNames = {
                'hot-desk': 'Hot Desk',
                'bureau-prive': 'Bureau Privé'
            };
            
            if (confirm(`Êtes-vous sûr de vouloir changer vers le plan ${planNames[planType]} ?`)) {
                alert(`Demande de changement vers ${planNames[planType]} envoyée. Vous recevrez une confirmation par email.`);
            }
        }

        // Payment method update
        function updatePayment() {
            alert('Redirection vers la page de mise à jour des informations de paiement');
        }

        // Invoice download
        function downloadInvoice(period) {
            alert(`Téléchargement de la facture pour ${period}`);
        }

        // Subscription cancellation
        function cancelSubscription() {
            if (confirm('Êtes-vous sûr de vouloir annuler votre abonnement ? Cette action prendra effet à la fin de votre période de facturation actuelle.')) {
                alert('Demande d\'annulation envoyée. Vous recevrez un email de confirmation.');
            }
        }

        // Animation on load
        function animateOnLoad() {
            const loadingElements = document.querySelectorAll('.loading');
            loadingElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
                }, index * 150);
            });
        }

        // Sidebar navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Mobile menu
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.createElement('button');
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            toggleBtn.className = 'btn-primary position-fixed';
            toggleBtn.style.cssText = 'top: 20px; left: 20px; z-index: 1001; width: 50px; height: 50px; border-radius: 50%;';
            document.body.appendChild(toggleBtn);
            
            toggleBtn.addEventListener('click', function() {
                sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-280px)' : 'translateX(0px)';
            });
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            animateOnLoad();
            initUsageCircle();
        });
 