<?php
session_start();
include "../db.php";

if(isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Try to find user by email or name
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? OR name = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get browser information
    $browser_info = $_SERVER['HTTP_USER_AGENT'];
    
    if($row = $result->fetch_assoc()) {
        // In the PHP section at the top, modify the success response:
        if(password_verify($password, $row['password'])) {
            // Log successful login
            $login_stmt = $conn->prepare("INSERT INTO login_history (username, email, browser_info, login_time, status) VALUES (?, ?, ?, NOW(), 'success')");
            $login_stmt->bind_param("sss", $row['name'], $row['email'], $browser_info);
            $login_stmt->execute();
        
            $_SESSION['admin'] = $row['name'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['theme'] = 'light';
            
            // Check if it's an AJAX request
            // In the PHP section where success is handled
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => true, 'username' => $row['name']]);
                exit;
            }
            else {
                // Regular form submission
                header("Location: dashboard.php");
                exit;
            }
        } else {
            // Log failed login attempt
            $login_stmt = $conn->prepare("INSERT INTO login_history (username, email, browser_info, login_time, status) VALUES (?, ?, ?, NOW(), 'failed')");
            $login_stmt->bind_param("sss", $row['name'], $row['email'], $browser_info);
            $login_stmt->execute();
        }
    } else {
        // Log failed login attempt with unknown user
        $login_stmt = $conn->prepare("INSERT INTO login_history (username, email, browser_info, login_time, status) VALUES (?, ?, ?, NOW(), 'failed')");
        $login_stmt->bind_param("sss", $username, $username, $browser_info); // Using username as email since we don't know which it is
        $login_stmt->execute();
    }
    $error = "Invalid username/email or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Yana Byahe Na Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .login-page {
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), 
                        url('../images/clouds.jpg') center/cover fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .airplane {
            position: absolute;
            width: 80px;
            height: auto;
            opacity: 0.9;
            pointer-events: none;
            filter: brightness(0) invert(1);
            z-index: 2;
        }

        .airplane-right {
            animation: flyRight 15s linear infinite;
        }

        .airplane-left {
            animation: flyLeft 18s linear infinite;
            transform: scaleX(-1);
        }

        .airplane-diagonal-right {
            animation: flyDiagonalRight 20s linear infinite;
        }

        .airplane-diagonal-left {
            animation: flyDiagonalLeft 17s linear infinite;
            transform: scaleX(-1);
        }

        .airplane-wave {
            animation: flyWave 22s linear infinite;
        }

        @keyframes flyRight {
            from {
                left: -150px;
                transform: translateY(30vh) rotate(0deg);
            }
            to {
                left: calc(100% + 150px);
                transform: translateY(30vh) rotate(0deg);
            }
        }

        @keyframes flyLeft {
            from {
                right: -150px;
                transform: translateY(60vh) rotate(0deg) scaleX(-1);
            }
            to {
                right: calc(100% + 150px);
                transform: translateY(60vh) rotate(0deg) scaleX(-1);
            }
        }

        @keyframes flyDiagonalRight {
            from {
                left: -150px;
                transform: translateY(80vh) rotate(25deg);
            }
            to {
                left: calc(100% + 150px);
                transform: translateY(20vh) rotate(25deg);
            }
        }

        @keyframes flyDiagonalLeft {
            from {
                right: -150px;
                transform: translateY(20vh) rotate(-25deg) scaleX(-1);
            }
            to {
                right: calc(100% + 150px);
                transform: translateY(80vh) rotate(-25deg) scaleX(-1);
            }
        }

        @keyframes flyWave {
            0% {
                left: -150px;
                transform: translateY(50vh) rotate(0deg);
            }
            25% {
                left: 25%;
                transform: translateY(30vh) rotate(15deg);
            }
            50% {
                left: 50%;
                transform: translateY(50vh) rotate(0deg);
            }
            75% {
                left: 75%;
                transform: translateY(70vh) rotate(-15deg);
            }
            100% {
                left: calc(100% + 150px);
                transform: translateY(50vh) rotate(0deg);
            }
        }

        .login-wrapper {
            width: 100%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            display: flex;
            position: relative;
            z-index: 1;
        }

        .login-left-panel {
            flex: 1;
            background: linear-gradient(135deg, #002366, #4169E1);
            padding: 60px 40px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .company-branding {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: visible;
        }

        .login-logo {
            width: 150px;
            height: auto;
            margin-bottom: 1.5rem;
            animation: zoomInOut 3s ease-in-out infinite;
            position: relative;
        }

        @keyframes zoomInOut {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.9);
                filter: 
                    drop-shadow(0 0 10px rgba(255, 255, 255, 0.8)) 
                    brightness(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Company branding styles */
        /* Add this new style for the company name text glow effect */
        @keyframes textGlow {
            0% { text-shadow: none; }
            50% { text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(255, 255, 255, 0.5); }
            100% { text-shadow: none; }
        }
        
        .company-branding h1 {
            font-size: 1.8rem;
            margin: 0 0 1.5rem 0;
            line-height: 1.3;
            animation: textGlow 3s ease-in-out infinite;
        }

        .company-tagline {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.5;
        }

        .login-right-panel {
            flex: 1;
            padding: 50px;
            background: white;
            max-width: 450px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
            background: #f0f5ff;
            border-radius: 50px;
            padding: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.15);
            background: #ffffff;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: transparent;
            color: #004d99;
            box-sizing: border-box;
            outline: none;
        }

        .input-group .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #004d99;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .input-group:focus-within .input-icon {
            color: #002366;
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
        }

        .alert {
            background: #ffe5e5;
            color: #cc0000;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .login-btn {
            background: linear-gradient(135deg, #003366, #004d99);
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #004d99, #0066cc);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            transition: transform 0.5s ease-out;
        }

        .login-btn:active::after {
            transform: translate(-50%, -50%) scale(2);
            opacity: 0;
        }
    </style>
</head>
<body class="login-page">
    <!-- Add video background -->
    <div class="video-background">
        <video autoplay muted loop id="login-bg-video">
            <source src="../videos/hero-bg.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay"></div>
    </div>
    
    <img src="../images/airplane.png" alt="airplane" class="airplane airplane-right">
    <img src="../images/airplane.png" alt="airplane" class="airplane airplane-left">
    <img src="../images/airplane.png" alt="airplane" class="airplane airplane-diagonal-right">
    <img src="../images/airplane.png" alt="airplane" class="airplane airplane-diagonal-left">
    <img src="../images/airplane.png" alt="airplane" class="airplane airplane-wave">
    <div class="login-wrapper">
        <div class="login-left-panel">
            <div class="company-branding">
                <img src="../images/new_logo.png" alt="Yana Byahe Na Logo" class="login-logo">
                <h1><i class="fas fa-plane-departure"></i> Yana Byahe Na Travel and Tours</h1>
                <p class="company-tagline">Your journey to amazing destinations begins here.</p>
                <!-- Add Visit Website Button -->
                <a href="../index.php" class="visit-website-btn">
                    <i class="fas fa-globe"></i> Visit Website
                </a>
            </div>
        </div>
        
        <div class="login-right-panel">
            <div class="login-header">
                <h2><i class="fas fa-paper-plane"></i> Welcome!</h2>
                <p>Please login to access your travel control center</p>
            </div>

            <?php if(isset($error) && $error != ''): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form" id="loginForm">
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" placeholder="username" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" placeholder="password" required>
                </div>

                <button type="submit" class="login-btn" id="loginButton">
                    <i class="fas fa-plane"></i> Take Off to Dashboard
                </button>
            </form>
        </div>
    </div>
</body>

<!-- Add these styles before the closing </head> tag -->
<style>
    /* Video Background Styles - Updated with blur effect */
    .video-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: -1;
    }
    
    #login-bg-video {
        position: absolute;
        top: 50%;
        left: 50%;
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        transform: translateX(-50%) translateY(-50%);
        object-fit: cover;
        filter: blur(8px); /* Add blur effect to video */
        transform-origin: center center;
        transform: translateX(-50%) translateY(-50%) scale(1.1); /* Scale up slightly to avoid blur edges */
    }
    
    .video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)); /* Slightly lighter overlay */
    }
    
    /* Update login page background to be transparent since we now have video */
    .login-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        overflow: hidden;
        background: transparent;
    }
</style>

<!-- Loading Screen -->
<div id="loading-screen" style="display: none;">
    <div class="loading-content">
        <img src="../images/airplane.png" alt="airplane" class="loading-airplane">
        <div class="loading-text">
            <h2>Preparing Your Dashboard</h2>
            <p>Fasten your seatbelt, we're taking off...</p>
        </div>
        <div class="loading-progress">
            <div class="progress-bar"></div>
        </div>
    </div>
</div>

<style>
    #loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #002366, #4169E1);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .loading-content {
        text-align: center;
        color: white;
        max-width: 500px;
        padding: 30px;
    }
    
    .loading-airplane {
        width: 100px;
        filter: brightness(0) invert(1);
        animation: loading-fly 3s infinite ease-in-out;
    }
    
    @keyframes loading-fly {
        0% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
        100% { transform: translateY(0) rotate(0deg); }
    }
    
    .loading-text h2 {
        font-size: 24px;
        margin-bottom: 10px;
    }
    
    .loading-text p {
        font-size: 16px;
        opacity: 0.8;
    }
    
    .loading-progress {
        margin-top: 30px;
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-bar {
        // Update the progress bar animation duration
        height: 100%;
        width: 0%;
        background: white;
        border-radius: 3px;
        animation: progress 2.5s ease-in-out forwards;
    }
    
    @keyframes progress {
        0% { width: 0%; }
        100% { width: 100%; }
    }

    /* Remove this duplicate style that's overriding the zoomInOut animation */
    .login-logo {
        width: 150px;
        height: auto;
        margin-bottom: 1.5rem;
        animation: zoomInOut 3s ease-in-out infinite;
        position: relative;
    }

    @keyframes zoomInOut {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.9);
            filter: 
                drop-shadow(0 0 10px rgba(255, 255, 255, 0.8)) 
                brightness(1.2);
        }
        100% {
            transform: scale(1);
        }
    }

    /* Add loading button style */
    .login-btn.loading {
        background: linear-gradient(135deg, #003366, #004d99);
        cursor: not-allowed;
        opacity: 0.9;
    }

    .login-btn.loading:hover {
        transform: none;
        box-shadow: 0 5px 15px rgba(0, 51, 102, 0.3);
    }

    .fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Fix for company branding position issue */
    .company-branding {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 500px;
        position: relative;
        overflow: visible;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input');
    const loginButton = document.getElementById('loginButton');
    const loginForm = document.getElementById('loginForm');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        }); 
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });

    // Add this new code for error message
    const alertDiv = document.querySelector('.alert');
    if (alertDiv) {
        setTimeout(() => {
            alertDiv.style.transition = 'opacity 0.5s ease';
            alertDiv.style.opacity = '0';
            setTimeout(() => {
                alertDiv.remove();
            }, 500);
        }, 2000);
    }
    
    // Add form submission handler with AJAX
    // Replace the existing loginForm event listener
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (loginForm.checkValidity()) {
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing for Takeoff...';
            loginButton.disabled = true;
            loginButton.classList.add('loading');
            
            const formData = new FormData(this);
            
            fetch('login.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show loading screen - modified to use the existing element
                    const loadingScreen = document.getElementById('loading-screen');
                    const loadingText = loadingScreen.querySelector('.loading-text h2');
                    
                    // Update the welcome message
                    loadingText.textContent = `Welcome Aboard, ${data.username}!`;
                    loadingScreen.querySelector('.loading-text p').textContent = 'Preparing your flight to the dashboard...';
                    
                    // Show the loading screen
                    loadingScreen.style.display = 'flex';
                    
                    setTimeout(() => {
                        window.location.href = "dashboard.php";
                    }, 2500);
                } else {
                    throw new Error('Login failed');
                }
            })
            .catch(error => {
                loginButton.innerHTML = '<i class="fas fa-plane"></i> Take Off to Dashboard';
                loginButton.disabled = false;
                loginButton.classList.remove('loading');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Invalid username/email or password';
                const loginHeader = document.querySelector('.login-header');
                loginHeader.insertAdjacentElement('afterend', errorDiv);
                
                setTimeout(() => {
                    errorDiv.style.transition = 'opacity 0.5s ease';
                    errorDiv.style.opacity = '0';
                    setTimeout(() => errorDiv.remove(), 500);
                }, 2000);
            });
        }
    });
});
</script>   
</html>

<style>
    /* Styles for the Visit Website button */
    .visit-website-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px; /* Reduced padding */
        margin-top: 20px;
        background: linear-gradient(135deg, #003366, #004d99);
        color: white;
        border-radius: 50px;
        text-decoration: none;
        font-size: 0.9rem; /* Reduced font size */
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s, box-shadow 0.3s;
    }

    .visit-website-btn i {
        margin-right: 8px; /* Add margin to create space between icon and text */
        color: #FFD700; /* Set the icon color to gold */
    }

    .visit-website-btn:hover {
        background: linear-gradient(135deg, #004d99, #0066cc);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }
</style>