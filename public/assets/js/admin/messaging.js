        // Date actuelle
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Animation des chiffres
        function animateNumbers() {
            const messages = document.getElementById('messages-number');
            const online = document.getElementById('online-number');
            const channels = document.getElementById('channels-number');
            const files = document.getElementById('files-number');

            animateValue(messages, 0, 1247, 1500);
            animateValue(online, 0, 23, 1200);
            animateValue(channels, 0, 8, 1000);
            animateValue(files, 0, 42, 1800);
        }

        function animateValue(element, start, end, duration, suffix = '') {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const currentValue = Math.floor(progress * (end - start) + start);
                element.textContent = currentValue.toLocaleString('fr-FR') + suffix;
                
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Chat functionality
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const chatMessages = document.getElementById('chatMessages');

        function sendMessage() {
            const message = messageInput.value.trim();
            if (message) {
                const time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message own';
                messageDiv.innerHTML = `
                    <div class="message-avatar">MA</div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-author">Moi</span>
                            <span class="message-time">${time}</span>
                        </div>
                        <div class="message-text">${message}</div>
                    </div>
                `;
                
                // Remove typing indicator
                const typingIndicator = chatMessages.querySelector('.typing-indicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
                
                chatMessages.appendChild(messageDiv);
                messageInput.value = '';
                
                // Auto-scroll to bottom
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Simulate response after 2 seconds
                setTimeout(() => {
                    simulateResponse();
                }, 2000);
            }
        }

        function simulateResponse() {
            const responses = [
                { author: 'Marie Dupont', avatar: 'MD', text: 'Merci pour l\'info ! 😊', color: 'linear-gradient(135deg, var(--primary-color), var(--primary-dark))' },
                { author: 'Jean Martin', avatar: 'JM', text: 'De rien ! N\'hésitez pas si vous avez d\'autres questions.', color: 'linear-gradient(135deg, #3498DB, #2980B9)' },
                { author: 'Ahmed Benali', avatar: 'AB', text: 'Super discussion ! 🚀', color: 'linear-gradient(135deg, #E74C3C, #C0392B)' }
            ];
            
            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            const time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message';
            messageDiv.innerHTML = `
                <div class="message-avatar" style="background: ${randomResponse.color};">${randomResponse.avatar}</div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-author">${randomResponse.author}</span>
                        <span class="message-time">${time}</span>
                    </div>
                    <div class="message-text">${randomResponse.text}</div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Channel switching
        document.querySelectorAll('.channel-item').forEach(channel => {
            channel.addEventListener('click', function() {
                // Remove active class from all channels
                document.querySelectorAll('.channel-item').forEach(c => c.classList.remove('active'));
                // Add active class to clicked channel
                this.classList.add('active');
                
                // Update chat header
                const channelName = this.querySelector('h6').textContent;
                document.querySelector('.chat-title h5').textContent = channelName;
                
                // Update input placeholder
                messageInput.placeholder = `Écrivez votre message dans ${channelName}...`;
                
                // Remove unread badge
                const badge = this.querySelector('.unread-badge');
                if (badge) {
                    badge.remove();
                }
            });
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

        // Auto-scroll chat to bottom on load
        document.addEventListener('DOMContentLoaded', function() {
            animateOnLoad();
            setTimeout(animateNumbers, 800);
            setTimeout(() => {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 1000);
        });

        // Simulate real-time updates
        setInterval(() => {
            // Update online count randomly
            const onlineElement = document.getElementById('online-number');
            const currentOnline = parseInt(onlineElement.textContent);
            const newOnline = Math.max(15, Math.min(30, currentOnline + Math.floor(Math.random() * 3) - 1));
            onlineElement.textContent = newOnline;
            
            // Update messages count
            const messagesElement = document.getElementById('messages-number');
            const currentMessages = parseInt(messagesElement.textContent.replace(',', ''));
            messagesElement.textContent = (currentMessages + Math.floor(Math.random() * 3)).toLocaleString('fr-FR');
        }, 30000);
   