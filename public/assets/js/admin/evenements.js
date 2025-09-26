// Global variables
let currentEvents = [];
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let currentEditingEventId = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Setup CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Set up Axios defaults
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
    
    // Set current date
    updateCurrentDate();
    
    // Set today's date for event date input
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('eventDate').value = today;
    
    // Load initial data
    loadEvents();
    loadCalendar();
    updateStats();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize animations
    animateOnLoad();
    setTimeout(animateNumbers, 800);
}

function setupEventListeners() {
    // Form submission
    const eventForm = document.getElementById('eventForm');
    if (eventForm) {
        eventForm.addEventListener('submit', handleEventFormSubmit);
    }
    
    // Edit form submission
    const updateBtn = document.getElementById('updateEventBtn');
    if (updateBtn) {
        updateBtn.addEventListener('click', handleEditEventSubmit);
    }
    
    // Edit from view button
    const editFromViewBtn = document.getElementById('editFromViewBtn');
    if (editFromViewBtn) {
        editFromViewBtn.addEventListener('click', function() {
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewEventModal'));
            viewModal.hide();
            setTimeout(() => {
                editEvent(currentEditingEventId);
            }, 300);
        });
    }
    
    // Filter listeners
    document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
    document.getElementById('typeFilter')?.addEventListener('change', applyFilters);
    document.getElementById('searchFilter')?.addEventListener('input', debounce(applyFilters, 500));
    
    // Sidebar navigation
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Event form submission
async function handleEventFormSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('createEventBtn');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Création...</span>';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(e.target);
        const eventData = Object.fromEntries(formData.entries());
        
        const response = await fetch(window.eventsData.createUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.eventsData.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(eventData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            resetForm();
            loadEvents();
            loadCalendar();
            updateStats();
        } else {
            showAlert('error', result.message || 'Erreur lors de la création de l\'événement');
        }
        
    } catch (error) {
        console.error('Error creating event:', error);
        showAlert('error', 'Erreur de connexion. Veuillez réessayer.');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Load events from API
async function loadEvents() {
    try {
        const params = new URLSearchParams();
        
        // Apply current filters
        const statusFilter = document.getElementById('statusFilter')?.value;
        const typeFilter = document.getElementById('typeFilter')?.value;
        const searchFilter = document.getElementById('searchFilter')?.value;
        
        if (statusFilter) params.append('status', statusFilter);
        if (typeFilter) params.append('type', typeFilter);
        if (searchFilter) params.append('search', searchFilter);
        
        const response = await fetch(`${window.eventsData.eventsUrl}?${params}`);
        const result = await response.json();
        
        if (result.success) {
            currentEvents = result.data;
            renderEvents(currentEvents);
        } else {
            showAlert('error', 'Erreur lors du chargement des événements');
        }
        
    } catch (error) {
        console.error('Error loading events:', error);
        showAlert('error', 'Erreur de connexion lors du chargement des événements');
    }
}

// Render events in the container
function renderEvents(events) {
    const container = document.getElementById('eventsContainer');
    
    if (!events || events.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-calendar-times text-muted" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h5 class="text-muted">Aucun événement trouvé</h5>
                <p class="text-muted">Créez votre premier événement pour commencer.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = events.map(event => renderEventItem(event)).join('');
}

// Render individual event item
function renderEventItem(event) {
    const participantsHtml = event.participants.map(participant => 
        `<div class="participant-avatar" style="background: linear-gradient(135deg, #${Math.floor(Math.random()*16777215).toString(16)}, #${Math.floor(Math.random()*16777215).toString(16)});">
            ${participant.initials}
        </div>`
    ).join('');
    
    const moreParticipants = Math.max(0, event.participants_count - event.participants.length);
    
    const ribbonHtml = getRibbonHtml(event);
    const actionsHtml = getEventActionsHtml(event);
    
    return `
        <div class="event-item" data-event-id="${event.id}">
            ${ribbonHtml}
            <div class="event-header">
                <div class="event-info">
                    <h6>${event.title}</h6>
                    <div class="event-meta">
                        <div class="event-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>${event.formatted_date}</span>
                        </div>
                        <div class="event-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>${event.formatted_time}</span>
                        </div>
                        <div class="event-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${event.location_label}</span>
                        </div>
                        <div class="event-meta-item">
                            <i class="fas fa-tag"></i>
                            <span>${event.price_format}</span>
                        </div>
                    </div>
                    <span class="event-type type-${event.type}">${event.type_label}</span>
                </div>
            </div>
            <div class="event-description">
                ${event.description}
            </div>
            <div class="event-stats">
                <div class="participants-info">
                    <div class="participant-avatars">
                        ${participantsHtml}
                        ${moreParticipants > 0 ? `<div class="participant-avatar more-participants">+${moreParticipants}</div>` : ''}
                    </div>
                    <span class="capacity-info">${event.participants_count}/${event.capacity} participants</span>
                    <div class="capacity-bar">
                        <div class="capacity-fill" style="width: ${event.occupancy_rate}%; background: ${getCapacityColor(event.occupancy_rate)};"></div>
                    </div>
                </div>
            </div>
            <div class="event-actions">
                ${actionsHtml}
            </div>
        </div>
    `;
}

function getRibbonHtml(event) {
    if (event.is_full) {
        return '<div class="event-ribbon ribbon-full">Complet</div>';
    }
    if (event.is_upcoming && (new Date(event.starts_at) - new Date()) < 7 * 24 * 60 * 60 * 1000) {
        return '<div class="event-ribbon ribbon-new">Bientôt</div>';
    }
    if (event.occupancy_rate > 80) {
        return '<div class="event-ribbon ribbon-popular">Populaire</div>';
    }
    return '';
}

function getEventActionsHtml(event) {
    if (event.status === 'completed') {
        return `
            <button class="action-btn-sm btn-participated">
                <i class="fas fa-check"></i> Terminé
            </button>
            <button class="action-btn-sm" onclick="viewEvent('${event.id}')">
                <i class="fas fa-eye"></i> Voir
            </button>
            <button class="action-btn-sm" onclick="shareEvent('${event.id}')">
                <i class="fas fa-share"></i> Partager
            </button>
        `;
    }
    
    if (event.is_full) {
        return `
            <button class="action-btn-sm btn-waitlist" onclick="joinWaitlist('${event.id}', '${event.title}')">
                <i class="fas fa-list"></i> Liste d'attente
            </button>
            <button class="action-btn-sm" onclick="shareEvent('${event.id}')">
                <i class="fas fa-share"></i> Partager
            </button>
            <button class="action-btn-sm" onclick="editEvent('${event.id}')">
                <i class="fas fa-edit"></i> Modifier
            </button>
        `;
    }
    
    return `
        <button class="action-btn-sm btn-participate" onclick="participateEvent('${event.id}', '${event.title}')">
            <i class="fas fa-user-plus"></i> Participer
        </button>
        <button class="action-btn-sm" onclick="shareEvent('${event.id}')">
            <i class="fas fa-share"></i> Partager
        </button>
        <button class="action-btn-sm" onclick="editEvent('${event.id}')">
            <i class="fas fa-edit"></i> Modifier
        </button>
        <button class="action-btn-sm" onclick="deleteEvent('${event.id}', '${event.title}')">
            <i class="fas fa-trash"></i> Supprimer
        </button>
    `;
}

function getCapacityColor(occupancyRate) {
    if (occupancyRate >= 100) return 'var(--danger-color)';
    if (occupancyRate >= 80) return 'var(--warning-color)';
    return 'var(--success-color)';
}

// Event participation
async function participateEvent(eventId, eventTitle) {
    try {
        const response = await fetch(`/admin/api/events/${eventId}/participate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.eventsData.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            loadEvents();
            updateStats();
        } else {
            showAlert('error', result.message);
        }
        
    } catch (error) {
        console.error('Error participating in event:', error);
        showAlert('error', 'Erreur lors de l\'inscription');
    }
}

// Join waitlist
async function joinWaitlist(eventId, eventTitle) {
    try {
        const response = await fetch(`/admin/api/events/${eventId}/participate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.eventsData.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('info', `Vous êtes ajouté à la liste d'attente pour "${eventTitle}"`);
            loadEvents();
        } else {
            showAlert('error', result.message);
        }
        
    } catch (error) {
        console.error('Error joining waitlist:', error);
        showAlert('error', 'Erreur lors de l\'ajout à la liste d\'attente');
    }
}

// Delete event
async function deleteEvent(eventId, eventTitle) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'événement "${eventTitle}" ?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/api/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.eventsData.csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            loadEvents();
            updateStats();
            loadCalendar();
        } else {
            showAlert('error', result.message);
        }
        
    } catch (error) {
        console.error('Error deleting event:', error);
        showAlert('error', 'Erreur lors de la suppression');
    }
}

// Load calendar data
async function loadCalendar() {
    try {
        const response = await fetch(`${window.eventsData.calendarUrl}?month=${currentMonth + 1}&year=${currentYear}`);
        const result = await response.json();
        
        if (result.success) {
            renderCalendar(result.data);
        }
        
    } catch (error) {
        console.error('Error loading calendar:', error);
    }
}

// Render calendar
function renderCalendar(eventsData) {
    const calendarGrid = document.getElementById('calendarGrid');
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const adjustedFirstDay = firstDay === 0 ? 6 : firstDay - 1; // Adjust for Monday start
    
    // Clear existing calendar
    const dayHeaders = calendarGrid.querySelectorAll('.day-header');
    calendarGrid.innerHTML = '';
    
    // Re-add day headers
    dayHeaders.forEach(header => calendarGrid.appendChild(header));
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < adjustedFirstDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day empty';
        calendarGrid.appendChild(emptyDay);
    }
    
    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = day;
        
        // Check if this day has events
        const eventCount = eventsData[day] || 0;
        if (eventCount > 0) {
            dayElement.classList.add(eventCount > 1 ? 'has-multiple' : 'has-event');
            dayElement.title = `${eventCount} événement(s)`;
        }
        
        // Mark today
        const today = new Date();
        if (currentYear === today.getFullYear() && 
            currentMonth === today.getMonth() && 
            day === today.getDate()) {
            dayElement.classList.add('today');
        }
        
        calendarGrid.appendChild(dayElement);
    }
}

// Calendar navigation
function changeMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    } else if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                   'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    
    document.getElementById('currentMonth').textContent = `${months[currentMonth]} ${currentYear}`;
    loadCalendar();
}

// Update statistics
async function updateStats() {
    try {
        const response = await fetch(window.eventsData.statsUrl);
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            
            // Update stat numbers
            animateValue(document.getElementById('events-number'), 0, stats.total_events, 1500);
            animateValue(document.getElementById('participants-number'), 0, stats.total_participants, 1800);
            animateValue(document.getElementById('upcoming-number'), 0, stats.upcoming_events, 1000);
            animateValue(document.getElementById('satisfaction-number'), 0, stats.average_rating * 10, 1200, '', (val) => (val/10).toFixed(1));
        }
        
    } catch (error) {
        console.error('Error updating stats:', error);
    }
}

// Event templates
const eventTemplates = {
    networking: {
        title: "Networking Breakfast",
        description: "Rejoignez-nous pour un moment d'échange et de networking autour d'un délicieux petit-déjeuner.",
        type: "networking",
        duration: 120,
        capacity: 20,
        location: "main",
        price: 0,
        time: "08:30"
    },
    workshop: {
        title: "Workshop Créativité",
        description: "Atelier pratique pour développer votre créativité et apprendre de nouvelles méthodes de travail.",
        type: "workshop", 
        duration: 180,
        capacity: 15,
        location: "meeting-a",
        price: 150,
        time: "14:00"
    },
    conference: {
        title: "Conférence Tech",
        description: "Découvrez les dernières tendances et innovations technologiques avec nos experts.",
        type: "conference",
        duration: 150,
        capacity: 50,
        location: "open-space",
        price: 0,
        time: "19:00"
    },
    social: {
        title: "After-work Détente",
        description: "Moment convivial pour se retrouver et échanger dans une ambiance détendue.",
        type: "social",
        duration: 120,
        capacity: 25,
        location: "terrace",
        price: 0,
        time: "18:00"
    }
};

function useEventTemplate(templateName) {
    const template = eventTemplates[templateName];
    if (!template) return;
    
    document.getElementById('eventTitle').value = template.title;
    document.getElementById('eventDescription').value = template.description;
    document.getElementById('eventType').value = template.type;
    document.getElementById('eventDuration').value = template.duration;
    document.getElementById('eventCapacity').value = template.capacity;
    document.getElementById('eventLocation').value = template.location;
    document.getElementById('eventPrice').value = template.price;
    document.getElementById('eventTime').value = template.time;
}

// Form utilities
function resetForm() {
    const form = document.getElementById('eventForm');
    if (form) {
        form.reset();
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('eventDate').value = today;
    }
}

function saveDraft() {
    // This could save to localStorage or send to server as draft
    showAlert('info', 'Brouillon sauvegardé');
}

// Filter functions
function applyFilters() {
    loadEvents();
}

function refreshEvents() {
    loadEvents();
    loadCalendar();
    updateStats();
    showAlert('info', 'Données actualisées');
}

// Utility functions
function updateCurrentDate() {
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function animateValue(element, start, end, duration, suffix = '', formatter = null) {
    if (!element) return;
    
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (end - start) + start);
        
        if (formatter) {
            element.textContent = formatter(currentValue);
        } else {
            element.textContent = currentValue + suffix;
        }
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function animateNumbers() {
    // Initial animation - will be updated by real data
    const events = document.getElementById('events-number');
    const participants = document.getElementById('participants-number');
    const upcoming = document.getElementById('upcoming-number');
    const satisfaction = document.getElementById('satisfaction-number');

    if (events && !events.dataset.animated) {
        animateValue(events, 0, parseInt(events.textContent), 1500);
        events.dataset.animated = 'true';
    }
    if (participants && !participants.dataset.animated) {
        animateValue(participants, 0, parseInt(participants.textContent), 1800);
        participants.dataset.animated = 'true';
    }
    if (upcoming && !upcoming.dataset.animated) {
        animateValue(upcoming, 0, parseInt(upcoming.textContent), 1000);
        upcoming.dataset.animated = 'true';
    }
    if (satisfaction && !satisfaction.dataset.animated) {
        const value = parseFloat(satisfaction.textContent);
        animateValue(satisfaction, 0, value * 10, 1200, '', (val) => (val/10).toFixed(1));
        satisfaction.dataset.animated = 'true';
    }
}

function animateOnLoad() {
    const loadingElements = document.querySelectorAll('.loading');
    loadingElements.forEach((element, index) => {
        setTimeout(() => {
            element.style.animation = `slideUp 0.6s ease ${index * 0.1}s forwards`;
        }, index * 150);
    });
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alertTypes = {
        success: { icon: 'check-circle', class: 'alert-success' },
        error: { icon: 'exclamation-triangle', class: 'alert-danger' },
        warning: { icon: 'exclamation-circle', class: 'alert-warning' },
        info: { icon: 'info-circle', class: 'alert-info' }
    };
    
    const alertInfo = alertTypes[type] || alertTypes.info;
    
    const alertElement = document.createElement('div');
    alertElement.className = `alert ${alertInfo.class} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertElement.style.zIndex = '9999';
    alertElement.innerHTML = `
        <i class="fas fa-${alertInfo.icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertElement);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertElement.parentNode) {
            alertElement.remove();
        }
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Edit event functionality
async function editEvent(eventId) {
    try {
        // Find the event in current events array
        const event = currentEvents.find(e => e.id == eventId);
        if (!event) {
            showAlert('error', 'Événement non trouvé');
            return;
        }
        
        currentEditingEventId = eventId;
        
        // Populate the edit form
        document.getElementById('editEventId').value = event.id;
        document.getElementById('editEventTitle').value = event.title;
        document.getElementById('editEventDescription').value = event.description;
        document.getElementById('editEventType').value = event.type;
        document.getElementById('editEventLocation').value = event.location;
        document.getElementById('editEventPrice').value = event.price;
        document.getElementById('editEventDuration').value = event.duration;
        document.getElementById('editEventCapacity').value = event.capacity;
        
        // Format dates for inputs
        const startDate = new Date(event.starts_at);
        document.getElementById('editEventDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('editEventTime').value = startDate.toTimeString().substr(0,5);
        
        // Set status if available
        if (event.status) {
            document.getElementById('editEventStatus').value = event.status;
        }
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
        editModal.show();
        
    } catch (error) {
        console.error('Error preparing edit form:', error);
        showAlert('error', 'Erreur lors du chargement des données');
    }
}

// Handle edit form submission
async function handleEditEventSubmit() {
    const submitBtn = document.getElementById('updateEventBtn');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Mise à jour...</span>';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(document.getElementById('editEventForm'));
        const eventData = Object.fromEntries(formData.entries());
        const eventId = eventData.event_id;
        
        // Remove the event_id from the data object as it's not needed in the request body
        delete eventData.event_id;
        
        const response = await fetch(`/admin/api/events/${eventId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': window.eventsData.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(eventData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            
            // Hide the modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
            editModal.hide();
            
            // Reload events and stats
            loadEvents();
            loadCalendar();
            updateStats();
        } else {
            showAlert('error', result.message || 'Erreur lors de la mise à jour de l\'événement');
        }
        
    } catch (error) {
        console.error('Error updating event:', error);
        showAlert('error', 'Erreur de connexion. Veuillez réessayer.');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// View event details
async function viewEvent(eventId) {
    try {
        const event = currentEvents.find(e => e.id == eventId);
        if (!event) {
            showAlert('error', 'Événement non trouvé');
            return;
        }
        
        currentEditingEventId = eventId;
        
        const content = `
            <div class="event-details">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h4 class="text-primary">${event.title}</h4>
                        <span class="badge bg-${getTypeColor(event.type)} mb-2">${event.type_label}</span>
                        <span class="badge bg-${getStatusColor(event.status)} mb-2 ms-2">${event.status_label}</span>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h5 text-success">${event.price_format}</div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-calendar text-primary me-2"></i>Date et heure</h6>
                        <p class="mb-1">${event.formatted_date}</p>
                        <p class="text-muted">${event.formatted_time}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Lieu</h6>
                        <p>${event.location_label}</p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-clock text-primary me-2"></i>Durée</h6>
                        <p>${event.duration} minutes</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-users text-primary me-2"></i>Participants</h6>
                        <p>${event.participants_count}/${event.capacity} participants</p>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-${getCapacityColorClass(event.occupancy_rate)}" 
                                 role="progressbar" style="width: ${event.occupancy_rate}%" 
                                 aria-valuenow="${event.occupancy_rate}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-info-circle text-primary me-2"></i>Description</h6>
                    <p class="text-muted">${event.description}</p>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-user text-primary me-2"></i>Créé par</h6>
                    <p>${event.creator}</p>
                </div>
                
                ${event.participants.length > 0 ? `
                <div class="mb-3">
                    <h6><i class="fas fa-users text-primary me-2"></i>Participants inscrits</h6>
                    <div class="d-flex flex-wrap gap-2">
                        ${event.participants.map(participant => 
                            `<span class="badge bg-light text-dark">${participant.name}</span>`
                        ).join('')}
                        ${event.participants_count > event.participants.length ? 
                            `<span class="badge bg-secondary">+${event.participants_count - event.participants.length} autres</span>` : ''}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        document.getElementById('viewEventContent').innerHTML = content;
        
        const viewModal = new bootstrap.Modal(document.getElementById('viewEventModal'));
        viewModal.show();
        
    } catch (error) {
        console.error('Error viewing event:', error);
        showAlert('error', 'Erreur lors du chargement des détails');
    }
}

// Helper functions for view modal
function getTypeColor(type) {
    const colors = {
        networking: 'info',
        workshop: 'success',
        conference: 'primary',
        social: 'warning',
        training: 'danger'
    };
    return colors[type] || 'secondary';
}

function getStatusColor(status) {
    const colors = {
        draft: 'secondary',
        published: 'success',
        cancelled: 'danger',
        completed: 'info'
    };
    return colors[status] || 'secondary';
}

function getCapacityColorClass(occupancyRate) {
    if (occupancyRate >= 100) return 'danger';
    if (occupancyRate >= 80) return 'warning';
    return 'success';
}

// Additional event functions
function toggleCreator() {
    const creator = document.getElementById('eventCreator');
    if (creator) {
        creator.scrollIntoView({ behavior: 'smooth' });
    }
}

function shareEvent(eventId) {
    const event = currentEvents.find(e => e.id == eventId);
    if (!event) return;
    
    // Simple share functionality - you can enhance this
    const shareText = `Événement: ${event.title}\nDate: ${event.formatted_date}\nLieu: ${event.location_label}`;
    
    if (navigator.share) {
        navigator.share({
            title: event.title,
            text: shareText,
            url: window.location.href
        }).catch(console.error);
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(shareText).then(() => {
            showAlert('success', 'Informations copiées dans le presse-papier');
        }).catch(() => {
            showAlert('info', 'Partage non supporté sur ce navigateur');
        });
    }
}

// Mobile menu toggle
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

// Enhanced error handling for API calls
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    showAlert('error', 'Une erreur inattendue s\'est produite');
});

// Add loading states for better UX
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
    }
}

function hideLoading(elementId, originalContent) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = originalContent;
    }
}