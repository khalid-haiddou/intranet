        // Initialize mini calendar
        function initMiniCalendar() {
            const calendarDays = document.getElementById('calendarDays');
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            
            const firstDay = new Date(currentYear, currentMonth, 1);
            const lastDay = new Date(currentYear, currentMonth + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
            
            calendarDays.innerHTML = '';
            
            // Empty cells for days before the first day
            for (let i = 0; i < startingDayOfWeek; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day';
                calendarDays.appendChild(emptyDay);
            }
            
            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;
                
                if (day === today.getDate() && currentMonth === today.getMonth()) {
                    dayElement.classList.add('today');
                }
                
                // Mock: Add event indicators for some days
                if ([18, 20, 22, 25, 27].includes(day)) {
                    dayElement.classList.add('has-event');
                }
                
                dayElement.addEventListener('click', () => selectDate(day));
                calendarDays.appendChild(dayElement);
            }
        }

        function selectDate(day) {
            console.log(`Selected date: ${day}`);
            // Filter events by selected date
        }

        // Filter functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterEvents(this.dataset.filter);
            });
        });

        function filterEvents(filter) {
            console.log('Filtering by:', filter);
            // Implementation for filtering events
        }

        function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const date = document.getElementById('dateFilter').value;
            const search = document.getElementById('searchFilter').value;
            
            console.log('Applying filters:', { category, date, search });
            // Implementation for applying filters
        }

        // Event actions
        function createEvent() {
            alert('Ouverture du formulaire de création d\'événement');
        }

        function suggestEvent() {
            alert('Formulaire de suggestion d\'événement');
        }

        function registerEvent(button, eventId) {
            const wasRegistered = button.classList.contains('registered');
            
            if (wasRegistered) {
                button.classList.remove('registered');
                button.innerHTML = '<i class="fas fa-plus"></i> S\'inscrire';
                alert(`Inscription annulée pour ${eventId}`);
            } else {
                button.classList.add('registered');
                button.innerHTML = '<i class="fas fa-check"></i> Inscrit';
                alert(`Inscription confirmée pour ${eventId}`);
            }
        }

        function viewEvent(eventId) {
            alert(`Afficher les détails de l'événement: ${eventId}`);
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
            initMiniCalendar();
        });
