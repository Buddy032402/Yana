<footer class="footer" style="background-color: #4169E1; color: white; padding: 3rem 0; margin-top: 3rem;">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 style="font-weight: 600; margin-bottom: 1.2rem;">Yana Byahe Na Travel and Tours</h5>
                <p style="line-height: 1.6;">Your journey begins with us. Discover the world's most amazing destinations with our expert-crafted travel packages.</p>
                <div class="footer-social" style="margin-top: 1.5rem;">
                    <a href="https://www.facebook.com/YanaBiyaheNa" target="_blank" class="social-link facebook" style="background-color: #1877F2; color: white; font-size: 1.2rem; margin-right: 15px; padding: 10px; border-radius: 50%; display: inline-flex; width: 40px; height: 40px; justify-content: center; align-items: center;"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.tiktok.com/@yanabiyahena?_t=ZS-8utlTr2THQV&_r=1" target="_blank" class="social-link tiktok" style="background-color: #000000; color: white; font-size: 1.2rem; margin-right: 15px; padding: 10px; border-radius: 50%; display: inline-flex; width: 40px; height: 40px; justify-content: center; align-items: center;"><i class="fab fa-tiktok"></i></a>
                    <a href="https://www.instagram.com/judzdba" class="social-link instagram" style="color: white; background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); font-size: 1.2rem; padding: 10px; border-radius: 50%; display: inline-flex; width: 40px; height: 40px; justify-content: center; align-items: center;"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h5 style="font-weight: 600; margin-bottom: 1.2rem;">Quick Links</h5>
                <ul class="list-unstyled" style="line-height: 2;">
                    <li><a href="#destinations" style="color: white; text-decoration: none; transition: all 0.3s;">Destinations</a></li>
                    <li><a href="#packages" style="color: white; text-decoration: none; transition: all 0.3s;">Packages</a></li>
                    <li><a href="#about" style="color: white; text-decoration: none; transition: all 0.3s;">About Us</a></li>
                    <li><a href="#contact" style="color: white; text-decoration: none; transition: all 0.3s;">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 style="font-weight: 600; margin-bottom: 1.2rem;">Contact Info</h5>
                <ul class="list-unstyled" style="line-height: 1.8;">
                    <li style="margin-bottom: 10px;"><i class="fas fa-map-marker-alt me-2"></i>Space 41-5 Generoso St. Corner Cervantes Bo. Obrero, Davao City, Davao City, Philippines, 8000
                    Address </li>
                    <li style="margin-bottom: 10px;"><i class="fas fa-phone me-2"></i> Mobile 0917 311 1569 / WhatsApp +63 917 311 1569 </li>
                    <li><i class="fas fa-envelope me-2"></i> yanabiyahena@gmail.com</li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 style="font-weight: 600; margin-bottom: 1.2rem;">Newsletter</h5>
                <p style="margin-bottom: 1rem;">Subscribe to our newsletter for travel updates and exclusive offers.</p>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email" style="border-radius: 4px 0 0 4px;">
                        <button class="btn btn-light" type="submit" style="border-radius: 0 4px 4px 0; font-weight: 500;">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        <hr class="mt-4 mb-4" style="opacity: 0.2; background-color: white;">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; 2015 Yana Byahe Na Travel and Tours All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-light me-3" style="text-decoration: none;">Privacy Policy</a>
                <a href="#" class="text-light me-3" style="text-decoration: none;">Terms of Service</a>
                <a href="#" class="text-light" style="text-decoration: none;">FAQ</a>
            </div>
        </div>
    
        <!-- Developer Credits Section -->
        <div class="text-center mt-3">
            <button class="btn btn-link text-light" onclick="toggleCredits()" style="text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-code"></i> Developer Credits
            </button>
            <div id="developerCredits" style="display: none; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-top: 10px;">
                <p class="mb-2" style="font-size: 0.9rem;">Developed by:</p>
                <p class="mb-0" style="font-size: 0.9rem;">
                    <strong>Marc Jullan M. Pague</strong><br>
                    <strong>Mike Angelo Collamat</strong><br>
                    <strong>Ronald Christian Eder</strong><br>
                    <small>BSIT Student - Assumption College of Davao</small>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Add this script before closing body tag -->
<script>
function toggleCredits() {
    const credits = document.getElementById('developerCredits');
    if (credits.style.display === 'none') {
        credits.style.display = 'block';
    } else {
        credits.style.display = 'none';
    }
}
</script>
