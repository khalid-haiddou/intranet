<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - La Station Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/users/Messagerie.css') }}">
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-circle" style="width: 200px; height: 200px; top: 10%; left: 5%; animation-delay: 0s;"></div>
        <div class="floating-circle" style="width: 150px; height: 150px; top: 60%; right: 8%; animation-delay: 2s;"></div>
        <div class="floating-circle" style="width: 100px; height: 100px; top: 40%; left: 70%; animation-delay: 4s;"></div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="logo-text">
                    <h3>La Station</h3>
                    <small>Co-working Space</small>
                </div>
            </div>
        </div>
        <nav class="sidebar-menu">
            <a href="#" class="nav-link">
                <i class="fas fa-home"></i> Mon Espace
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-check"></i> Réservations
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-user"></i> Mon Profil
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-credit-card"></i> Mon Abonnement
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-star"></i> Événements
            </a>
            <a href="#" class="nav-link active">
                <i class="fas fa-comments"></i> Messages
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-headset"></i> Support
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Conversations Sidebar -->
        <div class="conversations-sidebar loading">
            <div class="conversations-header">
                <div class="conversations-title">
                    <h4>Messages</h4>
                    <button class="new-message-btn" onclick="newMessage()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Rechercher une conversation..." id="searchConversations">
                </div>
            </div>

            <div class="conversations-list">
                <!-- Active conversation -->
                <div class="conversation-item active" onclick="selectConversation(this, 'sarah-alami')">
                    <div class="conversation-avatar" style="background: linear-gradient(135deg, var(--info-color), #2980b9);">
                        SA
                        <div class="online-indicator"></div>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name">Sarah Alami</span>
                            <span class="conversation-time">14:32</span>
                        </div>
                        <div class="conversation-preview">Parfait ! On se retrouve demain à 14h en salle B</div>
                    </div>
                </div>

                <!-- Group conversation -->
                <div class="conversation-item" onclick="selectConversation(this, 'group-marketing')">
                    <div class="conversation-avatar group-avatar">
                        MK
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name">Équipe Marketing</span>
                            <span class="conversation-time">13:45</span>
                        </div>
                        <div class="conversation-preview">Laila: J'ai préparé la présentation pour demain</div>
                    </div>
                    <div class="unread-badge">3</div>
                </div>

                <!-- Other conversations -->
                <div class="conversation-item" onclick="selectConversation(this, 'ahmed-benali')">
                    <div class="conversation-avatar" style="background: linear-gradient(135deg, var(--success-color), #2ecc71);">
                        AB
                        <div class="online-indicator"></div>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name">Ahmed Benali</span>
                            <span class="conversation-time">12:20</span>
                        </div>
                        <div class="conversation-preview">Merci pour les conseils sur le projet !</div>
                    </div>
                </div>

                <div class="conversation-item" onclick="selectConversation(this, 'fatima-zahra')">
                    <div class="conversation-avatar" style="background: linear-gradient(135deg, var(--warning-color), #e67e22);">
                        FZ
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name">Fatima Zahra</span>
                            <span class="conversation-time">11:15</span>
                        </div>
                        <div class="conversation-preview">Est-ce que tu peux m'aider avec Python ?</div>
                    </div>
                    <div class="unread-badge">1</div>
                </div>

                <div class="conversation-item" onclick="selectConversation(this, 'karim-rachid')">
                    <div class="conversation-avatar" style="background: linear-gradient(135deg, #9B59B6, #8e44ad);">
                        KR
                        <div class="online-indicator"></div>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name">Karim Rachid</span>
                            <span class="conversation-time">10:30</span>
                        </div>
                        <div class="conversation-preview">Tu as vu l'annonce pour le nouveau workshop ?</div>
                    </div>
                </div>

                <div class="conversation-item" onclick="selectConversation(this, 'omar-idrissi')">
                    <div class="conversation-avatar" style="background: linear-gradient(135deg, var(--danger-color), #c0392b);">
                        OI
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name">Omar Idrissi</span>
                            <span class="conversation-time">Hier</span>
                        </div>
                        <div class="conversation-preview">À bientôt pour le networking !</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area loading">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-user-info">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, var(--info-color), #2980b9);">
                        SA
                        <div class="online-indicator"></div>
                    </div>
                    <div class="chat-user-details">
                        <h5>Sarah Alami</h5>
                        <div class="chat-user-status">
                            <div class="status-dot"></div>
                            En ligne • Designer UX/UI
                        </div>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="chat-action-btn" onclick="makeCall()" title="Appel vocal">
                        <i class="fas fa-phone"></i>
                    </button>
                    <button class="chat-action-btn" onclick="videoCall()" title="Appel vidéo">
                        <i class="fas fa-video"></i>
                    </button>
                    <button class="chat-action-btn" onclick="chatInfo()" title="Informations">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                <!-- Received message group -->
                <div class="message-group received">
                    <div class="message-bubble received">
                        Salut Marie ! J'espère que tu vas bien. Je voulais te proposer qu'on travaille ensemble sur le projet de refonte du site web.
                    </div>
                    <div class="message-time">Aujourd'hui 13:15</div>
                </div>

                <!-- Sent message group -->
                <div class="message-group sent">
                    <div class="message-bubble sent">
                        Salut Sarah ! Oui ça va très bien merci 😊
                    </div>
                    <div class="message-time">13:16</div>
                </div>

                <div class="message-group sent">
                    <div class="message-bubble sent">
                        C'est une excellente idée ! J'aimerais beaucoup collaborer avec toi sur ce projet.
                    </div>
                    <div class="message-time">13:16</div>
                    <div class="message-status">
                        <i class="fas fa-check-double" style="color: var(--success-color);"></i>
                        Lu
                    </div>
                </div>

                <!-- Received message group -->
                <div class="message-group received">
                    <div class="message-bubble received">
                        Super ! On pourrait se rencontrer demain pour discuter des détails ? Je suis libre après 14h.
                    </div>
                    <div class="message-time">13:20</div>
                </div>

                <div class="message-group sent">
                    <div class="message-bubble sent">
                        Parfait ! On se retrouve demain à 14h en salle B
                    </div>
                    <div class="message-time">14:32</div>
                    <div class="message-status">
                        <i class="fas fa-check-double" style="color: var(--success-color);"></i>
                        Lu
                    </div>
                </div>
            </div>

            <!-- Typing Indicator -->
            <div class="typing-indicator" id="typingIndicator" style="display: none;">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
                <span>Sarah est en train d'écrire...</span>
            </div>

            <!-- Message Input -->
            <div class="message-input-container">
                <div class="message-input-wrapper">
                    <div class="message-attachments">
                        <button class="attachment-btn" onclick="attachFile()" title="Fichier">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button class="attachment-btn" onclick="attachImage()" title="Image">
                            <i class="fas fa-image"></i>
                        </button>
                        <button class="attachment-btn" onclick="attachEmoji()" title="Emoji">
                            <i class="fas fa-smile"></i>
                        </button>
                    </div>
                    <textarea class="message-input" placeholder="Tapez votre message..." id="messageInput" rows="1"></textarea>
                    <button class="send-btn" onclick="sendMessage()" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/users/Messagerie.js') }}"></script>
</body>
</html>