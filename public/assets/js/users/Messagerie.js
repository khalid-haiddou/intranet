        let currentConversation = 'sarah-alami';

        // Message input auto-resize
        const messageInput = document.getElementById('messageInput');
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            
            // Enable/disable send button
            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = !this.value.trim();
        });

        // Send message on Enter
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Conversation selection
        function selectConversation(element, conversationId) {
            // Update active conversation
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');
            
            // Remove unread badge
            const badge = element.querySelector('.unread-badge');
            if (badge) {
                badge.remove();
            }
            
            currentConversation = conversationId;
            loadConversation(conversationId);
        }

        function loadConversation(conversationId) {
            // Mock conversation data
            const conversations = {
                'sarah-alami': {
                    name: 'Sarah Alami',
                    status: 'En ligne • Designer UX/UI',
                    avatar: 'SA',
                    avatarColor: 'linear-gradient(135deg, var(--info-color), #2980b9)',
                    online: true
                },
                'group-marketing': {
                    name: 'Équipe Marketing',
                    status: '5 membres • Groupe',
                    avatar: 'MK',
                    avatarColor: 'linear-gradient(135deg, #9B59B6, #8e44ad)',
                    online: false,
                    isGroup: true
                },
                'ahmed-benali': {
                    name: 'Ahmed Benali',
                    status: 'En ligne • Développeur Full Stack',
                    avatar: 'AB',
                    avatarColor: 'linear-gradient(135deg, var(--success-color), #2ecc71)',
                    online: true
                }
            };

            const conversation = conversations[conversationId] || conversations['sarah-alami'];
            
            // Update chat header
            updateChatHeader(conversation);
            
            // Load messages (mock)
            loadMessages(conversationId);
        }

        function updateChatHeader(conversation) {
            const chatAvatar = document.querySelector('.chat-avatar');
            const chatName = document.querySelector('.chat-user-details h5');
            const chatStatus = document.querySelector('.chat-user-status');
            
            chatAvatar.style.background = conversation.avatarColor;
            chatAvatar.textContent = conversation.avatar;
            chatName.textContent = conversation.name;
            chatStatus.innerHTML = conversation.online ? 
                `<div class="status-dot"></div>${conversation.status}` : 
                conversation.status;
            
            // Update online indicator
            const onlineIndicator = chatAvatar.querySelector('.online-indicator');
            if (conversation.online && !onlineIndicator) {
                const indicator = document.createElement('div');
                indicator.className = 'online-indicator';
                chatAvatar.appendChild(indicator);
            } else if (!conversation.online && onlineIndicator) {
                onlineIndicator.remove();
            }
        }

        function loadMessages(conversationId) {
            // Mock messages for different conversations
            const mockMessages = {
                'ahmed-benali': `
                    <div class="message-group received">
                        <div class="message-bubble received">
                            Salut Marie ! J'ai une question sur React. Tu as un moment ?
                        </div>
                        <div class="message-time">Aujourd'hui 12:15</div>
                    </div>
                    <div class="message-group sent">
                        <div class="message-bubble sent">
                            Bien sûr ! Qu'est-ce qui te pose problème ?
                        </div>
                        <div class="message-time">12:16</div>
                    </div>
                    <div class="message-group received">
                        <div class="message-bubble received">
                            Merci pour les conseils sur le projet !
                        </div>
                        <div class="message-time">12:20</div>
                    </div>
                `,
                'group-marketing': `
                    <div class="message-group received">
                        <div class="message-bubble received">
                            <strong>Laila:</strong> Salut l'équipe ! On fait le point sur la campagne ?
                        </div>
                        <div class="message-time">Aujourd'hui 13:30</div>
                    </div>
                    <div class="message-group received">
                        <div class="message-bubble received">
                            <strong>Karim:</strong> Oui parfait ! Les résultats sont encourageants
                        </div>
                        <div class="message-time">13:32</div>
                    </div>
                    <div class="message-group received">
                        <div class="message-bubble received">
                            <strong>Laila:</strong> J'ai préparé la présentation pour demain
                        </div>
                        <div class="message-time">13:45</div>
                    </div>
                `
            };

            const chatMessages = document.getElementById('chatMessages');
            if (mockMessages[conversationId]) {
                chatMessages.innerHTML = mockMessages[conversationId];
            }
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Send message
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Add message to chat
            addMessage(message, true);
            
            // Clear input
            input.value = '';
            input.style.height = 'auto';
            document.getElementById('sendBtn').disabled = true;
            
            // Simulate typing indicator and response
            setTimeout(() => {
                showTypingIndicator();
                setTimeout(() => {
                    hideTypingIndicator();
                    simulateResponse();
                }, 2000);
            }, 500);
        }

        function addMessage(text, isSent) {
            const chatMessages = document.getElementById('chatMessages');
            const messageGroup = document.createElement('div');
            messageGroup.className = `message-group ${isSent ? 'sent' : 'received'}`;
            
            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            messageGroup.innerHTML = `
                <div class="message-bubble ${isSent ? 'sent' : 'received'}">
                    ${text}
                </div>
                <div class="message-time">${timeString}</div>
                ${isSent ? `
                <div class="message-status">
                    <i class="fas fa-check"></i>
                    Envoyé
                </div>` : ''}
            `;
            
            chatMessages.appendChild(messageGroup);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTypingIndicator() {
            document.getElementById('typingIndicator').style.display = 'flex';
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTypingIndicator() {
            document.getElementById('typingIndicator').style.display = 'none';
        }

        function simulateResponse() {
            const responses = [
                "C'est une excellente idée !",
                "Je vais regarder ça et je te reviens.",
                "Merci pour l'info !",
                "On peut en discuter de vive voix si tu veux.",
                "Parfait, j'ai noté."
            ];
            
            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            addMessage(randomResponse, false);
        }

        // Attachment functions
        function attachFile() {
            alert('Sélectionner un fichier à joindre');
        }

        function attachImage() {
            alert('Sélectionner une image à joindre');
        }

        function attachEmoji() {
            // Simple emoji insertion
            const emojis = ['😊', '👍', '❤️', '😂', '🔥', '💪', '🎉', '👏'];
            const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
            
            const input = document.getElementById('messageInput');
            input.value += randomEmoji;
            input.focus();
            
            // Trigger input event to resize
            input.dispatchEvent(new Event('input'));
        }

        // Chat actions
        function makeCall() {
            alert('Démarrage de l\'appel vocal...');
        }

        function videoCall() {
            alert('Démarrage de l\'appel vidéo...');
        }

        function chatInfo() {
            alert('Informations sur la conversation');
        }

        function newMessage() {
            alert('Nouvelle conversation');
        }

        // Search functionality
        document.getElementById('searchConversations').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const conversations = document.querySelectorAll('.conversation-item');
            
            conversations.forEach(conv => {
                const name = conv.querySelector('.conversation-name').textContent.toLowerCase();
                const preview = conv.querySelector('.conversation-preview').textContent.toLowerCase();
                
                if (name.includes(query) || preview.includes(query)) {
                    conv.style.display = 'flex';
                } else {
                    conv.style.display = 'none';
                }
            });
        });

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
            const conversationsSidebar = document.querySelector('.conversations-sidebar');
            const toggleBtn = document.createElement('button');
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            toggleBtn.className = 'btn-primary position-fixed';
            toggleBtn.style.cssText = 'top: 20px; left: 20px; z-index: 1001; width: 50px; height: 50px; border-radius: 50%;';
            document.body.appendChild(toggleBtn);
            
            toggleBtn.addEventListener('click', function() {
                sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-280px)' : 'translateX(0px)';
            });

            // Toggle conversations sidebar on mobile
            const conversationsToggle = document.createElement('button');
            conversationsToggle.innerHTML = '<i class="fas fa-comments"></i>';
            conversationsToggle.className = 'btn-primary position-fixed';
            conversationsToggle.style.cssText = 'top: 80px; left: 20px; z-index: 1001; width: 50px; height: 50px; border-radius: 50%;';
            document.body.appendChild(conversationsToggle);
            
            conversationsToggle.addEventListener('click', function() {
                conversationsSidebar.classList.toggle('show');
            });
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            animateOnLoad();
            loadConversation(currentConversation);
        });
