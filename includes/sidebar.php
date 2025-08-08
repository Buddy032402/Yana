<div class="sidebar">
    <div class="sidebar-header">
        <!-- Logo container with adjusted styles -->
        <div class="logo-container">
            <img src="../assets/images/logo.png" alt="Yana Logo" class="sidebar-logo">
        </div>
        <div class="company-logo-container">
            <h4 class="company-name">Yana Byahe Na</h4>
            <span class="company-tagline">Travel & Tours</span>
        </div>
        <!-- <button id="sidebarToggle" class="d-lg-none">
            <i class="fas fa-times"></i>
        </button> -->
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt icon-dashboard"></i> <span>Dashboard</span></a>
            </li>
            <!-- Add Visit Website Button -->
            <li>
                <a href="../index.php" class="visit-website-btn">
                    <i class="fas fa-globe"></i> <span>Visit Website</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_destinations.php' ? 'active' : ''; ?>">
                <a href="manage_destinations.php"><i class="fas fa-map-marker-alt icon-destination"></i> <span> Destinations</span></a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_packages.php' ? 'active' : ''; ?>">
                <a href="manage_packages.php"><i class="fas fa-suitcase icon-package"></i> <span>Packages</span></a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_bookings.php' ? 'active' : ''; ?>">
                <a href="manage_bookings.php"><i class="fas fa-calendar-check icon-booking"></i> <span>Bookings</span></a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_inquiries.php' ? 'active' : ''; ?>">
                <a href="manage_inquiries.php"><i class="fas fa-question-circle icon-inquiry"></i> <span> Inquiries</span></a>
            </li>
            <li class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['inbox.php', 'sent_messages.php', 'archived_messages.php', 'compose_message.php']) ? 'active' : ''; ?>">
                <a href="#" class="message-dropdown-toggle">
                    <i class="fas fa-envelope icon-message"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <ul class="message-submenu">
                    <li><a href="inbox.php"><i class="fas fa-inbox"></i> Inbox</a></li>
                    <!-- <li><a href="sent_messages.php"><i class="fas fa-paper-plane"></i> Sent</a></li> -->
                    <li><a href="archived_messages.php"><i class="fas fa-archive"></i> Archive</a></li>
                    <!-- <li><a href="compose_message.php"><i class="fas fa-pen"></i> Compose</a></li> -->
                </ul>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_testimonials.php' ? 'active' : ''; ?>">

<style>
    /* Add these styles for the message submenu */
    .message-submenu {
        display: block;
        list-style: none;
        padding-left: 3.5rem;
        margin-top: 5px;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease-in-out;
    }

    .message-submenu.show {
        max-height: 200px;
        opacity: 1;
        visibility: visible;
        margin-bottom: 10px;
    }

    .message-submenu li a {
        padding: 8px 15px;
        font-size: 0.9rem;
        opacity: 0.9;
        transform: translateX(-10px);
        transition: transform 0.3s ease;
    }

    .message-submenu.show li a {
        transform: translateX(0);
    }

    .message-submenu li a i {
        font-size: 0.8rem;
        width: 16px;
    }

    .message-dropdown-toggle .fa-chevron-down {
        transition: transform 0.3s ease;
    }

    .message-dropdown-toggle.active .fa-chevron-down {
        transform: rotate(180deg);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messageToggle = document.querySelector('.message-dropdown-toggle');
        if (messageToggle) {
            messageToggle.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                this.nextElementSibling.classList.toggle('show');
            });

            // Auto expand if on a message page
            if (window.location.pathname.includes('message') || 
                window.location.pathname.includes('inbox') || 
                window.location.pathname.includes('sent_messages') ||
                window.location.pathname.includes('archived_messages') ||
                window.location.pathname.includes('compose_message')) {
                messageToggle.classList.add('active');
                messageToggle.nextElementSibling.classList.add('show');
            }
        }
    });
</script>
                <a href="manage_testimonials.php"><i class="fas fa-star icon-testimonial"></i> <span>Testimonials</span></a>
            </li>
            <!-- Add Top Client Picks menu item -->
            <li class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['manage_top_picks.php', 'add_top_picks.php', 'edit_top_picks.php']) ? 'active' : ''; ?>">
                <a href="manage_top_picks.php"><i class="fas fa-award icon-top-picks"></i> <span>Top Client Picks</span></a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                    <a href="manage_users.php"><i class="fas fa-users icon-user"></i> <span>Users</span></a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'login_history.php' ? 'active' : ''; ?>">
                    <a href="login_history.php"><i class="fas fa-history icon-history"></i> <span>Login History</span></a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'cleanup_uploads.php' ? 'active' : ''; ?>">
                    <a href="cleanup_uploads.php" onclick="return confirm('Are you sure you want to clean up unused images?');">
                        <i class="fas fa-broom icon-cleanup"></i> <span>Cleanup Uploads</span>
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt icon-logout"></i> <span>Logout</span></a>
            </li>
        </ul>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php?confirm=yes" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<style>
 .sidebar {
    background: linear-gradient(135deg, #1a237e, #283593, #303f9f, #3949ab, #3f51b5);
    color: #fff;
    min-height: 100vh;
    width: 250px;
    position: fixed;
    transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    z-index: 1000;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
}
    
    /* Add this to ensure main content doesn't overlap */
    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        transition: all 0.3s ease;
    }
    
    /* Responsive adjustments */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        opacity: 0;
        visibility: hidden;
    }
    
    .sidebar.active {
        transform: translateX(0);
        opacity: 1;
        visibility: visible;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
    }
    
    .main-content {
        margin-left: 0;
        width: 100%;
        transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    }
    .sidebar::-webkit-scrollbar {
        width: 5px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }
    
    .sidebar-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.1);
    }
    
    .sidebar-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    #sidebarToggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    
    #sidebarToggle:hover {
        transform: rotate(90deg);
    }
    
    .sidebar-menu ul {
        list-style: none;
        padding: 10px;
        margin: 0;
    }
    
    .sidebar-menu li {
        padding: 0;
        margin: 8px 0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .sidebar-menu li a {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px;
        border-left: 3px solid transparent;
    }
    
    .sidebar-menu li a:hover {
        background-color: rgba(255, 255, 255, 0.15);
        color: #fff;
        transform: translateX(5px);
    }
    
    .sidebar-menu li.active a {
        background: linear-gradient(to right, rgba(26, 35, 126, 0.9), rgba(63, 81, 181, 0.7));
        color: #fff;
        border-left: 3px solid #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .sidebar-menu li a i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.2));
    }
    
    .sidebar-menu li a:hover i {
        transform: translateX(3px) scale(1.1);
    }
    
    .sidebar-menu li a span {
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    /* Icon colors */
    .icon-dashboard { color: #4fc3f7; }
    .icon-destination { color: #ff8a65; }
    .icon-package { color: #81c784; }
    .icon-booking { color: #ba68c8; }
    .icon-inquiry { color: #ffb74d; }
    .icon-message { color: #64b5f6; }
    .icon-testimonial { color: #ffd54f; }
    .icon-user { color: #7986cb; }
    .icon-history { color: #78909c; }
    .icon-setting { color: #9e9e9e; }
    
    /* Active state icon colors */
    .sidebar-menu li.active .icon-history { color: #90a4ae; }
    .sidebar-menu li.active .icon-destination { color: #ffab91; }
    .sidebar-menu li.active .icon-package { color: #a5d6a7; }
    .sidebar-menu li.active .icon-booking { color: #ce93d8; }
    .sidebar-menu li.active .icon-inquiry { color: #ffe082; }
    .sidebar-menu li.active .icon-message { color: #90caf9; }
    .sidebar-menu li.active .icon-testimonial { color: #fff176; }
    .sidebar-menu li.active .icon-user { color: #9fa8da; }
    .sidebar-menu li.active .icon-setting { color: #e0e0e0; }
    .sidebar-menu li.active .icon-logout { color: #ef9a9a; }
    
    /* Dark theme adjustments */
    [data-theme="dark"] .sidebar {
        background: linear-gradient(135deg, #0d1642, #162168, #1a237e, #283593, #303f9f);
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .sidebar {
            margin-left: -250px;
            box-shadow: none;
        }
        
        .sidebar.active {
            margin-left: 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
    }
    
    /* Animation for menu items */
    .sidebar-menu li {
        opacity: 0;
        transform: translateX(-20px);
        animation: fadeInRight 0.5s ease forwards;
    }
    
    @keyframes fadeInRight {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .sidebar-menu li:nth-child(1) { animation-delay: 0.1s; }
    .sidebar-menu li:nth-child(2) { animation-delay: 0.2s; }
    .sidebar-menu li:nth-child(3) { animation-delay: 0.3s; }
    .sidebar-menu li:nth-child(4) { animation-delay: 0.4s; }
    .sidebar-menu li:nth-child(5) { animation-delay: 0.5s; }
    .sidebar-menu li:nth-child(6) { animation-delay: 0.6s; }
    .sidebar-menu li:nth-child(7) { animation-delay: 0.7s; }
    .sidebar-menu li:nth-child(8) { animation-delay: 0.8s; }
    .sidebar-menu li:nth-child(9) { animation-delay: 0.9s; }
    .sidebar-menu li:nth-child(10) { animation-delay: 1.0s; }
    
    /* Glow effect for active item */
    .sidebar-menu li.active a {
        position: relative;
    }
    
    .sidebar-menu li.active a::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        animation: glow 2s infinite alternate;
        pointer-events: none;
    }
    
    @keyframes glow {
        from {
            opacity: 0.5;
        }
        to {
            opacity: 0.8;
        }
    }
</style>

<style>
    /* Styles for the Visit Website button */
    .visit-website-btn {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px;
        border-left: 3px solid transparent;
    }

    .visit-website-btn:hover {
        background-color: rgba(255, 255, 255, 0.15);
        color: #fff;
        transform: translateX(5px);
    }

    .visit-website-btn i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
        color: #FFD700; /* Set the icon color to gold */
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
                this.querySelector('i').classList.toggle('fa-times');
                this.querySelector('i').classList.toggle('fa-bars');
            });
        }
        
        // Handle mobile menu button
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        }
        
        // Add hover effect for menu items
        const menuItems = document.querySelectorAll('.sidebar-menu li a');
        menuItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.querySelector('i').classList.add('animated');
            });
            
            item.addEventListener('mouseleave', function() {
                this.querySelector('i').classList.remove('animated');
            });
        });
        
        // Add ripple effect to menu items
        function createRipple(event) {
            const button = event.currentTarget;
            
            const circle = document.createElement('span');
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;
            
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
            circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
            circle.classList.add('ripple');
            
            const ripple = button.getElementsByClassName('ripple')[0];
            
            if (ripple) {
                ripple.remove();
            }
            
            button.appendChild(circle);
        }
        
        const buttons = document.querySelectorAll('.sidebar-menu li a');
        buttons.forEach(button => {
            button.addEventListener('click', createRipple);
        });
        
        // Logout confirmation
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
                logoutModal.show();
            });
        }
    });
</script>

<style>
    /* Ripple effect */
    .sidebar-menu li a {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.4);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    /* Icon animation */
    .sidebar-menu li a i.animated {
        animation: pulse 0.5s ease;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
</style>

<style>
    /* Logout modal styling */
    #logoutModal .modal-content {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    #logoutModal .modal-header {
        background: linear-gradient(to right, #3949ab, #5c6bc0);
        color: white;
        border-bottom: none;
        border-radius: 10px 10px 0 0;
    }
    
    #logoutModal .btn-close {
        color: white;
        filter: invert(1) brightness(200%);
    }
    
    #logoutModal .modal-footer {
        border-top: none;
    }
    
    #logoutModal .btn-danger {
        background: linear-gradient(to right, #f44336, #e57373);
        border: none;
        transition: all 0.3s ease;
    }
    
    #logoutModal .btn-danger:hover {
        background: linear-gradient(to right, #d32f2f, #ef5350);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    #logoutModal .btn-secondary {
        background: #757575;
        border: none;
        transition: all 0.3s ease;
    }
    
    #logoutModal .btn-secondary:hover {
        background: #616161;
        transform: translateY(-2px);
    }
</style>

<style>
    /* Add these styles for the company name section */
    .company-logo-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .company-name {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        background: linear-gradient(to right, #ffffff, #e0e0e0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        position: relative;
    }
    
    .company-tagline {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        margin-top: -2px;
        font-style: italic;
        letter-spacing: 0.5px;
    }
    
    /* Add a subtle animation */
    @keyframes glow-company {
        0% { text-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
        50% { text-shadow: 0 0 15px rgba(255, 255, 255, 0.8); }
        100% { text-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
    }
    
    .company-name {
        animation: glow-company 3s infinite;
    }
    
    <a href="cleanup_uploads.php" class="nav-link" onclick="return confirm('Are you sure you want to clean up unused images?');">
    <i class="fas fa-broom"></i>
    <span>Cleanup Uploads</span>
    </a>
    
    /* Existing sidebar styles continue below */
    .sidebar {
        background: linear-gradient(135deg, #1a237e, #283593, #303f9f, #3949ab, #3f51b5);
        color: #fff;
        min-height: 100vh;
        width: 250px;
        position: fixed;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
    }
    
    /* Add this to ensure main content doesn't overlap */
    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        transition: all 0.3s ease;
    }
    
    /* Adjust responsive behavior */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-250px);
            margin-left: 0;
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
            width: 100%;
        }
    }
    .sidebar::-webkit-scrollbar {
        width: 5px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }
    
    .sidebar-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.1);
    }
    
    .sidebar-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    #sidebarToggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    
    #sidebarToggle:hover {
        transform: rotate(90deg);
    }
    
    .sidebar-menu ul {
        list-style: none;
        padding: 10px;
        margin: 0;
    }
    
    .sidebar-menu li {
        padding: 0;
        margin: 8px 0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .sidebar-menu li a {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px;
        border-left: 3px solid transparent;
    }
    
    .sidebar-menu li a:hover {
        background-color: rgba(255, 255, 255, 0.15);
        color: #fff;
        transform: translateX(5px);
    }
    
    .sidebar-menu li.active a {
        background: linear-gradient(to right, rgba(26, 35, 126, 0.9), rgba(63, 81, 181, 0.7));
        color: #fff;
        border-left: 3px solid #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .sidebar-menu li a i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.2));
    }
    
    .sidebar-menu li a:hover i {
        transform: translateX(3px) scale(1.1);
    }
    
    .sidebar-menu li a span {
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    /* Icon colors */
    .icon-dashboard { color: #4fc3f7; }
    .icon-destination { color: #ff8a65; }
    .icon-package { color: #81c784; }
    .icon-booking { color: #ba68c8; }
    .icon-inquiry { color: #ffb74d; }
    .icon-message { color: #64b5f6; }
    .icon-testimonial { color: #ffd54f; }
    .icon-user { color: #7986cb; }
    .icon-history { color: #78909c; }
    .icon-setting { color: #9e9e9e; }
    
    /* Active state icon colors */
    .sidebar-menu li.active .icon-history { color: #90a4ae; }
    .sidebar-menu li.active .icon-destination { color: #ffab91; }
    .sidebar-menu li.active .icon-package { color: #a5d6a7; }
    .sidebar-menu li.active .icon-booking { color: #ce93d8; }
    .sidebar-menu li.active .icon-inquiry { color: #ffe082; }
    .sidebar-menu li.active .icon-message { color: #90caf9; }
    .sidebar-menu li.active .icon-testimonial { color: #fff176; }
    .sidebar-menu li.active .icon-user { color: #9fa8da; }
    .sidebar-menu li.active .icon-setting { color: #e0e0e0; }
    .sidebar-menu li.active .icon-logout { color: #ef9a9a; }
    
    /* Dark theme adjustments */
    [data-theme="dark"] .sidebar {
        background: linear-gradient(135deg, #0d1642, #162168, #1a237e, #283593, #303f9f);
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .sidebar {
            margin-left: -250px;
            box-shadow: none;
        }
        
        .sidebar.active {
            margin-left: 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
    }
    
    /* Animation for menu items */
    .sidebar-menu li {
        opacity: 0;
        transform: translateX(-20px);
        animation: fadeInRight 0.5s ease forwards;
    }
    
    @keyframes fadeInRight {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .sidebar-menu li:nth-child(1) { animation-delay: 0.1s; }
    .sidebar-menu li:nth-child(2) { animation-delay: 0.2s; }
    .sidebar-menu li:nth-child(3) { animation-delay: 0.3s; }
    .sidebar-menu li:nth-child(4) { animation-delay: 0.4s; }
    .sidebar-menu li:nth-child(5) { animation-delay: 0.5s; }
    .sidebar-menu li:nth-child(6) { animation-delay: 0.6s; }
    .sidebar-menu li:nth-child(7) { animation-delay: 0.7s; }
    .sidebar-menu li:nth-child(8) { animation-delay: 0.8s; }
    .sidebar-menu li:nth-child(9) { animation-delay: 0.9s; }
    .sidebar-menu li:nth-child(10) { animation-delay: 1.0s; }
    
    /* Glow effect for active item */
    .sidebar-menu li.active a {
        position: relative;
    }
    
    .sidebar-menu li.active a::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        animation: glow 2s infinite alternate;
        pointer-events: none;
    }
    
    @keyframes glow {
        from {
            opacity: 0.5;
        }
        to {
            opacity: 0.8;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
                this.querySelector('i').classList.toggle('fa-times');
                this.querySelector('i').classList.toggle('fa-bars');
            });
        }
        
        // Handle mobile menu button
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        }
        
        // Add hover effect for menu items
        const menuItems = document.querySelectorAll('.sidebar-menu li a');
        menuItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.querySelector('i').classList.add('animated');
            });
            
            item.addEventListener('mouseleave', function() {
                this.querySelector('i').classList.remove('animated');
            });
        });
        
        // Add ripple effect to menu items
        function createRipple(event) {
            const button = event.currentTarget;
            
            const circle = document.createElement('span');
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;
            
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
            circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
            circle.classList.add('ripple');
            
            const ripple = button.getElementsByClassName('ripple')[0];
            
            if (ripple) {
                ripple.remove();
            }
            
            button.appendChild(circle);
        }
        
        const buttons = document.querySelectorAll('.sidebar-menu li a');
        buttons.forEach(button => {
            button.addEventListener('click', createRipple);
        });
        
        // Logout confirmation
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
                logoutModal.show();
            });
        }
    });
</script>

<style>
    /* Ripple effect */
    .sidebar-menu li a {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.4);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    /* Icon animation */
    .sidebar-menu li a i.animated {
        animation: pulse 0.5s ease;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
</style>
<style>
    /* Logout modal styling */
    #logoutModal .modal-content {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    #logoutModal .modal-header {
        background: linear-gradient(to right, #3949ab, #5c6bc0);
        color: white;
        border-bottom: none;
        border-radius: 10px 10px 0 0;
    }
    
    #logoutModal .btn-close {
        color: white;
        filter: invert(1) brightness(200%);
    }
    
    #logoutModal .modal-footer {
        border-top: none;
    }
    
    #logoutModal .btn-danger {
        background: linear-gradient(to right, #f44336, #e57373);
        border: none;
        transition: all 0.3s ease;
    }
    
    #logoutModal .btn-danger:hover {
        background: linear-gradient(to right, #d32f2f, #ef5350);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    #logoutModal .btn-secondary {
        background: #757575;
        border: none;
        transition: all 0.3s ease;
    }
    
    #logoutModal .btn-secondary:hover {
        background: #616161;
        transform: translateY(-2px);
    }
</style>

<style>
    /* Add these styles for the company name section */
    .company-logo-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .company-name {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        background: linear-gradient(to right, #ffffff, #e0e0e0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        position: relative;
    }
    
    .company-tagline {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        margin-top: -2px;
        font-style: italic;
        letter-spacing: 0.5px;
    }
    
    /* Add a subtle animation */
    @keyframes glow-company {
        0% { text-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
        50% { text-shadow: 0 0 15px rgba(255, 255, 255, 0.8); }
        100% { text-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
    }
    
    .company-name {
        animation: glow-company 3s infinite;
    }
    
    /* Updated logo styling */
    /* Updated logo styling */
/* Updated logo styling */
.logo-container {
    text-align: center;
    margin-bottom: 8px;
    width: 100%;
    padding: 8px;
}

/* Updated logo styling */
.logo-container {
    text-align: center;
    margin-bottom: 8px;
    width: 100%;
    padding: 8px;
}

/* Updated logo styling */
.logo-container {
    text-align: center;
    margin-bottom: 8px;
    width: 100%;
    padding: 8px;
}

.sidebar-logo {
    width: 150px;
    height: auto;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
    display: block;
    margin: 0 auto;
    animation: logoZoom 3s ease-in-out infinite;
}

@keyframes logoZoom {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); } /* Increased from 1.1 to 1.3 */
    100% { transform: scale(1); }
}

/* Adjust header spacing */
.sidebar-header {
    padding: 12px;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
</style>