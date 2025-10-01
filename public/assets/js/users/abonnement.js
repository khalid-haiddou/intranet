// Set CSRF token for all AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Initialize usage circle animation
function initUsageCircle() {
    const circle = document.getElementById('usageCircle');
    if (!circle) return;
    
    const percentage = parseFloat(circle.getAttribute('data-percentage')) || 0;
    const circumference = 314; // 2 * PI * radius (50)
    const offset = circumference - (circumference * percentage / 100);
    
    // Set initial state
    circle.style.strokeDasharray = `${circumference} ${circumference}`;
    circle.style.strokeDashoffset = circumference;
    
    // Animate to target
    setTimeout(() => {
        circle.style.strokeDashoffset = offset;
    }, 500);
}

// Subscription cancellation - AJAX
async function cancelSubscription() {
    if (!confirm('Êtes-vous sûr de vouloir annuler votre abonnement ? Cette action prendra effet à la fin de votre période de facturation actuelle.')) {
        return;
    }
    
    try {
        const response = await fetch('/user/abonnement/cancel', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessAlert(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showErrorAlert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorAlert('Une erreur est survenue lors de l\'annulation');
    }
}

// Success alert
function showSuccessAlert(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; animation: slideIn 0.3s ease-out; min-width: 300px;';
    alert.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// Error alert
function showErrorAlert(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger';
    alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; animation: slideIn 0.3s ease-out; min-width: 300px;';
    alert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${message}`;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => alert.remove(), 300);
    }, 4000);
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
        if (!this.getAttribute('href') || this.getAttribute('href') === '#') {
            e.preventDefault();
        }
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

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .alert {
        border-radius: 12px;
        border: none;
        padding: 15px 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
`;
document.head.appendChild(style);

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    animateOnLoad();
    initUsageCircle();
});