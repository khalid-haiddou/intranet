        // Current date and calendar management
        let currentDate = new Date();
        let selectedDate = new Date();

        // Initialize calendar
        function initCalendar() {
            updateCalendarDisplay();
            generateCalendarDays();
        }

        function updateCalendarDisplay() {
            const monthNames = [
                'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
            ];
            document.getElementById('currentMonth').textContent = 
                `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
        }

        function generateCalendarDays() {
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';

            const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            const today = new Date();

            // Previous month days
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - (firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1));

            for (let i = 0; i < 42; i++) {
                const day = new Date(startDate);
                day.setDate(startDate.getDate() + i);

                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day.getDate();

                // Add classes
                if (day.getMonth() !== currentDate.getMonth()) {
                    dayElement.style.opacity = '0.3';
                }
                if (day.toDateString() === today.toDateString()) {
                    dayElement.classList.add('today');
                }
                if (day.toDateString() === selectedDate.toDateString()) {
                    dayElement.classList.add('selected');
                }

                // Mock: Add booking indicator for some days
                if (day.getDate() % 3 === 0 && day.getMonth() === currentDate.getMonth()) {
                    dayElement.classList.add('has-booking');
                }

                dayElement.addEventListener('click', () => selectDate(day));
                calendarDays.appendChild(dayElement);
            }
        }

        function selectDate(date) {
            selectedDate = new Date(date);
            generateCalendarDays();
            updateDailyReservations();
        }

        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            updateCalendarDisplay();
            generateCalendarDays();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            updateCalendarDisplay();
            generateCalendarDays();
        }

        function updateDailyReservations() {
            const container = document.getElementById('dailyReservations');
            const dateStr = selectedDate.toLocaleDateString('fr-FR');
            
            // Mock data based on selected date
            if (selectedDate.getDate() % 3 === 0) {
                container.innerHTML = `
                    <div class="reservation-item">
                        <div class="reservation-header">
                            <div>
                                <div class="reservation-title">Hot Desk - Zone A</div>
                                <div class="reservation-time">09:00 - 17:00</div>
                            </div>
                            <span class="status-badge status-confirmed">Confirmé</span>
                        </div>
                        <div class="reservation-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                Étage 1
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-euro-sign"></i>
                                150 MAD
                            </div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                        <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>Aucune réservation pour le ${dateStr}</p>
                    </div>
                `;
            }
        }

        // Filter functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterReservations(this.dataset.filter);
            });
        });

        function filterReservations(filter) {
            console.log('Filtering by:', filter);
            // Implementation for filtering reservations
        }

        function applyFilters() {
            const spaceType = document.getElementById('spaceType').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            console.log('Applying filters:', { spaceType, startDate, endDate });
            // Implementation for applying filters
        }

        // Action functions
        function showNewBooking() {
            alert('Ouverture du formulaire de nouvelle réservation');
        }

        function quickBook(type) {
            alert(`Réservation rapide: ${type}`);
        }

        function bookSpace(spaceId) {
            alert(`Réservation de l'espace: ${spaceId}`);
        }

        function modifyReservation(resId) {
            alert(`Modification de la réservation: ${resId}`);
        }

        function cancelReservation(resId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
                alert(`Réservation ${resId} annulée`);
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
            initCalendar();
            animateOnLoad();
        });
