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
let optionCount = 2;
let templates = {};

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

// Poll Creator Toggle
function toggleCreator() {
    const section = document.getElementById('creatorSection');
    const isVisible = section.style.display !== 'none';
    
    if (isVisible) {
        section.style.display = 'none';
        resetPollForm();
    } else {
        section.style.display = 'grid';
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// Reset poll form
function resetPollForm() {
    document.getElementById('pollCreatorForm').reset();
    
    // Reset options to 2
    const container = document.getElementById('optionsContainer');
    container.innerHTML = `
        <div class="option-item">
            <div class="option-number">1</div>
            <input type="text" class="option-input" name="options[]" placeholder="Première option" required>
            <button type="button" class="remove-option" style="display: none;"><i class="fas fa-times"></i></button>
        </div>
        <div class="option-item">
            <div class="option-number">2</div>
            <input type="text" class="option-input" name="options[]" placeholder="Deuxième option" required>
            <button type="button" class="remove-option" style="display: none;"><i class="fas fa-times"></i></button>
        </div>
    `;
    
    optionCount = 2;
    updateRemoveButtons();
}

// Poll Creator Functionality
function initializePollCreator() {
    const addButton = document.getElementById('addNewOption');
    if (addButton) {
        addButton.addEventListener('click', function() {
            optionCount++;
            const container = document.getElementById('optionsContainer');
            const newOption = document.createElement('div');
            newOption.className = 'option-item';
            newOption.innerHTML = `
                <div class="option-number">${optionCount}</div>
                <input type="text" class="option-input" name="options[]" placeholder="Option ${optionCount}" required>
                <button type="button" class="remove-option" onclick="removeOption(this)"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(newOption);
            
            updateRemoveButtons();
        });
    }
}

function removeOption(button) {
    button.parentElement.remove();
    optionCount--;
    updateOptionNumbers();
    updateRemoveButtons();
}

function updateOptionNumbers() {
    const options = document.querySelectorAll('.option-item');
    options.forEach((option, index) => {
        const number = option.querySelector('.option-number');
        const input = option.querySelector('.option-input');
        number.textContent = index + 1;
        if (input.placeholder.startsWith('Option')) {
            input.placeholder = `Option ${index + 1}`;
        }
    });
    optionCount = options.length;
}

function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-option');
    removeButtons.forEach(btn => {
        btn.style.display = optionCount > 2 ? 'flex' : 'none';
    });
}

// Load templates
async function loadTemplates() {
    try {
        const data = await apiRequest('/dashboard/sondages/templates');
        if (data.success) {
            templates = data.data;
        }
    } catch (error) {
        console.error('Failed to load templates:', error);
    }
}

// Use template
function useTemplate(templateName) {
    const template = templates[templateName];
    if (!template) return;
    
    document.getElementById('pollTitle').value = template.title;
    document.getElementById('pollDescription').value = template.description;
    
    // Clear existing options
    const container = document.getElementById('optionsContainer');
    container.innerHTML = '';
    optionCount = 0;
    
    // Add template options
    template.options.forEach((option, index) => {
        optionCount++;
        const newOption = document.createElement('div');
        newOption.className = 'option-item';
        newOption.innerHTML = `
            <div class="option-number">${optionCount}</div>
            <input type="text" class="option-input" name="options[]" value="${option}" required>
            <button type="button" class="remove-option" onclick="removeOption(this)"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(newOption);
    });
    
    updateRemoveButtons();
    
    // Show creator if hidden
    const section = document.getElementById('creatorSection');
    if (section.style.display === 'none') {
        toggleCreator();
    } else {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// Handle poll form submission
async function handlePollFormSubmission(event) {
    event.preventDefault();
    
    const submitButton = event.submitter;
    const action = submitButton.dataset.action;
    
    const formData = new FormData(event.target);
    
    // Convert FormData to regular object
    const data = {
        title: formData.get('title'),
        description: formData.get('description'),
        options: formData.getAll('options[]').filter(option => option.trim() !== ''),
        duration_days: parseInt(formData.get('duration_days')),
        visibility: formData.get('visibility'),
        allow_multiple_choices: formData.get('allow_multiple_choices') === 'on',
        anonymous_voting: formData.get('anonymous_voting') === 'on',
        publish_immediately: action === 'publish'
    };
    
    // Validation
    if (!data.title.trim()) {
        showToast('Le titre est requis', 'danger');
        return;
    }
    
    if (data.options.length < 2) {
        showToast('Au moins 2 options sont requises', 'danger');
        return;
    }
    
    try {
        showLoading();
        
        const result = await apiRequest('/dashboard/sondages', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Reset form and hide creator
            resetPollForm();
            toggleCreator();
            
            // Add new poll to list if published
            if (data.publish_immediately) {
                addPollToList(result.data.poll);
            }
            
            // Update stats
            updateStats();
            
            // Refresh page after delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// View poll details
async function viewPollDetails(pollId) {
    try {
        showLoading();
        
        const data = await apiRequest(`/dashboard/sondages/${pollId}`);
        
        if (data.success) {
            displayPollDetails(data.data);
            const modal = new bootstrap.Modal(document.getElementById('pollModal'));
            modal.show();
        }
    } catch (error) {
        showToast('Erreur lors du chargement des détails: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Display poll details in modal
function displayPollDetails(poll) {
    const modalBody = document.getElementById('pollModalBody');
    
    const votesSection = poll.total_votes > 0 ? `
        <div class="row mt-4">
            <div class="col-md-12">
                <h6><i class="fas fa-chart-bar text-primary me-2"></i>Résultats détaillés</h6>
                ${poll.vote_results.map((result, index) => `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">${result.option}</span>
                            <small class="text-muted">${result.votes} votes (${result.percentage}%)</small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" style="width: ${result.percentage}%; background: ${['var(--primary-color)', 'var(--info-color)', 'var(--success-color)', 'var(--warning-color)', 'var(--danger-color)'][index % 5]};">
                                ${result.percentage}%
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
        
        ${poll.recent_voters && poll.recent_voters.length > 0 ? `
            <div class="row mt-4">
                <div class="col-md-12">
                    <h6><i class="fas fa-users text-success me-2"></i>Votes récents</h6>
                    <div class="list-group">
                        ${poll.recent_voters.map(voter => `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong>${voter.user_name}</strong>
                                    <small class="text-muted">${voter.voted_at}</small>
                                </div>
                                <small>A voté pour: ${voter.selected_options.join(', ')}</small>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        ` : ''}
    ` : '<div class="alert alert-info mt-4">Aucun vote pour le moment</div>';
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-8">
                <h4>${poll.title}</h4>
                ${poll.description ? `<p class="text-muted">${poll.description}</p>` : ''}
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Statut:</strong><br>
                        <span class="badge bg-${poll.status === 'active' ? 'success' : (poll.status === 'ended' ? 'secondary' : 'warning')}">${poll.status_label}</span>
                    </div>
                    <div class="col-sm-4">
                        <strong>Visibilité:</strong><br>
                        <small class="text-muted">${poll.visibility_label}</small>
                    </div>
                    <div class="col-sm-4">
                        <strong>Participation:</strong><br>
                        <small class="text-muted">${poll.participation_rate}%</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Créé le:</strong><br>
                        <small class="text-muted">${poll.created_at}</small>
                    </div>
                    <div class="col-sm-6">
                        <strong>Créé par:</strong><br>
                        <small class="text-muted">${poll.created_by}</small>
                    </div>
                </div>
                
                ${poll.status === 'active' && poll.time_remaining ? `
                    <div class="alert alert-warning">
                        <i class="fas fa-clock me-2"></i>
                        Se termine ${poll.time_remaining}
                    </div>
                ` : ''}
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h3 class="text-primary">${poll.total_votes}</h3>
                        <p class="mb-0">Votes totaux</p>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Options disponibles:</h6>
                    <ul class="list-group list-group-flush">
                        ${poll.options.map((option, index) => `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                ${option}
                                <span class="badge bg-secondary">${index + 1}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
                
                <div class="mt-3">
                    <small>
                        ${poll.allow_multiple_choices ? '<i class="fas fa-check text-success"></i> Choix multiples autorisés' : '<i class="fas fa-times text-danger"></i> Un seul choix'}
                    </small><br>
                    <small>
                        ${poll.anonymous_voting ? '<i class="fas fa-eye-slash text-info"></i> Vote anonyme' : '<i class="fas fa-eye text-secondary"></i> Vote public'}
                    </small>
                </div>
            </div>
        </div>
        ${votesSection}
    `;
}

// Publish poll
async function publishPoll(pollId) {
    if (!confirm('Êtes-vous sûr de vouloir publier ce sondage ?')) {
        return;
    }
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/sondages/${pollId}/publish`, {
            method: 'POST'
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Update poll item in the list
            updatePollItem(pollId, result.data.poll);
            updateStats();
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// End poll
async function endPoll(pollId) {
    if (!confirm('Êtes-vous sûr de vouloir terminer ce sondage ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/sondages/${pollId}/end`, {
            method: 'POST'
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Update poll item in the list
            updatePollItem(pollId, result.data.poll);
            updateStats();
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Delete poll
async function deletePoll(pollId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce sondage ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        showLoading();
        
        const result = await apiRequest(`/dashboard/sondages/${pollId}`, {
            method: 'DELETE'
        });
        
        if (result.success) {
            showToast(result.message);
            
            // Remove poll item from list
            const pollItem = document.querySelector(`[data-poll-id="${pollId}"]`);
            if (pollItem) {
                pollItem.remove();
            }
            
            updateStats();
        }
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// Update poll item in list
function updatePollItem(pollId, pollData) {
    const pollItem = document.querySelector(`[data-poll-id="${pollId}"]`);
    if (pollItem) {
        const statusBadge = pollItem.querySelector('.poll-status');
        if (statusBadge) {
            statusBadge.className = `poll-status status-${pollData.status}`;
            statusBadge.textContent = pollData.status_label;
        }
        
        // Update actions based on new status
        const actionsContainer = pollItem.querySelector('.poll-actions');
        updatePollActions(actionsContainer, pollId, pollData.status);
    }
}

// Update poll actions based on status
function updatePollActions(container, pollId, status) {
    const baseActions = `<button class="action-btn-sm view-poll" data-poll-id="${pollId}" title="Voir détails">
        <i class="fas fa-eye"></i> Détails
    </button>`;
    
    let specificActions = '';
    
    switch (status) {
        case 'draft':
            specificActions = `
                <button class="action-btn-sm publish-poll" data-poll-id="${pollId}" title="Publier">
                    <i class="fas fa-play"></i> Publier
                </button>
                <button class="action-btn-sm delete-poll" data-poll-id="${pollId}" title="Supprimer">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            `;
            break;
        case 'active':
            specificActions = `
                <button class="action-btn-sm end-poll" data-poll-id="${pollId}" title="Terminer">
                    <i class="fas fa-stop"></i> Terminer
                </button>
            `;
            break;
        case 'ended':
            specificActions = `
                <a href="/dashboard/sondages/${pollId}/export" class="action-btn-sm" title="Exporter">
                    <i class="fas fa-download"></i> Exporter
                </a>
            `;
            break;
    }
    
    container.innerHTML = baseActions + specificActions;
    
    // Rebind event listeners
    bindPollEventListeners(container);
}

// Bind event listeners to poll actions
function bindPollEventListeners(container = document) {
    // View poll details
    container.querySelectorAll('.view-poll').forEach(btn => {
        btn.addEventListener('click', function() {
            const pollId = this.dataset.pollId;
            viewPollDetails(pollId);
        });
    });
    
    // Publish poll
    container.querySelectorAll('.publish-poll').forEach(btn => {
        btn.addEventListener('click', function() {
            const pollId = this.dataset.pollId;
            publishPoll(pollId);
        });
    });
    
    // End poll
    container.querySelectorAll('.end-poll').forEach(btn => {
        btn.addEventListener('click', function() {
            const pollId = this.dataset.pollId;
            endPoll(pollId);
        });
    });
    
    // Delete poll
    container.querySelectorAll('.delete-poll').forEach(btn => {
        btn.addEventListener('click', function() {
            const pollId = this.dataset.pollId;
            deletePoll(pollId);
        });
    });
}

// Update statistics
async function updateStats() {
    try {
        const data = await apiRequest('/dashboard/sondages/stats');
        if (data.success) {
            const stats = data.data;
            document.getElementById('polls-number').textContent = stats.total_polls;
            document.getElementById('responses-number').textContent = stats.total_votes;
            document.getElementById('active-number').textContent = stats.active_polls;
            document.getElementById('engagement-number').textContent = stats.engagement_rate + '%';
            
            // Update notification badge
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                badge.textContent = stats.active_polls;
            }
        }
    } catch (error) {
        console.error('Failed to update stats:', error);
    }
}

// Add new poll to list
function addPollToList(poll) {
    // Implementation depends on how you want to handle the UI update
    // For now, just reload the page to show the new poll
    setTimeout(() => {
        window.location.reload();
    }, 1000);
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
document.addEventListener('DOMContentLoaded', function() {
    animateOnLoad();
    setTimeout(animateNumbers, 800);
    
    // Initialize poll creator
    initializePollCreator();
    
    // Load templates
    loadTemplates();
    
    // Bind event listeners
    bindPollEventListeners();
    
    // Handle poll form submission
    const pollForm = document.getElementById('pollCreatorForm');
    if (pollForm) {
        pollForm.addEventListener('submit', handlePollFormSubmission);
    }
});

// Refresh stats periodically (every 5 minutes)
setInterval(updateStats, 300000); // 5 minutes