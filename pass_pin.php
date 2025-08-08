<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure PIN Entry</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #7209b7;
            --accent-color: #3a0ca3;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4169E1, #1E90FF, #00008B, #5F9EA0); /* Enhanced gradient with additional color */
            background-size: 300% 300%; /* Further increase background size for more movement */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            animation: smokeGradient 15s infinite alternate; /* Adjust animation duration */
        }
        
        @keyframes smokeGradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            animation: fadeIn 0.8s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            background-color: var(--primary-color);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.7);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(67, 97, 238, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
            }
        }
        
        h1 {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.8rem;
        }
        
        .pin-container {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
            gap: 10px;
        }
        
        .pin-digit {
            width: 45px;
            height: 50px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            font-size: 1.5rem;
            background: var(--light-color);
            transition: all 0.3s;
        }
        
        .pin-digit:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }
        
        .submit-btn {
            padding: 12px 0;
            width: 100%;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        
        .submit-btn:active {
            transform: translateY(1px);
        }
        
        .submit-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .submit-btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
        
        .message {
            margin-top: 1.5rem;
            padding: 10px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0;
            transform: translateY(10px);
            animation: slideUp 0.5s forwards;
        }
        
        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success {
            background-color: rgba(46, 204, 113, 0.15);
            color: var(--success-color);
        }
        
        .error {
            background-color: rgba(231, 76, 60, 0.15);
            color: var(--error-color);
        }
        
        .hidden {
            display: none;
        }
        
        .finger-scan {
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem;
            position: relative;
        }
        
        .finger-scan .scan {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgba(67, 97, 238, 0.5);
            border-radius: 2px;
            animation: scanning 2s ease-in-out infinite;
        }
        
        @keyframes scanning {
            0% {
                top: 0;
            }
            50% {
                top: calc(100% - 4px);
            }
            100% {
                top: 0;
            }
        }
        
        .shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        
        @keyframes shake {
            10%, 90% {
                transform: translate3d(-1px, 0, 0);
            }
            20%, 80% {
                transform: translate3d(2px, 0, 0);
            }
            30%, 50%, 70% {
                transform: translate3d(-4px, 0, 0);
            }
            40%, 60% {
                transform: translate3d(4px, 0, 0);
            }
        }
    </style>
</head>
<body>
    <div class="container" id="pinContainer">
        <!-- Add Back Button -->
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div class="logo">
            <i class="fas fa-shield-alt fa-2x"></i>
        </div>
        <h1>Secure Authentication</h1>
        <p>Please enter your 6-digit PIN to continue</p>
        
        <form id="pinForm" method="post">
            <div class="pin-container">
                <input type="password" class="pin-digit" maxlength="1" data-index="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="password" class="pin-digit" maxlength="1" data-index="2" pattern="[0-9]" inputmode="numeric" required>
                <input type="password" class="pin-digit" maxlength="1" data-index="3" pattern="[0-9]" inputmode="numeric" required>
                <input type="password" class="pin-digit" maxlength="1" data-index="4" pattern="[0-9]" inputmode="numeric" required>
                <input type="password" class="pin-digit" maxlength="1" data-index="5" pattern="[0-9]" inputmode="numeric" required>
                <input type="password" class="pin-digit" maxlength="1" data-index="6" pattern="[0-9]" inputmode="numeric" required>
            </div>
            
            <input type="hidden" id="fullPin" name="pin">
            <button type="submit" class="submit-btn">
                <i class="fas fa-lock-open"></i> Verify PIN
            </button>
        </form>
        
        <div id="successMessage" class="message success hidden">
            <i class="fas fa-check-circle"></i> PIN verified successfully!
        </div>
        
        <div id="errorMessage" class="message error hidden">
            <i class="fas fa-exclamation-circle"></i> Incorrect PIN. Please try again.
        </div>
    </div>
    
    <div class="container hidden" id="successContainer">
        <div class="logo" style="background-color: var(--success-color)">
            <i class="fas fa-check fa-2x"></i>
        </div>
        <h1>Authentication Successful</h1>
        <p>You have been successfully authenticated</p>
    </div>

    <script>
        // PHP result simulation (this would normally be done server-side)
        const urlParams = new URLSearchParams(window.location.search);
        const result = urlParams.get('result');
        
        // PIN input handling
        const pinDigits = document.querySelectorAll('.pin-digit');
        const fullPinInput = document.getElementById('fullPin');
        const form = document.getElementById('pinForm');
        
        // Focus the first input on load
        window.addEventListener('load', function() {
            pinDigits[0].focus();
        });
        
        // Handle digit input and auto-focus next field
        pinDigits.forEach(input => {
            input.addEventListener('keyup', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                const index = parseInt(this.dataset.index);
                
                // Auto-focus next field if value entered
                if (this.value && index < pinDigits.length) {
                    pinDigits[index].focus();
                }
                
                // Backspace key handling - focus previous input
                if (e.key === 'Backspace' && !this.value && index > 1) {
                    pinDigits[index-2].focus();
                }
                
                // Update the hidden full PIN field
                updateFullPin();
            });
        });
        
        function updateFullPin() {
            let pin = '';
            pinDigits.forEach(input => {
                pin += input.value;
            });
            fullPinInput.value = pin;
        }
        
        // Form submission
        let attemptCount = 0;
        const maxAttempts = 5;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get the full PIN
            const pin = fullPinInput.value;
            
            // Check against the new 6-digit PIN "032402"
            if (pin === '000000') {
                showSuccess();
            } else {
                attemptCount++;
                if (attemptCount >= maxAttempts) {
                    showBanWarning();
                } else {
                    showError();
                }
            }
        });
        
        function showBanWarning() {
            const banMessage = document.createElement('div');
            banMessage.className = 'ban-warning';
            banMessage.innerHTML = `
                <div class="ban-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="ban-text">Too many attempts. You are temporarily banned!</div>
            `;
            document.body.appendChild(banMessage);
            
            // Set ban time in local storage
            localStorage.setItem('banTime', Date.now());
            
            setTimeout(() => {
                banMessage.remove();
                window.location.href = 'index.php'; // Redirect back to the website
            }, 5000); // 5 seconds delay
        }
        
        // Check if user is banned
        function checkBanStatus() {
            const banTime = localStorage.getItem('banTime');
            const cooldownPeriod = 5 * 60 * 1000; // 5 minutes in milliseconds
            
            if (banTime && (Date.now() - banTime) < cooldownPeriod) {
                alert('You are temporarily banned. Please try again later.');
                window.location.href = 'index.php'; // Redirect back to the website
            }
        }
        
        // Call checkBanStatus on page load
        window.addEventListener('load', checkBanStatus);
        
        function showSuccess() {
            document.getElementById('successMessage').classList.remove('hidden');
            document.getElementById('errorMessage').classList.add('hidden');
            
            // Immediate transition to success screen
            document.getElementById('pinContainer').classList.add('animate__animated', 'animate__fadeOut');
            setTimeout(() => {
                document.getElementById('pinContainer').classList.add('hidden');
                document.getElementById('successContainer').classList.remove('hidden');
                document.getElementById('successContainer').classList.add('animate__animated', 'animate__fadeIn');
                
                // Redirect after 3 seconds
                setTimeout(() => {
                    window.location.href = 'admin/login.php';
                }, 3000);
            }, 500);
        }
        
        function showError() {
            document.getElementById('errorMessage').classList.remove('hidden');
            document.getElementById('successMessage').classList.add('hidden');
            document.getElementById('pinContainer').classList.add('shake');
            
            // Reset PIN fields
            pinDigits.forEach(input => {
                input.value = '';
            });
            updateFullPin();
            pinDigits[0].focus();
            
            // Remove shake animation after it completes
            setTimeout(() => {
                document.getElementById('pinContainer').classList.remove('shake');
            }, 500);
        }
        
        function resetForm() {
            // Reset the entire form for demo purposes
            document.getElementById('successContainer').classList.add('hidden');
            document.getElementById('pinContainer').classList.remove('hidden', 'animate__fadeOut');
            document.getElementById('errorMessage').classList.add('hidden');
            document.getElementById('successMessage').classList.add('hidden');
            
            pinDigits.forEach(input => {
                input.value = '';
            });
            updateFullPin();
            pinDigits[0].focus();
        }
    </script>
</body>

    <style>
        /* Enhanced styles for the back button */
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--dark-color);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 10px 15px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        
        .back-btn:hover {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
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
</body>
</html>

