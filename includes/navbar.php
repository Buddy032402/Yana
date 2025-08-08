<nav class="navbar navbar-expand-lg fixed-top navbar-glass">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/new_logo.png" alt="Yana Byahe Na Logo" class="brand-logo">
            <div class="brand-text-container">
                <span class="brand-text-main">Yana Biyahe Na</span>
                <span class="brand-text-sub">Travel and Tours</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#destinations">
                        <i class="fas fa-map-marked-alt"></i> Destinations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#packages">
                        <i class="fas fa-box-open"></i> Packages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#top-picks">
                        <i class="fas fa-star"></i> Top Picks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#testimonials">
                        <i class="fas fa-comments"></i> Testimonials
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#about">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
                <!-- Update login icon button here -->
                <li class="nav-item">
                    <a class="nav-link" href="pass_pin.php">
                        <i class="fas fa-sign-in-alt"></i> 
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Base navbar styles */
.navbar {
    transition: all 0.4s ease;
    backdrop-filter: blur(8px);
    background: rgba(41, 82, 204, 0.85); /* Professional royal blue with transparency */
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(41, 82, 204, 0.2);
}

/* Add this CSS for navbar hiding functionality */
.navbar.hidden {
    transform: translateY(-100%);
    box-shadow: none;
}

.navbar.scrolled {
    background: rgba(41, 82, 204, 0.95); /* Slightly more opaque when scrolled */
    box-shadow: 0 4px 25px rgba(41, 82, 204, 0.25);
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
    .navbar {
        background: rgba(41, 82, 204, 0.8);
    }
    .navbar.scrolled {
        background: rgba(41, 82, 204, 0.9);
    }
}

@supports (-webkit-backdrop-filter: none) {
    .navbar {
        -webkit-backdrop-filter: blur(8px);
    }
    .navbar.scrolled {
        -webkit-backdrop-filter: blur(10px);
    }
}

/* Link colors - always white */
.nav-link {
    color: #FFFFFF !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    font-weight: 600;
}

/* Override scrolled state to keep white text */
.navbar.scrolled .nav-link {
    color: #FFFFFF !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Remove dark text color overrides */
.navbar.scrolled .brand-text-main,
.navbar.scrolled .brand-text-sub {
    background: linear-gradient(45deg, #FFFFFF, rgba(255, 255, 255, 0.85));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.brand-text-sub,
.navbar.scrolled .brand-text-sub {
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.navbar.scrolled .brand-text-main,
.navbar.scrolled .brand-text-sub {
    background: linear-gradient(45deg, #FFFFFF, rgba(255, 255, 255, 0.85));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
@supports (-webkit-backdrop-filter: none) {
    .navbar {
        -webkit-backdrop-filter: blur(10px);
    }
    .navbar.scrolled {
        -webkit-backdrop-filter: blur(15px);
    }
}

/* Adjust link colors for better visibility */
.nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    transition: all 0.4s ease;
}

.nav-link:hover,
.nav-link.active {
    color: rgba(255, 255, 255, 0.9) !important;
    text-shadow: none;
}

.navbar.scrolled .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    text-shadow: none;
}

.navbar.scrolled .nav-link:hover,   
.navbar.scrolled .nav-link.active {
    color: rgba(255, 255, 255, 0.9) !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

@media (prefers-color-scheme: dark) {
    .navbar {
        background: rgb(255, 255, 255);
    }
    .navbar.scrolled {
        background: rgb(255, 255, 255);
    }
}
.nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.4s ease;
}

.nav-link:hover,
.nav-link.active {
    color: rgba(255, 255, 255, 1) !important;
}

.navbar.scrolled .nav-link {
    color: rgba(255, 248, 248, 0.8) !important;
    text-shadow: 0 1px 2px rgba(255, 255, 255, 0.1);
}

.navbar.scrolled .nav-link:hover,
.navbar.scrolled .nav-link.active {
    color: rgb(255, 255, 255) !important;
}

@media (prefers-color-scheme: dark) {
    .navbar:not(.scrolled) .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    .navbar:not(.scrolled) .nav-link:hover,
    .navbar:not(.scrolled) .nav-link.active {
        color: rgba(255, 255, 255, 1) !important;
    }
}

@supports (-webkit-backdrop-filter: none) {
    .navbar {
        -webkit-backdrop-filter: blur(10px);
    }
    .navbar.scrolled {
        -webkit-backdrop-filter: blur(12px);
    }
}

/* Adjust for dark backgrounds */
@media (prefers-color-scheme: dark) {
    .navbar {
        background: rgba(65, 105, 225, 0.7);
    }
    .navbar.scrolled {
        background: rgba(65, 105, 225, 0.8);
    }
}

.navbar-nav .nav-link {
    position: relative;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
    color: #333;
}

.navbar-nav .nav-link:before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: #2563eb;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.navbar-nav .nav-link:hover:before,
.navbar-nav .nav-link.active:before {
    width: 80%;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link.active {
    color: #2563eb;
    transform: translateY(-2px);
}

.navbar-nav .nav-link i {
    transition: transform 0.3s ease;
    display: inline-block;
    margin-right: 5px;
}

/* Icon colors - updated to complement royal blue */
.nav-link i.fa-home { color: #FFFFFF; }
.nav-link i.fa-map-marked-alt { color: #FFFFFF; }
.nav-link i.fa-box-open { color: #FFFFFF; }
.nav-link i.fa-star { color: #FFFFFF; }
.nav-link i.fa-comments { color: #FFFFFF; }
.nav-link i.fa-info-circle { color: #FFFFFF; }
.nav-link i.fa-envelope { color: #FFFFFF; }

/* Icon colors with unique colors for each icon */
.nav-link i.fa-home { color: #4CAF50; }         /* Green */
.nav-link i.fa-map-marked-alt { color: #FF5722; } /* Deep Orange */
.nav-link i.fa-box-open { color: #9C27B0; }     /* Purple */
.nav-link i.fa-star { color: #FFC107; }         /* Amber */
.nav-link i.fa-comments { color: #2196F3; }     /* Blue */
.nav-link i.fa-info-circle { color: #607D8B; }  /* Blue Grey */
.nav-link i.fa-envelope { color: #E91E63; }     /* Pink */

/* Hover effect for icons */
.navbar-nav .nav-link:hover i {
    transform: scale(1.2);
    filter: brightness(1.2);
}

/* Active state colors - slightly darker variants */
.navbar-nav .nav-link.active i.fa-home { color: #388E3C; }
.navbar-nav .nav-link.active i.fa-map-marked-alt { color: #D84315; }
.navbar-nav .nav-link.active i.fa-box-open { color: #7B1FA2; }
.navbar-nav .nav-link.active i.fa-star { color: #FFA000; }
.navbar-nav .nav-link.active i.fa-comments { color: #1976D2; }
.navbar-nav .nav-link.active i.fa-info-circle { color: #455A64; }
.navbar-nav .nav-link.active i.fa-envelope { color: #C2185B; }

.navbar-nav .nav-link:hover i,
.navbar-nav .nav-link.active i {
    transform: scale(1.2);
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.brand-logo {
    height: 100px;
    width: auto;
    object-fit: contain;
    margin: -25px 0;
    padding: 5px;
    animation: logoBreathing 3s ease-in-out infinite;
    transition: all 0.5s ease;
}

@keyframes logoBreathing {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.3);  /* Increased from 1.15 */
    }
    100% {
        transform: scale(1);
    }
}

.navbar-brand:hover .brand-logo {
    transform: scale(1.4) rotate(12deg);  /* Increased from 1.2 and 8deg */
    filter: brightness(1.4);  /* Increased brightness */
    animation: logoHover 1s ease-in-out;  /* Longer animation duration */
}

@keyframes logoHover {
    0% {
        transform: scale(1) rotate(0deg);
    }
    50% {
        transform: scale(1.5) rotate(15deg);  /* Increased from 1.25 and 12deg */
    }
    100% {
        transform: scale(1.4) rotate(12deg);  /* Increased from 1.2 and 8deg */
    }
}

.brand-text-container {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
    margin-left: 5px;
    align-items: center;  /* Center text horizontally */
    text-align: center;   /* Ensure text alignment is centered */
}

.brand-text-main {
    background: linear-gradient(45deg, #FFFFFF, rgba(255, 255, 255, 0.85), #FFFFFF);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
    font-size: 1.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-family: 'Montserrat', sans-serif;
    text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.1);
    margin-bottom: 2px;
    transition: all 0.4s ease;
}

.brand-text-sub {
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 500;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: 'Montserrat', sans-serif;
    transition: all 0.4s ease;
}

/* Remove these conflicting styles that change text to dark when scrolled */
.navbar.scrolled .brand-text-main {
    background: linear-gradient(45deg, #FFFFFF, rgba(255, 255, 255, 0.85));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: none;
}

.navbar.scrolled .brand-text-sub {
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.navbar-brand:hover .brand-text-main,
.navbar-brand:hover .brand-text-sub {
    filter: brightness(1.2);
    transform: scale(1.02);
}

/* Add this in the head section of your HTML */
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">

.navbar-brand:hover .brand-text {
    background: linear-gradient(45deg, #3498db, #2c3e50);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.ban-warning {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: rgba(231, 76, 60, 0.9);
    color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 1000;
    animation: fadeInOut 5s ease-in-out;
}

.ban-icon {
    font-size: 2rem;
}

.ban-text {
    font-size: 1.2rem;
    font-weight: bold;
}

@keyframes fadeInOut {
    0%, 100% {
        opacity: 0;
    }
    10%, 90% {
        opacity: 1;
    }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        
        // Handle active state for package details page
        if (currentPath.includes('package_details.php')) {
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href.includes('#packages')) {
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'page');
                }
            });
        }
        
        // Handle active state for other pages
        else {
            const currentPage = currentPath.split('/').pop();
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && (href === currentPage || (currentPage === '' && href === 'index.php'))) {
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'page');
                }
            });
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginLink = document.querySelector('.nav-link[href="pass_pin.php"]');

    loginLink.addEventListener('click', function(e) {
        const banTime = localStorage.getItem('banTime');
        const cooldownPeriod = 5 * 60 * 1000; // 5 minutes in milliseconds

        if (banTime && (Date.now() - banTime) < cooldownPeriod) {
            e.preventDefault();
            showBanWarning(banTime, cooldownPeriod);
        }
    });

    function showBanWarning(banTime, cooldownPeriod) {
        const existingWarning = document.querySelector('.ban-warning');
        if (existingWarning) return; // Prevent multiple warnings

        const banMessage = document.createElement('div');
        banMessage.className = 'ban-warning';
        banMessage.innerHTML = `
            <div class="ban-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="ban-text">You are temporarily banned. Please try again later.</div>
            <div class="ban-timer">Time remaining: <span id="timer"></span></div>
        `;
        document.body.appendChild(banMessage);

        const timerElement = document.getElementById('timer');
        updateTimer(timerElement, banTime, cooldownPeriod);

        setTimeout(() => {
            banMessage.remove();
        }, 5000); // 5 seconds delay
    }

    function updateTimer(timerElement, banTime, cooldownPeriod) {
        const interval = setInterval(() => {
            const timeLeft = cooldownPeriod - (Date.now() - banTime);
            if (timeLeft <= 0) {
                clearInterval(interval);
                timerElement.textContent = '00:00';
            } else {
                const minutes = Math.floor(timeLeft / 60000);
                const seconds = Math.floor((timeLeft % 60000) / 1000);
                timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
        }, 1000);
    }
});
</script>

