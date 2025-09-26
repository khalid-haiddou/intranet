<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interne - Coworking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin/messaging.css') }}">
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
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-wallet"></i> Finances
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-users"></i> Membres
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-door-open"></i> Espaces
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-calendar-check"></i> Réservations
            </a>
            <a href="#" class="nav-link active">
                <i class="fas fa-comments"></i> Chat Interne
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-poll"></i> Sondages
            </a>
            <a href="#" class="nav-link ">
                <i class="fas fa-calendar-star"></i> Événements
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-cog"></i> Déconnexion
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header Section -->
        <div class="header-section loading">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="welcome-text">
                        <h2>Chat Interne</h2>
                        <p><i class="fas fa-calendar-alt me-2"></i><span id="current-date"></span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-info justify-content-end d-flex">
                        <button class="notification-btn me-3">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <div class="user-avatar">
                            MA
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid loading">
            <div class="stats-card messages">
                <div class="stats-header">
                    <div class="stats-icon messages">
                        <i class="fas fa-comment"></i>
                    </div>
                </div>
                <div class="stats-number" id="messages-number">1,247</div>
                <div class="stats-label">Messages aujourd'hui</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-trending-up"></i>
                    <span>+18% vs hier</span>
                </div>
            </div>
            
            <div class="stats-card online">
                <div class="stats-header">
                    <div class="stats-icon online">
                        <i class="fas fa-circle"></i>
                    </div>
                </div>
                <div class="stats-number" id="online-number">23</div>
                <div class="stats-label">Membres en ligne</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-user-check"></i>
                    <span>Activité élevée</span>
                </div>
            </div>
            
            <div class="stats-card channels">
                <div class="stats-header">
                    <div class="stats-icon channels">
                        <i class="fas fa-hashtag"></i>
                    </div>
                </div>
                <div class="stats-number" id="channels-number">8</div>
                <div class="stats-label">Canaux actifs</div>
                <div class="stats-trend trend-neutral">
                    <i class="fas fa-comments"></i>
                    <span>Discussions ouvertes</span>
                </div>
            </div>
            
            <div class="stats-card files">
                <div class="stats-header">
                    <div class="stats-icon files">
                        <i class="fas fa-paperclip"></i>
                    </div>
                </div>
                <div class="stats-number" id="files-number">42</div>
                <div class="stats-label">Fichiers partagés</div>
                <div class="stats-trend trend-positive">
                    <i class="fas fa-upload"></i>
                    <span>+6 cette semaine</span>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="chat-container loading">
            <!-- Chat Sidebar - Channels -->
            <div class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h5><i class="fas fa-hashtag"></i> Canaux</h5>
                </div>
                <div class="channels-list">
                    <div class="channel-item active">
                        <div class="channel-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="channel-info">
                            <h6># général</h6>
                            <small>Discussions générales</small>
                        </div>
                        <span class="unread-badge">3</span>
                    </div>
                    
                    <div class="channel-item">
                        <div class="channel-icon" style="background: rgba(39, 174, 96, 0.1); color: var(--success-color);">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="channel-info">
                            <h6># networking</h6>
                            <small>Événements & rencontres</small>
                        </div>
                    </div>
                    
                    <div class="channel-item">
                        <div class="channel-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--warning-color);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="channel-info">
                            <h6># idées</h6>
                            <small>Innovations & projets</small>
                        </div>
                        <span class="unread-badge">1</span>
                    </div>
                    
                    <div class="channel-item">
                        <div class="channel-icon" style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="channel-info">
                            <h6># support</h6>
                            <small>Aide technique</small>
                        </div>
                    </div>
                    
                    <div class="channel-item">
                        <div class="channel-icon" style="background: rgba(155, 89, 182, 0.1); color: #9B59B6;">
                            <i class="fas fa-coffee"></i>
                        </div>
                        <div class="channel-info">
                            <h6># pause-café</h6>
                            <small>Détente & casual</small>
                        </div>
                    </div>

                    <hr style="margin: 15px 0; opacity: 0.3;">
                    <small style="padding: 0 15px; color: #7f8c8d; font-weight: 600;">Messages Privés</small>
                    
                    <div class="channel-item">
                        <div class="user-avatar-small">
                            <span>MD</span>
                            <div class="status-indicator status-online"></div>
                        </div>
                        <div class="channel-info">
                            <h6>Soukaina Farid</h6>
                            <small>En ligne</small>
                        </div>
                        <span class="unread-badge">2</span>
                    </div>
                    
                    <div class="channel-item">
                        <div class="user-avatar-small">
                            <span>JM</span>
                            <div class="status-indicator status-away"></div>
                        </div>
                        <div class="channel-info">
                            <h6>Jean Martin</h6>
                            <small>Absent</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="chat-main">
                <div class="chat-header">
                    <div class="chat-title">
                        <div class="channel-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div>
                            <h5># général</h5>
                            <small>23 membres • 12 en ligne <span class="online-indicator"></span></small>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="chat-btn" title="Informations du canal">
                            <i class="fas fa-info"></i>
                        </button>
                        <button class="chat-btn" title="Rechercher">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="chat-btn" title="Plus d'options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <div class="message">
                        <div class="message-avatar">MD</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-author">Marie Dupont</span>
                                <span class="message-time">09:15</span>
                            </div>
                            <div class="message-text">
                                Bonjour à tous ! Est-ce que quelqu'un sait si la salle de réunion A est libre cet après-midi ?
                            </div>
                        </div>
                    </div>

                    <div class="message">
                        <div class="message-avatar" style="background: linear-gradient(135deg, #3498DB, #2980B9);">JM</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-author">Jean Martin</span>
                                <span class="message-time">09:18</span>
                            </div>
                            <div class="message-text">
                                Salut Marie ! Je viens de vérifier, elle est libre à partir de 14h. Tu peux la réserver via l'app 👍
                            </div>
                        </div>
                    </div>

                    <div class="message own">
                        <div class="message-avatar">MA</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-author">Moi</span>
                                <span class="message-time">09:20</span>
                            </div>
                            <div class="message-text">
                                N'oubliez pas le networking breakfast demain à 8h30 ! 🥐☕
                            </div>
                        </div>
                    </div>

                    <div class="message">
                        <div class="message-avatar" style="background: linear-gradient(135deg, #E74C3C, #C0392B);">AB</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-author">Ahmed Benali</span>
                                <span class="message-time">09:25</span>
                            </div>
                            <div class="message-text">
                                Super idée ! Je serai là. Quelqu'un veut co-travailler sur un projet blockchain après ? 🚀
                            </div>
                        </div>
                    </div>

                    <div class="message">
                        <div class="message-avatar" style="background: linear-gradient(135deg, #9B59B6, #8E44AD);">SC</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-author">Sarah Cohen</span>
                                <span class="message-time">09:28</span>
                            </div>
                            <div class="message-text">
                                @Ahmed ça m'intéresse ! J'ai de l'expérience en smart contracts. On peut discuter demain ?
                            </div>
                        </div>
                    </div>

                    <div class="typing-indicator">
                        <span>Marie Dupont est en train d'écrire</span>
                        <div class="typing-dots">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                </div>

                <div class="message-input-container">
                    <div class="message-input-wrapper">
                        <input type="text" class="message-input" placeholder="Écrivez votre message dans # général..." id="messageInput">
                        <div class="input-actions">
                            <button class="input-btn" title="Emoji">
                                <i class="fas fa-smile"></i>
                            </button>
                            <button class="input-btn" title="Ajouter un fichier">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <button class="input-btn" title="Mentions">
                                <i class="fas fa-at"></i>
                            </button>
                            <button class="input-btn send-btn" title="Envoyer" id="sendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Online -->
            <div class="users-online">
                <div class="users-header">
                    <h5><i class="fas fa-users"></i> En ligne (23)</h5>
                </div>
                <div class="users-list">
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <span>MD</span>
                            <div class="status-indicator status-online"></div>
                        </div>
                        <div class="user-info">
                            <h6>Marie Dupont</h6>
                            <small>Designer UX</small>
                        </div>
                    </div>
                    
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <span>JM</span>
                            <div class="status-indicator status-away"></div>
                        </div>
                        <div class="user-info">
                            <h6>Jean Martin</h6>
                            <small>Développeur</small>
                        </div>
                    </div>
                    
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <span>AB</span>
                            <div class="status-indicator status-online"></div>
                        </div>
                        <div class="user-info">
                            <h6>Ahmed Benali</h6>
                            <small>Blockchain Dev</small>
                        </div>
                    </div>
                    
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <span>SC</span>
                            <div class="status-indicator status-online"></div>
                        </div>
                        <div class="user-info">
                            <h6>Sarah Cohen</h6>
                            <small>Smart Contracts</small>
                        </div>
                    </div>
                    
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <span>LR</span>
                            <div class="status-indicator status-busy"></div>
                        </div>
                        <div class="user-info">
                            <h6>Laila Rachid</h6>
                            <small>Marketing Digital</small>
                        </div>
                    </div>
                    
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <span>KA</span>
                            <div class="status-indicator status-online"></div>
                        </div>
                        <div class="user-info">
                            <h6>Karim Alami</h6>
                            <small>Data Scientist</small>
                        </div>
                    </div>
                    
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <span>TC</span>
                            <div class="status-indicator status-away"></div>
                        </div>
                        <div class="user-info">
                            <h6>TechCorp</h6>
                            <small>Startup</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/admin/messaging.js') }}"></script>
</body>
</html>