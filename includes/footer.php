<!-- Font Awesome CDN for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
footer {
    background-color: var(--navy, #0a192f);
    color: var(--cream, #f8fafc);
    padding: 3.5rem 1.5rem 1.5rem 1.5rem;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 2.5rem;
    align-items: start;
}

/* --- Larger Logo & Name Alignment --- */
.footer-brand .logo-container {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
    text-decoration: none;
    margin-bottom: 1.25rem;
}

.footer-brand .logo-img {
    height: 70px;
    width: auto;
    object-fit: contain;
    display: block;
}

.footer-brand .logo-text {
    color: var(--gold, #c5a059);
    font-size: 1.8rem;
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    line-height: 1.2;
}

/* --- Clean List Structures --- */
.footer-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-brand .footer-list li {
    color: #94a3b8;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.footer-links h4,
.footer-social h4 {
    color: var(--gold, #c5a059);
    font-size: 1.25rem;
    margin-bottom: 1.25rem;
    font-family: 'Playfair Display', serif;
}

.footer-links .footer-list li {
    margin-bottom: 0.6rem;
}

.footer-links a {
    color: var(--cream, #f8fafc);
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: var(--gold, #c5a059);
}

/* --- Vertical Social Icons List --- */
.social-vertical-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.social-vertical-list li {
    margin-bottom: 0.6rem;
}

.social-vertical-list a {
    color: var(--cream, #f8fafc);
    text-decoration: none;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    transition: color 0.3s ease;
}

.social-vertical-list a i {
    width: 20px;
    text-align: center;
    color: var(--gold, #c5a059);
}

.social-vertical-list a:hover {
    color: var(--gold, #c5a059);
}

/* --- Footer Bottom --- */
.footer-bottom {
    text-align: center;
    margin-top: 3rem;
    padding-top: 1.25rem;
    border-top: 1px solid #172a45;
    font-size: 0.85rem;
    color: #94a3b8;
}

.footer-bottom a {
    color: var(--gold, #c5a059);
    text-decoration: none;
}

.footer-bottom a:hover {
    text-decoration: underline;
}

/* --- STICKY FLOATING WHATSAPP BUTTON --- */
.whatsapp-sticky-btn {
    position: fixed !important;
    bottom: 25px !important;
    right: 25px !important;
    background-color: #25d366 !important;
    color: #ffffff !important;
    border-radius: 50px !important;
    padding: 12px 20px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    font-family: inherit !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.35) !important;
    z-index: 999999 !important;
    transition: all 0.3s ease !important;
}

.whatsapp-sticky-btn i {
    font-size: 22px !important;
}

.whatsapp-sticky-btn:hover {
    background-color: #128c7e !important;
    color: #ffffff !important;
    transform: scale(1.05) translateY(-3px) !important;
    box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.45) !important;
}
</style>

<footer>
    <div class="footer-container">
        <div class="footer-grid">
            
            <!-- Left Column: Logo First, Then Name -->
            <div class="footer-brand">
                <a href="index.php" class="logo-container">
                    <img src="assets/images/logos.png" alt="Azure Haven Logo" class="logo-img">
                    <span class="logo-text">Azure Haven</span>
                </a>
                <ul class="footer-list">
                    <li>Sanctuary of coastal tranquility, refined luxury, and unforgettable memories.</li>
                    <li><strong>Address:</strong> 205-20210 Ocean Drive, Mombasa Kenya</li>
                    <li><strong>Email:</strong> kibetvictor0603@gmail.com</li>
                    <li><strong>Call:</strong> +254700276320 </li>
                </ul>
            </div>

            <!-- Middle Column: Quick Links List -->
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul class="footer-list">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="rooms.php">Rooms & Suites</a></li>
                    <li><a href="service.php">Services & Venues</a></li>
                    <li><a href="meals.php">Meals & Beverages</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>

            <!-- Right Column: Social Media List -->
            <div class="footer-social">
                <h4>Connect With Us</h4>
                <ul class="social-vertical-list">
                    <li><a href="https://x.com" target="_blank" aria-label="Twitter / X"><i class="fa-brands fa-x-twitter"></i>Twitter</a></li>
                    <li><a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i>Facebook</a></li>
                    <li><a href="https://wa.me/254700276320?text=Hello%20Azure%20Haven%20Hotel,%20I%20have%20an%20inquiry." target="_blank" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i>WhatsApp</a></li>
                    <li><a href="https://tiktok.com" target="_blank" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i>TikTok</a></li>
                    <li><a href="https://youtube.com" target="_blank" aria-label="YouTube"><i class="fa-brands fa-youtube"></i>YouTube</a></li>
                    <li><a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i>LinkedIn</a></li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom Credit -->
        <div class="footer-bottom">
            &copy; 2026 Azure Haven Hotel. All Rights Reserved. Developed by <a href="https://victorkibet.vercel.app/" target="_blank">Victor Kibet</a>
        </div>
    </div>
</footer>

<!-- Sticky Floating WhatsApp Button -->
<a href="https://wa.me/254700276320?text=Hello%20Azure%20Haven%20Hotel,%20I%20have%20an%20inquiry." 
   target="_blank" 
   class="whatsapp-sticky-btn" 
   aria-label="Chat with Azure Haven Hotel on WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
    <span>Chat with us</span>
</a>

<script src="assets/js/main.js"></script>
</body>
</html>