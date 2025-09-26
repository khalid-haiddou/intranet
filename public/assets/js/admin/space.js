// Set CSRF token for all AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Date actuelle
document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

// Global variables
let utilizationChart = null;
let reservationsChart = null;
let users = []; // Will be loaded from API
let currentEditingSpaceId = null;

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 10000; max-width: 350px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// API helper function
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Une erreur est survenue');
        }
        
        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

// Load users for reservations
async function loadUsers() {
    try {
        const response = await apiRequest('/api/users'); // You'll need to create this endpoint
        if (response.success) {
            users = response.data;
        }
    } catch (error) {
        console.error('Failed to load users:', error);
        // Mock users for now
        users = [
            { id: 1, display_name: 'Ahmed Benali', email: 'ahmed@example.com' },
            { id: 2, display_name: 'Fatima Zahra', email: 'fatima@example.com' },
            { id: 3, display_name: 'Mohamed Alami', email: 'mohamed@example.com' }
        ];
    }
}

// Animation des chiffres
function animateNumbers() {
    const numbers = document.querySelectorAll('[id$="-number"]');
    numbers.forEach(element => {
        const target = element.textContent.replace('%', '');
        const isPercentage = element.textContent.includes('%');
        animateValue(element, 0, parseInt(target), 1500, isPercentage ? '%' : '');
    });
}

function animateValue(element, start, end, duration, suffix = '') {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (end - start) + start);
        element.textContent = currentValue + suffix;
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// View space details
async function viewSpaceDetails(spaceId) {
    try {
        showLoading();
        
        const data = await apiRequest(`/dashboard/espaces/${spaceId}`);
        
        if (data.success) {
            displaySpaceDetails(data.data);
            const modal = new bootstrap.Modal(document.getElementById('spaceModal'));
            modal.show();
        }
    } catch (error) {
        showToast('Erreur lors du chargement des détails: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Display space details in modal
function displaySpaceDetails(space) {
    const modalBody = document.getElementById('spaceModalBody');
    
    const featuresHtml = space.features && space.features.length > 0 ? 
        space.features.map(feature => `<span class="feature-tag">${feature}</span>`).join('') : 
        '<span class="text-muted">Aucun équipement spécifié</span>';

    const currentReservationHtml = space.current_reservation ? `
        <div class="alert alert-info">
            <h6><i class="fas fa-clock me-2"></i>Réservation en cours</h6>
            <p><strong>Par:</strong> ${space.current_reservation.user_name}<br>
            <strong>De:</strong> ${space.current_reservation.starts_at} à ${space.current_reservation.ends_at}<br>
            <strong>Participants:</strong> ${space.current_reservation.expected_attendees}/${space.capacity}<br>
            ${space.current_reservation.purpose ? `<strong>Objet:</strong> ${space.current_reservation.purpose}` : ''}</p>
        </div>
    ` : '';

    const upcomingReservationsHtml = space.upcoming_reservations && space.upcoming_reservations.length > 0 ? `
        <h6><i class="fas fa-calendar-alt text-info me-2"></i>Prochaines réservations</h6>
        <div class="list-group">
            ${space.upcoming_reservations.map(reservation => `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <strong>${reservation.user_name}</strong>
                        <small class="text-muted">${reservation.status_label}</small>
                    </div>
                    <small>${reservation.starts_at} - ${reservation.ends_at}</small>
                    ${reservation.purpose ? `<div><small class="text-muted">${reservation.purpose}</small></div>` : ''}
                </div>
            `).join('')}
        </div>
    ` : '<p class="text-muted">Aucune réservation à venir</p>';

    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4>${space.full_name}</h4>
                        <span class="badge bg-${space.status === 'available' ? 'success' : (space.status === 'occupied' ? 'danger' : (space.status === 'reserved' ? 'warning' : 'secondary'))}">${space.status_label}</span>
                        ${space.description ? `<p class="text-muted mt-2">${space.description}</p>` : ''}
                    </div>
                    <div class="text-end">
                        <span class="iot-indicator ${space.iot_status === 'online' ? 'iot-online' : 'iot-offline'}" title="IoT ${space.iot_status === 'online' ? 'en ligne' : 'hors ligne'}"></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Type:</strong><br>
                        <small class="text-muted">${space.type_label}</small>
                    </div>
                    <div class="col-sm-3">
                        <strong>Capacité:</strong><br>
                        <small class="text-muted">${space.capacity} personnes</small>
                    </div>
                    <div class="col-sm-3">
                        <strong>Surface:</strong><br>
                        <small class="text-muted">${space.area ? space.area + ' m²' : 'Non définie'}</small>
                    </div>
                    <div class="col-sm-3">
                        <strong>Étage:</strong><br>
                        <small class="text-muted">Niveau ${space.floor_level}</small>
                    </div>
                </div>

                ${space.location_details ? `
                    <div class="mb-3">
                        <strong>Localisation:</strong><br>
                        <small class="text-muted">${space.location_details}</small>
                    </div>
                ` : ''}
                
                <div class="mb-3">
                    <strong>Équipements disponibles:</strong><br>
                    <div class="mt-2">${featuresHtml}</div>
                </div>

                ${space.hourly_rate || space.daily_rate ? `
                    <div class="mb-3">
                        <strong>Tarification:</strong><br>
                        ${space.hourly_rate ? `<small class="text-muted">Horaire: ${space.hourly_rate} MAD/h</small><br>` : ''}
                        ${space.daily_rate ? `<small class="text-muted">Journalier: ${space.daily_rate} MAD/jour</small>` : ''}
                    </div>
                ` : ''}

                ${currentReservationHtml}

                <div class="mt-4">
                    ${upcomingReservationsHtml}
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body text-center">
                        <h3 class="text-primary">${space.occupancy_rate}%</h3>
                        <p class="mb-0">Occupation actuelle</p>
                        <small class="text-muted">${space.current_occupancy}/${space.capacity} personnes</small>
                    </div>
                </div>
                
                <div class="card bg-light mb-3">
                    <div class="card-body text-center">
                        <h5 class="text-info">${space.utilization_rate}%</h5>
                        <p class="mb-0">Taux d'utilisation</p>
                        <small class="text-muted">Ce mois</small>
                    </div>
                </div>

                ${space.monthly_revenue ? `
                    <div class="card bg-light mb-3">
                        <div class="card-body text-center">
                            <h5 class="text-success">${space.monthly_revenue} MAD</h5>
                            <p class="mb-0">Revenus du mois</p>
                        </div>
                    </div>
                ` : ''}

                <div class="d-grid gap-2">
                    ${space.is_available ? `
                        <button class="btn btn-success" onclick="showReserveSpaceModal(${space.id})">
                            <i class="fas fa-calendar-plus"></i> Réserver
                        </button>
                    ` : ''}
                    <button class="btn btn-warning" onclick="showScheduleMaintenanceModal(${space.id})">
                        <i class="fas fa-tools"></i> Programmer maintenance
                    </button>
                    <button class="btn btn-info" onclick="showEditSpaceModal(${space.id})">
                        <i class="fas fa-edit"></i> Modifier l'espace
                    </button>
                    <button class="btn btn-secondary" onclick="checkSpaceAvailability(${space.id})">
                        <i class="fas fa-calendar"></i> Voir disponibilités
                    </button>
                </div>

                ${space.maintenance_records && space.maintenance_records.length > 0 ? `
                    <div class="mt-3">
                        <h6>Historique maintenance</h6>
                        <div class="list-group list-group-flush">
                            ${space.maintenance_records.slice(0, 3).map(maintenance => `
                                <div class="list-group-item px-0">
                                    <small><strong>${maintenance.title}</strong><br>
                                    ${maintenance.type_label} - ${maintenance.status_label}<br>
                                    ${maintenance.scheduled_at}</small>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Show create space modal
function showCreateSpaceModal() {
    const modalTitle = document.querySelector('#createSpaceModal .modal-title');
    modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>Nouvel espace';
    
    currentEditingSpaceId = null;
    const modalBody = document.getElementById('createSpaceModalBody');
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Nom de l'espace *</label>
                    <input type="text" name="name" class="form-control" placeholder="Ex: Salle de réunion A" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Numéro/Identifiant *</label>
                    <input type="text" name="number" class="form-control" placeholder="Ex: A101, B205" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Type d'espace *</label>
                    <select name="type" class="form-select" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="office">Bureau privé</option>
                        <option value="meeting_room">Salle de réunion</option>
                        <option value="open_space">Espace ouvert</option>
                        <option value="phone_booth">Cabine téléphonique</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Capacité *</label>
                    <input type="number" name="capacity" class="form-control" min="1" max="200" placeholder="Nombre de personnes" required>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Description de l'espace..."></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Surface (m²)</label>
                    <input type="number" name="area" class="form-control" step="0.1" min="0.1" placeholder="Surface en m²">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Étage</label>
                    <input type="number" name="floor_level" class="form-control" min="0" value="1" placeholder="Niveau d'étage">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tarif horaire (MAD)</label>
                    <input type="number" name="hourly_rate" class="form-control" step="0.01" min="0" placeholder="Prix par heure">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tarif journalier (MAD)</label>
                    <input type="number" name="daily_rate" class="form-control" step="0.01" min="0" placeholder="Prix par jour">
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Détails de localisation</label>
            <input type="text" name="location_details" class="form-control" placeholder="Ex: Près de l'entrée principale, côte fenêtre...">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Équipements disponibles</label>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="WiFi" id="wifi">
                        <label class="form-check-label" for="wifi">WiFi</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Écran TV" id="tv">
                        <label class="form-check-label" for="tv">Écran TV</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Projecteur" id="projector">
                        <label class="form-check-label" for="projector">Projecteur</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Tableau blanc" id="whiteboard">
                        <label class="form-check-label" for="whiteboard">Tableau blanc</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Climatisation" id="ac">
                        <label class="form-check-label" for="ac">Climatisation</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Prises électriques" id="outlets">
                        <label class="form-check-label" for="outlets">Prises électriques</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Éclairage LED" id="lighting">
                        <label class="form-check-label" for="lighting">Éclairage LED</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Insonorisation" id="soundproof">
                        <label class="form-check-label" for="soundproof">Insonorisation</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]" value="Visioconférence" id="videoconf">
                        <label class="form-check-label" for="videoconf">Visioconférence</label>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('createSpaceModal'));
    modal.show();
}

// Show edit space modal
async function showEditSpaceModal(spaceId) {
    try {
        showLoading();
        
        const data = await apiRequest(`/dashboard/espaces/${spaceId}`);
        
        if (data.success) {
            const space = data.data;
            currentEditingSpaceId = spaceId;
            
            const modalTitle = document.querySelector('#createSpaceModal .modal-title');
            modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Modifier l\'espace';
            
            const modalBody = document.getElementById('createSpaceModalBody');
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nom de l'espace *</label>
                            <input type="text" name="name" class="form-control" value="${space.name || ''}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Numéro/Identifiant *</label>
                            <input type="text" name="number" class="form-control" value="${space.number || ''}" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Type d'espace *</label>
                            <select name="type" class="form-select" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="office" ${space.type === 'office' ? 'selected' : ''}>Bureau privé</option>
                                <option value="meeting_room" ${space.type === 'meeting_room' ? 'selected' : ''}>Salle de réunion</option>
                                <option value="open_space" ${space.type === 'open_space' ? 'selected' : ''}>Espace ouvert</option>
                                <option value="phone_booth" ${space.type === 'phone_booth' ? 'selected' : ''}>Cabine téléphonique</option>
                                <option value="other" ${space.type === 'other' ? 'selected' : ''}>Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Capacité *</label>
                            <input type="number" name="capacity" class="form-control" value="${space.capacity || ''}" min="1" max="200" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">${space.description || ''}</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Surface (m²)</label>
                            <input type="number" name="area" class="form-control" value="${space.area || ''}" step="0.1" min="0.1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Étage</label>
                            <input type="number" name="floor_level" class="form-control" value="${space.floor_level || 1}" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tarif horaire (MAD)</label>
                            <input type="number" name="hourly_rate" class="form-control" value="${space.hourly_rate || ''}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tarif journalier (MAD)</label>
                            <input type="number" name="daily_rate" class="form-control" value="${space.daily_rate || ''}" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Détails de localisation</label>
                    <input type="text" name="location_details" class="form-control" value="${space.location_details || ''}">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Équipements disponibles</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="WiFi" id="edit_wifi" ${space.features && space.features.includes('WiFi') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_wifi">WiFi</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Écran TV" id="edit_tv" ${space.features && space.features.includes('Écran TV') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_tv">Écran TV</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Projecteur" id="edit_projector" ${space.features && space.features.includes('Projecteur') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_projector">Projecteur</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Tableau blanc" id="edit_whiteboard" ${space.features && space.features.includes('Tableau blanc') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_whiteboard">Tableau blanc</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Climatisation" id="edit_ac" ${space.features && space.features.includes('Climatisation') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_ac">Climatisation</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Prises électriques" id="edit_outlets" ${space.features && space.features.includes('Prises électriques') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_outlets">Prises électriques</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Éclairage LED" id="edit_lighting" ${space.features && space.features.includes('Éclairage LED') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_lighting">Éclairage LED</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Insonorisation" id="edit_soundproof" ${space.features && space.features.includes('Insonorisation') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_soundproof">Insonorisation</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" value="Visioconférence" id="edit_videoconf" ${space.features && space.features.includes('Visioconférence') ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_videoconf">Visioconférence</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" ${space.is_active !== false ? 'checked' : ''}>
                        <label class="form-check-label" for="is_active">
                            Espace actif
                        </label>
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('createSpaceModal'));
            modal.show();
        }
    } catch (error) {
        showToast('Erreur lors du chargement des détails: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Handle create/edit space form submission
async function handleCreateSpaceForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    // Convert features checkboxes to array
    const features = [];
    formData.getAll('features[]').forEach(feature => {
        if (feature) features.push(feature);
    });
    
    const data = {
        name: formData.get('name'),
        number: formData.get('number'),
        type: formData.get('type'),
        description: formData.get('description'),
        capacity: parseInt(formData.get('capacity')),
        area: formData.get('area') ? parseFloat(formData.get('area')) : null,
        floor_level: parseInt(formData.get('floor_level')),
        hourly_rate: formData.get('hourly_rate') ? parseFloat(formData.get('hourly_rate')) : null,
        daily_rate: formData.get('daily_rate') ? parseFloat(formData.get('daily_rate')) : null,
        location_details: formData.get('location_details'),
        features: features
    };
    
    // Add is_active for edit mode
    if (currentEditingSpaceId) {
        data.is_active = formData.has('is_active');
    }
    
    try {
        showLoading();
        
        const url = currentEditingSpaceId ? 
            `/dashboard/espaces/${currentEditingSpaceId}` : 
            '/dashboard/espaces';
        
        const method = currentEditingSpaceId ? 'PUT' : 'POST';
        
        const result = await apiRequest(url, {
            method: method,
            body: JSON.stringify(data)
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createSpaceModal'));
            modal.hide();
            
            // Refresh page
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Show reserve space modal
function showReserveSpaceModal(spaceId) {
    const modalBody = document.getElementById('createSpaceModalBody');
    const modalTitle = document.querySelector('#createSpaceModal .modal-title');
    modalTitle.innerHTML = '<i class="fas fa-calendar-plus me-2"></i>Réserver l\'espace';
    
    // Get tomorrow's date as default
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const defaultDate = tomorrow.toISOString().split('T')[0];
    
    modalBody.innerHTML = `
        <div class="mb-3">
            <label class="form-label">Membre *</label>
            <select name="user_id" class="form-select" required>
                <option value="">Sélectionnez un membre</option>
                ${users.map(user => `<option value="${user.id}">${user.display_name} (${user.email})</option>`).join('')}
            </select>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date de début *</label>
                    <input type="date" name="start_date" class="form-control" value="${defaultDate}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Heure de début *</label>
                    <input type="time" name="start_time" class="form-control" value="09:00" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date de fin *</label>
                    <input type="date" name="end_date" class="form-control" value="${defaultDate}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Heure de fin *</label>
                    <input type="time" name="end_time" class="form-control" value="17:00" required>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Nombre de participants *</label>
            <input type="number" name="expected_attendees" class="form-control" min="1" value="1" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Objet de la réservation</label>
            <input type="text" name="purpose" class="form-control" placeholder="Ex: Réunion équipe, Formation...">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Notes supplémentaires</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Informations supplémentaires..."></textarea>
        </div>
        
        <input type="hidden" name="space_id" value="${spaceId}">
    `;
    
    // Override the form submit handler for reservation
    const form = document.getElementById('createSpaceForm');
    form.onsubmit = handleReservationForm;
    
    const modal = new bootstrap.Modal(document.getElementById('createSpaceModal'));
    modal.show();
}

// Handle reservation form submission
async function handleReservationForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    const startDateTime = `${formData.get('start_date')}T${formData.get('start_time')}`;
    const endDateTime = `${formData.get('end_date')}T${formData.get('end_time')}`;
    
    const data = {
        user_id: parseInt(formData.get('user_id')),
        starts_at: startDateTime,
        ends_at: endDateTime,
        expected_attendees: parseInt(formData.get('expected_attendees')),
        purpose: formData.get('purpose'),
        notes: formData.get('notes')
    };
    
    const spaceId = formData.get('space_id');
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/espaces/${spaceId}/reservations`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createSpaceModal'));
            modal.hide();
            
            // Reset form handler
            document.getElementById('createSpaceForm').onsubmit = handleCreateSpaceForm;
            
            // Refresh page
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Show schedule maintenance modal
function showScheduleMaintenanceModal(spaceId = null) {
    const modalBody = document.getElementById('createSpaceModalBody');
    const modalTitle = document.querySelector('#createSpaceModal .modal-title');
    modalTitle.innerHTML = '<i class="fas fa-tools me-2"></i>Programmer maintenance';
    
    // Get tomorrow's date as default
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const defaultDateTime = tomorrow.toISOString().slice(0, 16);
    
    modalBody.innerHTML = `
        <div class="mb-3">
            <label class="form-label">Titre de la maintenance *</label>
            <input type="text" name="title" class="form-control" placeholder="Ex: Vérification climatisation" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Description *</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Détails de la maintenance à effectuer..." required></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Type de maintenance *</label>
                    <select name="type" class="form-select" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="preventive">Maintenance préventive</option>
                        <option value="corrective">Maintenance corrective</option>
                        <option value="emergency">Intervention d'urgence</option>
                        <option value="inspection">Inspection</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Priorité *</label>
                    <select name="priority" class="form-select" required>
                        <option value="">Sélectionnez une priorité</option>
                        <option value="low">Faible</option>
                        <option value="medium">Moyenne</option>
                        <option value="high">Haute</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Date et heure prévue *</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" value="${defaultDateTime}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Coût estimé (MAD)</label>
                    <input type="number" name="estimated_cost" class="form-control" step="0.01" min="0" placeholder="Coût prévu">
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Assigné à</label>
            <input type="text" name="assigned_to" class="form-control" placeholder="Nom du technicien/responsable">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Pièces/Matériaux nécessaires</label>
            <textarea name="parts_needed" class="form-control" rows="2" placeholder="Liste des pièces ou matériaux requis (un par ligne)"></textarea>
        </div>
        
        ${spaceId ? `<input type="hidden" name="space_id" value="${spaceId}">` : ''}
    `;
    
    // Override the form submit handler for maintenance
    const form = document.getElementById('createSpaceForm');
    form.onsubmit = handleMaintenanceForm;
    
    const modal = new bootstrap.Modal(document.getElementById('createSpaceModal'));
    modal.show();
}

// Handle maintenance form submission
async function handleMaintenanceForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    // Convert parts_needed to array
    const partsNeeded = formData.get('parts_needed') ? 
        formData.get('parts_needed').split('\n').filter(part => part.trim()) : [];
    
    const data = {
        title: formData.get('title'),
        description: formData.get('description'),
        type: formData.get('type'),
        priority: formData.get('priority'),
        scheduled_at: formData.get('scheduled_at'),
        estimated_cost: formData.get('estimated_cost') ? parseFloat(formData.get('estimated_cost')) : null,
        assigned_to: formData.get('assigned_to'),
        parts_needed: partsNeeded
    };
    
    const spaceId = formData.get('space_id');
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/espaces/${spaceId}/maintenance`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createSpaceModal'));
            modal.hide();
            
            // Reset form handler
            document.getElementById('createSpaceForm').onsubmit = handleCreateSpaceForm;
            
            // Refresh page
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Check space availability
async function checkSpaceAvailability(spaceId) {
    try {
        showLoading();
        
        const today = new Date().toISOString().split('T')[0];
        const data = await apiRequest(`/dashboard/espaces/${spaceId}/availability?date=${today}`);
        
        if (data.success) {
            displayAvailabilityModal(data.data);
        }
    } catch (error) {
        showToast('Erreur lors de la vérification des disponibilités: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Display availability modal
function displayAvailabilityModal(availabilityData) {
    const modalBody = document.getElementById('spaceModalBody');
    
    modalBody.innerHTML = `
        <div class="text-center mb-4">
            <h5>Disponibilités pour le ${availabilityData.date}</h5>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-${availabilityData.is_available_now ? 'success' : 'danger'} text-white text-center">
                    <div class="card-body">
                        <h6>Statut actuel</h6>
                        <h4>${availabilityData.is_available_now ? 'Disponible' : 'Occupé'}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h6>Réservations aujourd'hui</h6>
                        <h4>${availabilityData.reservations.length}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <h6>Créneaux horaires</h6>
        <div class="row">
            ${availabilityData.available_slots.map(slot => `
                <div class="col-md-3 mb-2">
                    <div class="card ${slot.available ? 'border-success' : 'border-danger'}" style="border-width: 2px;">
                        <div class="card-body text-center py-2">
                            <small>${slot.start} - ${slot.end}</small><br>
                            <span class="badge bg-${slot.available ? 'success' : 'danger'}">
                                ${slot.available ? 'Libre' : 'Occupé'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
        
        ${availabilityData.reservations.length > 0 ? `
            <div class="mt-4">
                <h6>Réservations du jour</h6>
                <div class="list-group">
                    ${availabilityData.reservations.map(reservation => `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${reservation.starts_at} - ${reservation.ends_at}</strong><br>
                                    <small>${reservation.expected_attendees} participant(s)</small>
                                </div>
                                <span class="badge bg-primary">${reservation.expected_attendees} pers.</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : '<div class="alert alert-success mt-4">Aucune réservation pour aujourd\'hui</div>'}
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('spaceModal'));
    modal.show();
}

// Update space statuses dynamically
async function updateSpaceStatuses() {
    const spaceItems = document.querySelectorAll('.space-item');
    
    for (const item of spaceItems) {
        const spaceId = item.dataset.spaceId;
        if (spaceId) {
            try {
                const data = await apiRequest(`/dashboard/espaces/${spaceId}`);
                if (data.success) {
                    updateSpaceItemDisplay(item, data.data);
                }
            } catch (error) {
                console.error('Error updating space status:', error);
            }
        }
    }
}

// Update space item display
function updateSpaceItemDisplay(item, spaceData) {
    const statusBadge = item.querySelector('.status-badge');
    const occupancyBar = item.querySelector('.capacity-fill');
    const occupancyText = item.querySelectorAll('small');
    
    if (statusBadge) {
        statusBadge.className = `status-badge status-${spaceData.status}`;
        statusBadge.textContent = spaceData.status_label;
    }
    
    if (occupancyBar) {
        occupancyBar.style.width = spaceData.occupancy_rate + '%';
        const color = spaceData.occupancy_rate >= 90 ? 'var(--danger-color)' : 
                     (spaceData.occupancy_rate >= 70 ? 'var(--warning-color)' : 'var(--success-color)');
        occupancyBar.style.background = color;
    }
    
    if (occupancyText.length >= 2) {
        occupancyText[0].textContent = `Occupation: ${spaceData.current_occupancy}/${spaceData.capacity}`;
        occupancyText[1].textContent = spaceData.occupancy_rate + '%';
    }
}

// Update statistics
async function updateStats() {
    try {
        const data = await apiRequest('/dashboard/espaces/stats');
        if (data.success) {
            const stats = data.data;
            
            document.getElementById('spaces-number').textContent = stats.total_spaces;
            document.getElementById('occupation-number').textContent = stats.occupancy_rate + '%';
            document.getElementById('reservations-number').textContent = stats.today_reservations;
            document.getElementById('maintenance-number').textContent = stats.pending_maintenance;
            
            // Update notification badge
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                badge.textContent = stats.urgent_maintenance;
            }
        }
    } catch (error) {
        console.error('Failed to update stats:', error);
    }
}

// Initialize charts
async function initializeCharts() {
    try {
        const data = await apiRequest('/dashboard/espaces/dashboard');
        if (data.success) {
            const dashboardData = data.data;
            
            // Create utilization chart
            createUtilizationChart(dashboardData.hourly_occupancy);
            
            // Create space types chart
            createSpaceTypesChart(dashboardData.space_types);
        }
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
        // Create default charts with sample data
        createSampleCharts();
    }
}

// Create utilization chart
function createUtilizationChart(hourlyData) {
    const ctx = document.getElementById('utilizationChart');
    if (!ctx) return;
    
    if (utilizationChart) {
        utilizationChart.destroy();
    }
    
    utilizationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: hourlyData ? hourlyData.map(d => d.hour) : ['8h', '9h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h'],
            datasets: [{
                label: 'Occupation (%)',
                data: hourlyData ? hourlyData.map(d => d.occupancy) : [45, 62, 78, 85, 92, 75, 88, 94, 89, 76, 58],
                borderColor: '#27AE60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#27AE60',
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
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        color: '#7f8c8d',
                        callback: function(value) {
                            return value + '%';
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

// Create space types chart
function createSpaceTypesChart(spaceTypes) {
    const ctx = document.getElementById('reservationsChart');
    if (!ctx) return;
    
    if (reservationsChart) {
        reservationsChart.destroy();
    }
    
    const labels = spaceTypes ? spaceTypes.map(s => s.label) : ['Bureau privé', 'Salle réunion', 'Espace ouvert', 'Cabine tel.'];
    const data = spaceTypes ? spaceTypes.map(s => s.count) : [6, 4, 3, 2];
    
    reservationsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nombre d\'espaces',
                data: data,
                backgroundColor: [
                    '#FFCC01',
                    '#3498DB',
                    '#27AE60',
                    '#9B59B6'
                ],
                borderRadius: 8,
                borderSkipped: false
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
                        color: '#7f8c8d'
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

// Create sample charts when real data is not available
function createSampleCharts() {
    createUtilizationChart(null);
    createSpaceTypesChart(null);
}

// Update IoT status simulation
function updateIoTStatus() {
    // Simulate IoT sensor updates
    const temperature = 20 + Math.random() * 6; // 20-26°C
    const wifiQuality = 85 + Math.random() * 15; // 85-100%
    const lightingLevel = 70 + Math.random() * 30; // 70-100%
    
    const tempElement = document.getElementById('temperature-status');
    const wifiElement = document.getElementById('wifi-status');
    const lightingElement = document.getElementById('lighting-status');
    
    if (tempElement) {
        const temp = temperature.toFixed(1);
        const status = temp < 22 ? 'Frais' : temp > 25 ? 'Chaud' : 'Optimal';
        tempElement.textContent = `${temp}°C • ${status}`;
    }
    
    if (wifiElement) {
        const wifi = Math.floor(wifiQuality);
        const status = wifi > 95 ? 'Excellent' : wifi > 85 ? 'Bon' : 'Moyen';
        wifiElement.textContent = `${wifi}% • ${status} signal`;
    }
    
    if (lightingElement) {
        const lighting = Math.floor(lightingLevel);
        lightingElement.textContent = `${lighting}% • Ajustement auto`;
    }
}

// Maintenance actions
async function startMaintenance(maintenanceId) {
    try {
        showLoading();
        
        const result = await apiRequest(`/api/maintenance/${maintenanceId}/start`, {
            method: 'POST'
        });
        
        if (result.success) {
            showToast('Maintenance démarrée avec succès');
            setTimeout(() => window.location.reload(), 1500);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

async function completeMaintenance(maintenanceId) {
    const actualCost = prompt('Coût réel de la maintenance (MAD):');
    const notes = prompt('Notes de fin (optionnel):');
    
    if (actualCost === null) return; // User cancelled
    
    try {
        showLoading();
        
        const result = await apiRequest(`/api/maintenance/${maintenanceId}/complete`, {
            method: 'POST',
            body: JSON.stringify({
                actual_cost: actualCost ? parseFloat(actualCost) : null,
                notes: notes
            })
        });
        
        if (result.success) {
            showToast('Maintenance terminée avec succès');
            setTimeout(() => window.location.reload(), 1500);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Bind event listeners
function bindEventListeners() {
    // View space details
    document.querySelectorAll('.view-space').forEach(btn => {
        btn.addEventListener('click', function() {
            const spaceId = this.dataset.spaceId;
            viewSpaceDetails(spaceId);
        });
    });
    
    // Reserve space
    document.querySelectorAll('.reserve-space').forEach(btn => {
        btn.addEventListener('click', function() {
            const spaceId = this.dataset.spaceId;
            showReserveSpaceModal(spaceId);
        });
    });
    
    // Edit space
    document.querySelectorAll('.edit-space').forEach(btn => {
        btn.addEventListener('click', function() {
            const spaceId = this.dataset.spaceId;
            showEditSpaceModal(spaceId);
        });
    });
    
    // Schedule maintenance
    document.querySelectorAll('.schedule-maintenance').forEach(btn => {
        btn.addEventListener('click', function() {
            const spaceId = this.dataset.spaceId;
            showScheduleMaintenanceModal(spaceId);
        });
    });
    
    // Maintenance actions
    document.querySelectorAll('.start-maintenance').forEach(btn => {
        btn.addEventListener('click', function() {
            const maintenanceId = this.dataset.maintenanceId;
            if (confirm('Êtes-vous sûr de vouloir démarrer cette maintenance?')) {
                startMaintenance(maintenanceId);
            }
        });
    });
    
    document.querySelectorAll('.complete-maintenance').forEach(btn => {
        btn.addEventListener('click', function() {
            const maintenanceId = this.dataset.maintenanceId;
            if (confirm('Êtes-vous sûr de vouloir terminer cette maintenance?')) {
                completeMaintenance(maintenanceId);
            }
        });
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

// Gestion des clics sur la sidebar
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        // Only prevent default for links without href or with # href
        if (!this.href || this.href.endsWith('#')) {
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

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', async function() {
    animateOnLoad();
    setTimeout(animateNumbers, 800);
    
    // Load users for reservations
    await loadUsers();
    
    // Bind event listeners
    bindEventListeners();
    
    // Initialize charts
    setTimeout(initializeCharts, 1000);
    
    // Handle create space form
    const createSpaceForm = document.getElementById('createSpaceForm');
    if (createSpaceForm) {
        createSpaceForm.addEventListener('submit', handleCreateSpaceForm);
    }
    
    // Update IoT status every 30 seconds
    setInterval(updateIoTStatus, 30000);
    updateIoTStatus(); // Initial update
});

// Refresh stats and space statuses periodically (every 2 minutes)
setInterval(() => {
    updateStats();
    updateSpaceStatuses();
}, 120000); // 2 minutes