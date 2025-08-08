<?php
session_start();
include "db.php"; // Add database connection

if (!isset($_SESSION['submission_token'])) {
    $_SESSION['submission_token'] = bin2hex(random_bytes(32));
}

// Get package_id and destination_id from URL if available
$selected_package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;
$selected_destination_id = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;

// Fetch package details if package_id is provided  
$package_name = '';
if ($selected_package_id > 0) {
    $stmt = $conn->prepare("SELECT name FROM packages WHERE id = ? AND status = 1");
    $stmt->bind_param("i", $selected_package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $package_name = $result->fetch_assoc()['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e88e5;
            --primary-dark: #1565c0;
            --secondary: #64b5f6;
            --accent: #ff6d00;
            --success: #43a047;
            --error: #e53935;
            --text-dark: #263238;
            --text-light: #78909c;
            --light-bg: #f5f9fc;
            --white: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .booking-sidebar {
            max-width: 600px;
            margin: 40px auto;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        .booking-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 25px;
            color: var(--white);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .back-button {
            position: absolute;
            top: 15px;
            left: 15px;
            color: var(--white);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 20px;
            transition: all 0.3s ease;
            z-index: 2;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .back-button i {
            margin-right: 6px;
            font-size: 12px;
            transition: transform 0.3s ease;
        }
        
        .back-button:hover {
            background-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .back-button:hover i {
            transform: translateX(-3px);
        }
        
        .back-button:active {
            transform: translateY(0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
            margin-right: 5px;
        }
        
        .back-button:hover {
            color: rgba(255, 255, 255, 0.8);
            transform: translateX(-3px);
        }

        .booking-header h2 {
            font-size: 24px;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .booking-header p {
            opacity: 0.9;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }

        .booking-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            animation: pulse 8s infinite;
        }

        .booking-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
            transition: var(--transition);
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            transition: var(--transition);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: var(--transition);
            background-color: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.25);
        }

        .form-control.is-invalid {
            border-color: var(--error);
            background-color: rgba(229, 57, 53, 0.05);
        }

        .invalid-feedback {
            color: var(--error);
            font-size: 12px;
            margin-top: 5px;
            animation: fadeIn 0.3s;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            animation: slideDown 0.4s ease-out;
        }

        .alert-danger {
            background-color: rgba(229, 57, 53, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-success {
            background-color: rgba(67, 160, 71, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .btn-book-now {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .btn-book-now:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(30, 136, 229, 0.3);
        }

        .btn-book-now:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .btn-book-now i {
            margin-right: 8px;
        }

        .btn-book-now.loading {
            opacity: 0.8;
            pointer-events: none;
        }

        .btn-book-now.loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            right: 20px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: rotate 1s infinite linear;
        }

        /* Animation for ripple effect */
        .btn-book-now::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: var(--transition);
            z-index: 0;
        }

        .btn-book-now:hover::before {
            width: 300%;
            height: 300%;
        }

        .form-group.focus label {
            color: var(--primary);
        }

        .form-progress {
            display: flex;
            justify-content: space-between;
            padding: 0 30px 20px;
        }

        .progress-step {
            flex: 1;
            position: relative;
            text-align: center;
            font-size: 12px;
            color: var(--text-light);
            padding-top: 25px;
        }

        .progress-step::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 20px;
            background-color: var(--secondary);
            border-radius: 50%;
            z-index: 1;
            transition: var(--transition);
        }

        .progress-step::after {
            content: "";
            position: absolute;
            top: 9px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--secondary);
            z-index: 0;
        }

        .progress-step:first-child::after {
            left: 50%;
            width: 50%;
        }

        .progress-step:last-child::after {
            width: 50%;
        }

        .progress-step.active {
            color: var(--primary);
            font-weight: 600;
        }

        .progress-step.active::before {
            background-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.3);
        }

        /* Fancy animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Calendar styling */
        input[type="date"]::-webkit-calendar-picker-indicator {
            color: var(--primary);
            cursor: pointer;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .booking-sidebar {
                margin: 20px;
                max-width: none;
            }
            
            .booking-form {
                padding: 20px;
            }
            
            .form-progress {
                padding: 0 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="booking-sidebar">
        <div class="booking-header">
            <h2>Book Your Dream Vacation</h2>
            <p>Fill out the form below to start your journey</p>
            <a href="javascript:history.back()" class="back-button"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        
        <div class="form-progress">
            <div class="progress-step active">Personal Info</div>
            <div class="progress-step">Trip Details</div>
            <div class="progress-step">Confirmation</div>
        </div>

        <div class="booking-form">
            <div id="errorAlert" class="alert alert-danger" style="display:none;"></div>
            <div id="successAlert" class="alert alert-success" style="display:none;">
                <i class="fas fa-check-circle"></i> Your booking inquiry has been submitted successfully!
            </div>

            <form id="bookingForm" action="process_booking.php" method="POST" novalidate>
                <input type="hidden" name="submission_token" value="<?php echo $_SESSION['submission_token']; ?>">
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="+63 XXX XXX XXXX" required>
                </div>

                <div class="form-group">
                    <label for="destination">Destination *</label>
                    <select class="form-control" id="destination" name="destination" required>
                        <option value="">Select Destination</option>
                        <?php
                        $destinations = $conn->query("SELECT id, name FROM destinations WHERE status = 1");
                        while($dest = $destinations->fetch_assoc()) {
                            $selected = ($dest['id'] == $selected_destination_id) ? 'selected' : '';
                            echo "<option value='" . $dest['id'] . "' $selected>" . htmlspecialchars($dest['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="package">Package *</label>
                    <select class="form-control" id="package" name="package" required>
                        <option value="">Select Package</option>
                        <?php if($selected_package_id && $package_name): ?>
                            <option value="<?php echo $selected_package_id; ?>" selected><?php echo htmlspecialchars($package_name); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="travelers">Number of Travelers *</label>
                    <input type="number" class="form-control" id="travelers" name="travelers" min="1" placeholder="How many people will be traveling?" required>
                </div>

                <div class="form-group">
                    <label for="preferred_date">Preferred Date *</label>
                    <input type="date" class="form-control" id="preferred_date" name="preferred_date" required>
                </div>

                <div class="form-group">
                    <label for="budget_range">Budget Range *</label>
                    <select class="form-control" id="budget_range" name="budget_range" required>
                        <option value="">Select Budget Range</option>
                        <option value="economy">Economy (Below ₱20,000)</option>
                        <option value="standard">Standard (₱20,000 - ₱50,000)</option>
                        <option value="luxury">Luxury (Above ₱50,000)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests</label>
                    <textarea class="form-control" id="special_requests" name="special_requests" rows="3" placeholder="Any special requirements or preferences?"></textarea>
                </div>

                <button type="submit" class="btn-book-now">
                    <i class="fas fa-calendar-check"></i> Submit Booking
                </button>
            </form>
        </div>
    </div>

    <script>
    // Form validation and dynamic updates
    const form = document.getElementById('bookingForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    // Get pre-selected values from URL if available
    const selectedDestinationId = <?php echo $selected_destination_id ?: 'null'; ?>;
    const selectedPackageId = <?php echo $selected_package_id ?: 'null'; ?>;

    // Focus effects
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focus');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focus');
            validateInput(this);
        });
        
        input.addEventListener('input', function() {
            validateInput(this);
        });
    });

    // Validate individual input
    function validateInput(input) {
        // Existing validation code remains unchanged
    }

    function setErrorFor(input, message) {
        // Existing error setting code remains unchanged
    }

    function removeErrorFor(input) {
        // Existing error removal code remains unchanged
    }

    // Update packages based on selected destination with animation
    document.getElementById('destination').addEventListener('change', function() {
        loadPackagesForDestination(this.value);
    });

    // Function to load packages for a destination
    function loadPackagesForDestination(destinationId) {
        if (!destinationId) return;
        
        const packageSelect = document.getElementById('package');
        packageSelect.innerHTML = '<option value="">Select Package</option>';
        
        packageSelect.disabled = true;
        packageSelect.classList.add('is-loading');
        
        fetch(`get_packages.php?destination_id=${destinationId}`)
            .then(response => response.json())
            .then(packages => {
                // Clear and add new options with a slight delay for animation
                setTimeout(() => {
                    packageSelect.innerHTML = '<option value="">Select Package</option>';
                    packages.forEach(pkg => {
                        const option = document.createElement('option');
                        option.value = pkg.id;
                        option.textContent = pkg.name;
                        // Select the package if it matches the pre-selected package ID
                        if (selectedPackageId && pkg.id == selectedPackageId) {
                            option.selected = true;
                        }
                        packageSelect.appendChild(option);
                        
                        // Animate each option appearing
                        setTimeout(() => {
                            option.style.opacity = '1';
                        }, 50);
                    });
                    
                    packageSelect.disabled = false;
                    packageSelect.classList.remove('is-loading');
                }, 300);
            })
            .catch(error => {
                console.error('Error fetching packages:', error);
                packageSelect.disabled = false;
                packageSelect.classList.remove('is-loading');
                setErrorFor(packageSelect, 'Failed to load packages. Please try again.');
            });
    }

    // Load packages for pre-selected destination on page load
    document.addEventListener('DOMContentLoaded', function() {
        // If we have a pre-selected destination, load its packages
        if (selectedDestinationId) {
            loadPackagesForDestination(selectedDestinationId);
        }
        
        // Set minimum date for preferred_date input
        const preferredDateInput = document.getElementById('preferred_date');
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        preferredDateInput.min = tomorrow.toISOString().split('T')[0];

        // Rest of the DOMContentLoaded code remains unchanged
    });

    // Update form submission handling
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate all inputs before submission
        let isValid = true;
        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value.trim()) {
                setErrorFor(input, `${input.previousElementSibling.textContent.replace(' *', '')} is required`);
                isValid = false;
            } else if (input.id === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    setErrorFor(input, 'Please enter a valid email address');
                    isValid = false;
                }
            } else if (input.id === 'phone' && input.value.trim()) {
                const phoneRegex = /^(\+\d{1,3})?\s?\d{9,15}$/;
                if (!phoneRegex.test(input.value.trim())) {
                    setErrorFor(input, 'Please enter a valid phone number');
                    isValid = false;
                }
            } else if (input.id === 'travelers' && input.value.trim()) {
                if (parseInt(input.value) < 1) {
                    setErrorFor(input, 'Number of travelers must be at least 1');
                    isValid = false;
                }
            }
        });
        
        if (!isValid) {
            document.getElementById('errorAlert').textContent = 'Please fill the details in the form before submitting.';
            document.getElementById('errorAlert').style.display = 'block';
            setTimeout(() => {
                document.getElementById('errorAlert').style.display = 'none';
            }, 5000);
            return;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const formData = new FormData(form);
            formData.append('status', 'pending');
            formData.append('payment_status', 'unpaid');
            formData.append('user_id', '0'); // Set default user_id to 0 for non-logged in users
            
            const response = await fetch('process_booking.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update progress steps
                document.querySelectorAll('.progress-step').forEach((step, index) => {
                    if (index <= 2) {
                        step.classList.add('active');
                    }
                });

                // Hide form and show enhanced success message
                form.style.display = 'none';
                const successAlert = document.getElementById('successAlert');
                successAlert.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: var(--success); margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 15px;">Booking Submitted Successfully!</h3>
                        <p style="margin-bottom: 15px;">Thank you for choosing YANA Tours. Your booking request has been received.</p>
                        <p style="font-size: 14px; color: var(--text-light);">You will receive a confirmation email shortly.</p>
                        <div style="margin-top: 20px;">
                            <p style="font-size: 14px;">Redirecting to home page...</p>
                            <div class="loading-dots" style="margin-top: 10px;">
                                <span>.</span><span>.</span><span>.</span>
                            </div>
                        </div>
                    </div>
                `;
                successAlert.style.display = 'block';
                document.getElementById('errorAlert').style.display = 'none';
                
                // Redirect to home page after 5 seconds
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 5000);
            } else {
                // Show error message
                document.getElementById('errorAlert').textContent = result.message || 'An error occurred. Please try again.';
                document.getElementById('errorAlert').style.display = 'block';
                document.getElementById('successAlert').style.display = 'none';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('errorAlert').textContent = 'A network error occurred. Please try again.';
            document.getElementById('errorAlert').style.display = 'block';
        } finally {
            // Reset button state
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = '<i class="fas fa-calendar-check"></i> Submit Booking';
        }
    });
    </script>
</body>
</html>
 <style> tag */
        .loading-dots span {
            animation: dots 1.5s infinite;
            animation-fill-mode: both;
            font-size: 20px;
            margin: 0 2px;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes dots {
            0% { opacity: 0; transform: translateY(0); }
            25% { opacity: 1; transform: translateY(-5px); }
            50% { opacity: 1; transform: translateY(0); }
            75% { opacity: 1; transform: translateY(5px); }
            100% { opacity: 0; transform: translateY(0); }
        }