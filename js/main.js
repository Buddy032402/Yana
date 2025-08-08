// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            window.scrollTo({
                top: target.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Initialize AOS
    AOS.init({
        duration: 800,
        offset: 100,
        once: true
    });
    
    // Dynamic package loading based on destination selection
    const destinationSelect = document.getElementById('destination-select');
    if (destinationSelect) {
        destinationSelect.addEventListener('change', function() {
            const destinationId = this.value;
            const packageSelect = document.getElementById('package-select');
            
            // Clear current options
            packageSelect.innerHTML = '<option value="">Select Package</option>';
            
            if (destinationId) {
                // Fetch packages for the selected destination
                // Find the section that populates the package dropdown and modify it:
                // Look for code similar to this:
                
                fetch('get_packages.php?destination_id=' + destinationId)
                    .then(response => response.json())
                    .then(data => {
                        // Clear previous options
                        packageSelect.innerHTML = '<option value="">Select Package</option>';
                        
                        // Add loading state
                        packageSelect.disabled = false;
                        
                        // Create a Set to track unique package IDs
                        const addedPackages = new Set();
                        
                        // Add new options
                        data.forEach(package => {
                            // Only add if this package ID hasn't been added yet
                            if (!addedPackages.has(package.id)) {
                                const option = document.createElement('option');
                                option.value = package.id;
                                option.textContent = package.name;
                                packageSelect.appendChild(option);
                                
                                // Mark this package ID as added
                                addedPackages.add(package.id);
                            }
                        });
                    })
                    .catch(error => console.error('Error loading packages:', error));
            }
        });
    }

    // Booking Inquiry Form submission handling
    const bookingInquiryForm = document.getElementById('bookingInquiryForm');
    if (bookingInquiryForm) {
        bookingInquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process_booking_inquiry.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    document.getElementById('booking-success').style.display = 'block';
                    // Reset form
                    document.getElementById('bookingInquiryForm').reset();
                    // Scroll to success message
                    document.getElementById('booking-success').scrollIntoView({behavior: 'smooth'});
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        document.getElementById('booking-success').style.display = 'none';
                    }, 5000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                alert('An error occurred. Please try again later.');
            });
        });
    }
    
    // View All/Less Destinations functionality
    document.addEventListener('DOMContentLoaded', function() {
        const viewAllBtn = document.getElementById('view-all-destinations');
        const viewLessBtn = document.getElementById('view-less-destinations');
        
        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.hidden-destination').forEach(destination => {
                    destination.style.display = 'block';
                    setTimeout(() => {
                        destination.style.opacity = '1';
                        destination.style.transform = 'translateY(0)';
                    }, 10);
                });
                
                viewAllBtn.style.display = 'none';
                if (viewLessBtn) {
                    viewLessBtn.style.display = 'inline-flex';
                    viewLessBtn.classList.add('show');
                }
            });
        }
        
        if (viewLessBtn) {
            viewLessBtn.addEventListener('click', function() {
                document.querySelectorAll('.hidden-destination').forEach(destination => {
                    destination.style.opacity = '0';
                    destination.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        destination.style.display = 'none';
                    }, 400);
                });
                
                if (viewAllBtn) viewAllBtn.style.display = 'inline-flex';
                viewLessBtn.classList.remove('show');
                viewLessBtn.style.display = 'none';
                
                // Smooth scroll back to destinations section
                document.getElementById('destinations').scrollIntoView({ behavior: 'smooth' });
            });
        }
    });
    
    // Destinations View All/Less functionality
    document.addEventListener('DOMContentLoaded', function() {
        const viewAllDestBtn = document.getElementById('view-all-destinations');
        const viewLessDestBtn = document.getElementById('view-less-destinations');
        
        // Initially hide the view less button
        if (viewLessDestBtn) {
            viewLessDestBtn.style.display = 'none';
            viewLessDestBtn.style.visibility = 'hidden';
            viewLessDestBtn.style.opacity = '0';
        }
        
        if (viewAllDestBtn) {
            viewAllDestBtn.addEventListener('click', function() {
                document.querySelectorAll('.hidden-destination').forEach(dest => {
                    dest.style.display = 'block';
                    setTimeout(() => {
                        dest.style.opacity = '1';
                        dest.style.transform = 'translateY(0)';
                    }, 10);
                });
                
                // Show view less button and hide view all button
                this.style.display = 'none';
                if (viewLessDestBtn) {
                    viewLessDestBtn.style.display = 'inline-flex';
                    viewLessDestBtn.style.visibility = 'visible';
                    viewLessDestBtn.style.opacity = '1';
                }
            });
        }
        
        if (viewLessDestBtn) {
            viewLessDestBtn.addEventListener('click', function() {
                document.querySelectorAll('.hidden-destination').forEach(dest => {
                    dest.style.opacity = '0';
                    dest.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        dest.style.display = 'none';
                    }, 400);
                });
                
                // Show view all button and hide view less button
                if (viewAllDestBtn) viewAllDestBtn.style.display = 'inline-flex';
                this.style.display = 'none';
                this.style.visibility = 'hidden';
                this.style.opacity = '0';
                
                // Scroll back to destinations section
                document.getElementById('destinations').scrollIntoView({ behavior: 'smooth' });
            });
        }
    });
    
    // Destination card text color adjustment based on image brightness
    const destinations = document.querySelectorAll('.destination-card');
    destinations.forEach(destination => {
        const img = destination.querySelector('img');
        const title = destination.querySelector('.destination-content h3');
        const location = destination.querySelector('.destination-location');
        
        if (img && title && location) {
            // Create a new image object to handle the loading
            const tempImg = new Image();
            tempImg.crossOrigin = "Anonymous";
            tempImg.src = img.src;
            
            tempImg.onload = function() {
                try {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    
                    canvas.width = tempImg.width;
                    canvas.height = tempImg.height;
                    context.drawImage(tempImg, 0, 0, tempImg.width, tempImg.height);
                    
                    // Get the pixel data from the bottom area where text is displayed
                    const imageData = context.getImageData(0, Math.floor(tempImg.height * 0.7), tempImg.width, Math.floor(tempImg.height * 0.3));
                    const data = imageData.data;
                    
                    // Calculate average brightness
                    let brightness = 0;
                    for (let i = 0; i < data.length; i += 4) {
                        brightness += ((data[i] * 299) + (data[i + 1] * 587) + (data[i + 2] * 114)) / 1000;
                    }
                    brightness = brightness / (data.length / 4);
                    
                    // Remove any existing classes
                    title.classList.remove('light-text', 'dark-text');
                    location.classList.remove('light-text', 'dark-text');
                    
                    // Apply appropriate text color
                    if (brightness < 128) {
                        title.classList.add('light-text');
                        location.classList.add('light-text');
                    } else {
                        title.classList.add('dark-text');
                        location.classList.add('dark-text');
                    }
                } catch (error) {
                    // Fallback to light text if there's an error
                    title.classList.add('light-text');
                    location.classList.add('light-text');
                }
            };
            
            tempImg.onerror = function() {
                // Fallback to light text if image fails to load
                title.classList.add('light-text');
                location.classList.add('light-text');
            };
        }
    });
    
    // Contact form submission
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton.disabled) {
                return; // Prevent double submission
            }
            
            // Disable button and start countdown
            submitButton.disabled = true;
            let secondsLeft = 20;
            
            const originalText = submitButton.textContent;
            submitButton.innerHTML = `Please wait ${secondsLeft}s`;
            
            const countdown = setInterval(() => {
                secondsLeft--;
                submitButton.innerHTML = `Please wait ${secondsLeft}s`;
                
                if (secondsLeft <= 0) {
                    clearInterval(countdown);
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            }, 1000);
            
            // Continue with form submission
            const formData = new FormData(this);
            const messageStatus = document.getElementById('messageStatus');
            
            // Change button text and disable it
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitButton.disabled = true;
            
            fetch('process_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                } else {
                    showMessage(data.message, 'info');
                    
                    if (data.warning) {
                        setTimeout(() => {
                            showMessage(data.warning, 'warning');
                        }, data.show_warning_after);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again later.', 'danger');
            });

            function showMessage(text, type) {
                const messageDiv = document.getElementById('message-container');
                messageDiv.textContent = text;
                messageDiv.className = `alert alert-${type}`;
                messageDiv.style.display = 'block';
            }
            
            // Reset form
            contactForm.reset();
            
            // Scroll to message
            messageStatus.scrollIntoView({behavior: 'smooth'});
            
            // Hide message after 5 seconds
            setTimeout(() => {
                messageStatus.style.display = 'none';
            }, 5000);
        });
    }
});

// Navbar background change on scroll
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('navbar-scrolled');
    } else {
        navbar.classList.remove('navbar-scrolled');
    }
});

// Initialize testimonial slider
const testimonialSlider = new Swiper('.testimonial-slider', {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    breakpoints: {
        768: {
            slidesPerView: 2,
        },
        1024: {
            slidesPerView: 3,
        }
    }
});

// Hover effects for destination cards
document.querySelectorAll('.destination-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.querySelector('img').style.transform = 'scale(1.1)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.querySelector('img').style.transform = 'scale(1)';
    });
});

// View All Packages functionality
document.addEventListener('DOMContentLoaded', function() {
    // View All Packages functionality
    const viewAllPackagesBtn = document.getElementById('view-all-packages');
    const viewLessPackagesBtn = document.getElementById('view-less-packages');
    
    if (viewAllPackagesBtn) {
        viewAllPackagesBtn.addEventListener('click', function() {
            // Show all hidden packages
            document.querySelectorAll('.hidden-package').forEach(function(package) {
                package.style.display = 'block';
                setTimeout(() => {
                    package.style.opacity = '1';
                    package.style.transform = 'translateY(0)';
                }, 10);
            });
            
            // Hide "View All" and show "View Less" button
            this.style.display = 'none';
            if (viewLessPackagesBtn) viewLessPackagesBtn.style.display = 'inline-flex';
        });
    }
    
    if (viewLessPackagesBtn) {
        viewLessPackagesBtn.addEventListener('click', function() {
            // Hide extra packages
            document.querySelectorAll('.hidden-package').forEach(function(package) {
                package.style.opacity = '0';
                package.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    package.style.display = 'none';
                }, 400);
            });
            
            // Show "View All" and hide "View Less" button
            if (viewAllPackagesBtn) viewAllPackagesBtn.style.display = 'inline-flex';
            this.style.display = 'none';
            
            // Scroll back to packages section
            document.getElementById('packages').scrollIntoView({ behavior: 'smooth' });
        });
    }
});

// Add smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            const navbarHeight = document.querySelector('.navbar').offsetHeight;
            const targetPosition = targetElement.offsetTop - navbarHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    }); 
});
