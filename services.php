<?php
require_once 'includes/functions.php';
include 'includes/header.php';

// Array of 8 detailed hotel services
$services = [
    [
        'id'          => 'meeting-rooms',
        'title'       => 'Executive Meeting Rooms',
        'subtitle'    => 'State-of-the-art technology tailored for seamless corporate assemblies.',
        'image'       => 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Modern executive meeting room with AV setup',
        'description' => 'Designed for productivity and high-level collaboration, our executive meeting rooms feature ultra-fast fiber connection, integrated audiovisual systems, acoustic soundproofing, and ergonomic seating. Full catering support and personal tech concierge are available throughout your session.',
        'features'    => ['High-Speed Wi-Fi', 'Interactive Displays', 'Gourmet Catering Options', 'Capacity: Up to 30 Guests']
    ],
    [
        'id'          => 'grand-boardroom',
        'title'       => 'The Grand Boardroom',
        'subtitle'    => 'Prestige and privacy for high-stakes executive decisions.',
        'image'       => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Luxury boardroom table with executive leather chairs',
        'description' => 'Commanding views of the coastal grounds complement an atmosphere of discretion and luxury. Featuring custom mahogany furnishings, video conferencing infrastructure, secretarial support lounges, and private espresso service, the Grand Boardroom hosts your leadership teams with unparalleled distinction.',
        'features'    => ['4K Video Conferencing', 'Private Antechamber', 'Dedicated Espresso Bar', 'Capacity: Up to 18 Guests']
    ],
    [
        'id'          => 'spa-wellness',
        'title'       => 'Serenity Spa & Massage',
        'subtitle'    => 'Holistic wellness treatments engineered for ultimate relaxation.',
        'image'       => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Spa massage room with warm lighting and ocean views',
        'description' => 'Rejuvenate body and spirit with custom aromatherapies, deep-tissue sports massage, hot stone rituals, and soothing body wraps. Our licensed therapists integrate oceanfront elements with traditional healing protocols to help you unwind completely.',
        'features'    => ['Deep Tissue & Swedish Massage', 'Steam Sauna Access', 'Organic Essential Oils', 'Couples Treatment Rooms']
    ],
    [
        'id'          => 'oceanfront-weddings',
        'title'       => 'Oceanfront Wedding Ceremonies',
        'subtitle'    => 'Bespoke coastal celebrations designed for an unforgettable life event.',
        'image'       => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Oceanfront wedding setup with floral arch on beach lawn',
        'description' => 'Say your vows against a backdrop of rolling waves and golden sunsets. Our wedding package provides exclusive access to our private beach lawn, custom floral curation, tailored multi-course banquet menus, and a dedicated bridal suite with on-site event coordination.',
        'features'    => ['Private Beach Lawn Access', 'Custom Banquet Menus', 'Dedicated Event Planner', 'Capacity: Up to 250 Guests']
    ],
    [
        'id'          => 'grand-ballroom',
        'title'       => 'The Imperial Ballroom',
        'subtitle'    => 'Sophisticated venue space for galas, banquets, and major celebrations.',
        'image'       => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Luxury imperial ballroom set up for evening gala dinner',
        'description' => 'Spanning over 6,000 square feet with crystal chandeliers and flexible partitions, the Imperial Ballroom accommodates grand galas, award dinners, and international summits. Enjoy bespoke lighting arrays, stage setups, and world-class culinary presentations.',
        'features'    => ['Flexible Layout Configurations', 'Professional Stage & Lighting', 'Valet Parking Included', 'Capacity: Up to 400 Guests']
    ],
    [
        'id'          => 'fine-dining',
        'title'       => 'Private Dining & Culinary Experiences',
        'subtitle'    => 'Exclusive culinary journeys curated by our executive chef.',
        'image'       => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Intimate private dining room with gourmet dishes',
        'description' => 'Savor multi-course tasting menus featuring fresh-caught seafood and locally sourced organic produce paired with reserve wines. Available for intimate family celebrations, VIP corporate dinners, or private chef table experiences overlooking the ocean.',
        'features'    => ['Custom Sommelier Wine Pairing', 'Private Chef Experience', 'Secluded Ocean View Pavilion', 'Capacity: Up to 24 Guests']
    ],
    [
        'id'          => 'infinity-pool-cabanas',
        'title'       => 'Luxury Poolside Cabana Reservations',
        'subtitle'    => 'VIP lounging at our signature oceanfront infinity pool.',
        'image'       => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Infinity pool deck with luxury cabanas and sun loungers',
        'description' => 'Reserve a private shaded cabana for full-day poolside luxury. Package includes dedicated butler service, chilled towel service, tropical fruit platters, artisan cocktails, and direct access to our heated oceanfront infinity pool.',
        'features'    => ['Personal Butler Attendance', 'Chilled Refreshment Basket', 'Private Sunbed & Shade', 'Full-Day Exclusive Reservation']
    ],
    [
        'id'          => 'yacht-excursions',
        'title'       => 'Private Yacht & Coastal Charters',
        'subtitle'    => 'Exclusive ocean adventures and sunset cruises along the coastline.',
        'image'       => 'https://images.unsplash.com/photo-1530521954074-e64f6810b32d?auto=format&fit=crop&w=800&q=80',
        'alt'         => 'Luxury private yacht cruising near ocean shores',
        'description' => 'Embark on a private yacht excursion tailored to your schedule. Whether planning a sunset champagne cruise, deep-sea fishing tour, or private snorkeling excursion, our vessel comes fully crewed with gourmet catering and watersports equipment.',
        'features'    => ['Fully Crewed Vessel', 'Gourmet Catering & Wine', 'Water Sports Equipment', 'Capacity: Up to 12 Guests']
    ]
];
?>

<style>
/* Page Header Section */
.services-header {
    text-align: center;
    max-width: 850px;
    margin: 3rem auto 4rem auto;
    padding: 0 1rem;
}

.services-header h1 {
    color: var(--navy, #0a192f);
    font-size: 2.8rem;
    margin-bottom: 1rem;
    font-family: 'Playfair Display', serif;
}

.services-header p {
    color: var(--text-muted, #64748b);
    font-size: 1.1rem;
    line-height: 1.8;
}

/* Service Row Layout */
.service-list {
    display: flex;
    flex-direction: column;
    gap: 4rem;
    margin-bottom: 5rem;
}

.service-card {
    display: flex;
    align-items: stretch;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

/* Alternating Image Left/Right logic */
.service-card.row-reverse {
    flex-direction: row-reverse;
}

.service-img-wrap {
    flex: 1;
    min-width: 45%;
    position: relative;
    overflow: hidden;
}

.service-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.service-card:hover .service-img-wrap img {
    transform: scale(1.05);
}

.service-content {
    flex: 1;
    padding: 3rem 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.service-title {
    color: var(--navy, #0a192f);
    font-size: 2rem;
    margin-bottom: 0.5rem;
    font-family: 'Playfair Display', serif;
}

.service-subtitle {
    color: var(--gold, #c5a059);
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1.25rem;
}

.service-description {
    color: var(--text-muted, #64748b);
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 1.5rem;
    text-align: justify;
}

.service-features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--navy, #0a192f);
    font-weight: 500;
}

.feature-item::before {
    content: "✓";
    color: var(--gold, #c5a059);
    font-weight: bold;
}

.book-btn {
    align-self: flex-start;
    padding: 0.85rem 2rem;
    background-color: var(--navy, #0a192f);
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    text-decoration: none;
}

.book-btn:hover {
    background-color: var(--gold, #c5a059);
    color: #0a192f;
}

/* Modal Styling */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(10, 25, 47, 0.85);
    z-index: 4000;
    justify-content: center;
    align-items: center;
    padding: 1rem;
}

.modal-overlay.active {
    display: flex;
}

.modal-box {
    background: #ffffff;
    width: 100%;
    max-width: 550px;
    border-radius: 10px;
    padding: 2.5rem;
    position: relative;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-close {
    position: absolute;
    top: 1rem;
    right: 1.5rem;
    font-size: 2rem;
    cursor: pointer;
    color: var(--text-muted, #64748b);
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.4rem;
    font-weight: 600;
    color: var(--navy, #0a192f);
    font-size: 0.9rem;
}

.form-group input, 
.form-group select, 
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.95rem;
    outline: none;
}

.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
    border-color: var(--navy, #0a192f);
}

/* Responsive Breakpoints */
@media (max-width: 992px) {
    .service-card, .service-card.row-reverse {
        flex-direction: column;
    }
    
    .service-img-wrap {
        height: 300px;
        min-width: 100%;
    }

    .service-content {
        padding: 2rem 1.5rem;
    }

    .service-features {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container" style="margin-top:2rem;">
    <!-- Page Header -->
    <div class="services-header">
        <h1>Hotel Services & Space Reservations</h1>
        <p>Explore our exclusive collection of event venues, wellness retreats, and tailored hospitality services. Reserve your preferred space or experience online below.</p>
    </div>

    <!-- Alternating Services List -->
    <div class="service-list">
        <?php foreach ($services as $index => $s): 
            $isReverse = ($index % 2 !== 0) ? 'row-reverse' : '';
        ?>
            <div class="service-card <?php echo $isReverse; ?>" id="<?php echo $s['id']; ?>">
                <div class="service-img-wrap">
                    <img src="<?php echo $s['image']; ?>" alt="<?php echo sanitize($s['alt']); ?>" loading="lazy">
                </div>
                <div class="service-content">
                    <h2 class="service-title"><?php echo sanitize($s['title']); ?></h2>
                    <div class="service-subtitle"><?php echo sanitize($s['subtitle']); ?></div>
                    <p class="service-description"><?php echo sanitize($s['description']); ?></p>
                    
                    <div class="service-features">
                        <?php foreach ($s['features'] as $feature): ?>
                            <div class="feature-item"><?php echo sanitize($feature); ?></div>
                        <?php endforeach; ?>
                    </div>

                    <button class="book-btn" onclick="openBookingModal('<?php echo sanitize(addslashes($s['title'])); ?>')">
                        Book This Space / Service
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="modal-overlay" onclick="closeModalOnOverlay(event)">
    <div class="modal-box">
        <span class="modal-close" onclick="closeBookingModal()">&times;</span>
        <h3 style="color:var(--navy,#0a192f); font-size:1.8rem; margin-bottom:0.5rem; font-family:'Playfair Display', serif;">Reserve Experience</h3>
        <p style="color:var(--text-muted,#64748b); font-size:0.95rem; margin-bottom:1.5rem;">Fill out the details below to request a reservation for your chosen venue or service.</p>
        
        <form action="process-booking.php" method="POST">
            <div class="form-group">
                <label for="service_name">Selected Service / Space</label>
                <input type="text" id="service_name" name="service_name" readonly style="background-color:#f1f5f9; font-weight:600;">
            </div>

            <div class="form-group">
                <label for="guest_name">Full Name</label>
                <input type="text" id="guest_name" name="guest_name" required placeholder="John Doe">
            </div>

            <div class="form-group">
                <label for="guest_email">Email Address</label>
                <input type="email" id="guest_email" name="guest_email" required placeholder="john@example.com">
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="booking_date">Preferred Date</label>
                    <input type="date" id="booking_date" name="booking_date" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="guest_count">Attendees / Guests</label>
                    <input type="number" id="guest_count" name="guest_count" min="1" value="1" required>
                </div>
            </div>

            <div class="form-group">
                <label for="special_requests">Special Requests / Notes</label>
                <textarea id="special_requests" name="special_requests" rows="3" placeholder="Specify catering preferences, time slots, setup configurations, etc."></textarea>
            </div>

            <button type="submit" class="book-btn" style="width:100%; text-align:center; margin-top:0.5rem;">Submit Reservation Request</button>
        </form>
    </div>
</div>

<script>
function openBookingModal(serviceTitle) {
    document.getElementById('service_name').value = serviceTitle;
    document.getElementById('bookingModal').classList.add('active');
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.remove('active');
}

function closeModalOnOverlay(e) {
    if (e.target.id === 'bookingModal') {
        closeBookingModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>