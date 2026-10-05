<?php 
require_once 'includes/functions.php';
$pdo = getDBConnection();

// Retrieve up to 12 rooms from database
$dbRooms = [];
try {
    $dbRooms = $pdo->query("SELECT * FROM rooms LIMIT 12")->fetchAll();
} catch (Exception $e) {
    $dbRooms = [];
}

// Compute remaining units per room (snapshot across all active bookings)
$remainingMap = [];
$totalQtyMap  = [];
try {
    $rows = $pdo->query(
        "SELECT r.id, r.total_quantity,
                COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending','pending_payment') THEN b.quantity ELSE 0 END), 0) AS booked
         FROM rooms r
         LEFT JOIN bookings b ON b.room_id = r.id
         GROUP BY r.id"
    )->fetchAll();
    foreach ($rows as $row) {
        $totalQtyMap[(int)$row['id']]  = (int)$row['total_quantity'];
        $remainingMap[(int)$row['id']] = max(0, (int)$row['total_quantity'] - (int)$row['booked']);
    }
} catch (Exception $e) {
    $remainingMap = [];
    $totalQtyMap  = [];
}

// Fallback pool ensuring at least 12 distinct cards with price range $102 - $250
$defaultCards = [
    ['id' => 1, 'title' => 'Deluxe Oceanfront Suite', 'description' => 'Panoramic balcony views with personal king bed and marble bath.', 'price' => 245.00, 'img' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'],
    ['id' => 2, 'title' => 'Coastal Studio Room', 'description' => 'Cozy modern space perfect for solo travelers or couples.', 'price' => 102.00, 'img' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80'],
    ['id' => 3, 'title' => 'Executive Bay View', 'description' => 'Spacious accommodation equipped with work area and private terrace.', 'price' => 175.00, 'img' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80'],
    ['id' => 4, 'title' => 'Sunset Palm Haven', 'description' => 'Relax with floor-to-ceiling windows overlooking tropical palm trees.', 'price' => 135.00, 'img' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=600&q=80'],
    ['id' => 5, 'title' => 'Seaside Villa Suite', 'description' => 'Premium multi-room layout with direct resort beach access.', 'price' => 250.00, 'img' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80'],
    ['id' => 6, 'title' => 'Azure Breeze Chamber', 'description' => 'Quiet corner suite offering constant ocean breeze and plush seating.', 'price' => 118.00, 'img' => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=600&q=80'],
    ['id' => 7, 'title' => 'Royal Horizon Penthouse', 'description' => 'Top-floor luxury complete with private Jacuzzi and sun loungers.', 'price' => 240.00, 'img' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=600&q=80'],
    ['id' => 8, 'title' => 'Garden Coast Retreat', 'description' => 'Peaceful ground room adjacent to our lush private coastal garden.', 'price' => 110.00, 'img' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=600&q=80'],
    ['id' => 9, 'title' => 'Coral Reef Family Suite', 'description' => 'Double queen bedding setup tailored for family getaways.', 'price' => 198.00, 'img' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=600&q=80'],
    ['id' => 10, 'title' => 'Nautical Vista Balcony', 'description' => 'Elevated room featuring panoramic harbor views and artisan décor.', 'price' => 162.00, 'img' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=600&q=80'],
    ['id' => 11, 'title' => 'Serenity Ocean Cottage', 'description' => 'Charming standalone cottage steps away from white sand shoreline.', 'price' => 215.00, 'img' => 'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=600&q=80'],
    ['id' => 12, 'title' => 'Minimalist Dune Studio', 'description' => 'Sleek contemporary design emphasizing light, simplicity, and calm.', 'price' => 105.00, 'img' => 'https://images.unsplash.com/photo-1616046229478-9901c5536a45?auto=format&fit=crop&w=600&q=80']
];

// Merge DB results with fallback items if database has fewer than 12
$featuredRooms = [];
for ($i = 0; $i < 12; $i++) {
    if (isset($dbRooms[$i])) {
        $room = $dbRooms[$i];
        // Ensure price is dynamically bounded between $102 and $250
        $rawPrice = floatval($room['price'] ?? 0);
        if ($rawPrice < 102 || $rawPrice > 250) {
            $room['price'] = 102 + (($i * 13.5) % 148);
        }
        $room['img'] = $defaultCards[$i]['img'];

        // Attach availability (fallback to 10 total / 10 remaining if not in DB maps)
        $rid = (int)$room['id'];
        $room['total_units']     = $totalQtyMap[$rid]  ?? 10;
        $room['remaining_units'] = $remainingMap[$rid] ?? 10;

        $featuredRooms[] = $room;
    } else {
        $fallback = $defaultCards[$i];
        $rid = (int)$fallback['id'];
        $fallback['total_units']     = $totalQtyMap[$rid]  ?? 10;
        $fallback['remaining_units'] = $remainingMap[$rid] ?? 10;
        $featuredRooms[] = $fallback;
    }
}

include 'includes/header.php'; 
?>

<style>
/* Availability badge on featured cards */
.avail-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.avail-ok   { background: #d1fae5; color: #065f46; }
.avail-low  { background: #fef3c7; color: #92400e; }
.avail-none { background: #fee2e2; color: #991b1b; }

/* Subtle sold-out treatment */
.card-sold-out .card-img { filter: grayscale(35%) brightness(0.92); }
.btn-disabled {
  background: #cbd5e1 !important;
  color: #64748b !important;
  cursor: not-allowed !important;
  pointer-events: none;
  display: block;
  text-align: center;
}
</style>

<!-- Edge-to-Edge Hero Animation Slider -->
<section class="hero-slider-wrapper">
    <!-- Slide 1 -->
    <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-content">
            <h1>Experience Coastal Perfection</h1>
            <p>Indulge in uncompromised luxury and panoramic ocean views designed to elevate your seaside escape.</p>
            <a href="rooms.php" class="btn btn-gold">Explore Suites</a>
        </div>
    </div>

    <!-- Slide 2 -->
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-content">
            <h1>Your Private Paradise Awaits</h1>
            <p>Unwind in exquisite bespoke suites crafted for tranquility, elegance, and supreme comfort.</p>
            <a href="rooms.php" class="btn btn-gold">Discover Our Rooms</a>
        </div>
    </div>

    <!-- Slide 3 -->
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-content">
            <h1>Unrivaled Oceanfront Luxury</h1>
            <p>Wake up to azure tides, serene breezes, and personalized world-class hospitality.</p>
            <a href="booking.php" class="btn btn-gold">Reserve Your Stay</a>
        </div>
    </div>

    <!-- Slide 4 -->
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-content">
            <h1>Culinary & Wellness Excellence</h1>
            <p>Immerse your senses in gourmet coastal dining and restorative oceanfront spa treatments.</p>
            <a href="about.php" class="btn btn-gold">Explore Resort Amenities</a>
        </div>
    </div>

    <!-- Slide 5 -->
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1920&q=80');">
        <div class="hero-content">
            <h1>Timeless Elegance by the Sea</h1>
            <p>Create unforgettable memories in an architectural sanctuary tailored just for you.</p>
            <a href="contact.php" class="btn btn-gold">Plan Your Retreat</a>
        </div>
    </div>

    <!-- Dynamic Pagination Indicators -->
    <div class="hero-dots" id="heroDots"></div>
</section>

<!-- Full Width Edge-to-Edge Cards Carousel Section -->
<section class="cards-section-fullwidth">
    <div class="section-header">
        <h2>Featured Accommodations</h2>
        <p>Handpicked luxury sanctuaries with prices starting from $102 to $250 per night.</p>
    </div>

    <div class="card-carousel-container">
        <div class="card-carousel-track" id="cardCarouselTrack">
            <?php foreach ($featuredRooms as $index => $room): ?>
                <?php
                    $remain = (int)($room['remaining_units'] ?? 0);
                    $total  = (int)($room['total_units'] ?? 0);
                    $badgeCls = $remain <= 0 ? 'avail-none' : ($remain <= 2 ? 'avail-low' : 'avail-ok');
                    $badgeLbl = $remain <= 0 ? 'Sold Out' : ($remain . ' of ' . $total . ' units available');
                    $soldOut  = $remain <= 0;
                ?>
                <div class="card-slide">
                    <div class="card <?php echo $soldOut ? 'card-sold-out' : ''; ?>">
                        <img src="<?php echo $room['img']; ?>" class="card-img" alt="<?php echo sanitize($room['title']); ?>">
                        <div class="card-body">
                            <span class="avail-badge <?php echo $badgeCls; ?>"><?php echo $badgeLbl; ?></span>
                            <h3><?php echo sanitize($room['title']); ?></h3>
                            <p style="color:var(--text-muted);margin:0.5rem 0;"><?php echo sanitize($room['description']); ?></p>
                            <p style="font-weight:600;color:var(--gold);font-size:1.2rem;">$<?php echo number_format($room['price'], 2); ?> / night</p>

                            <?php if ($soldOut): ?>
                                <a href="room-detail.php?id=<?php echo $room['id']; ?>" class="btn btn-disabled" style="margin-top:1rem;width:100%;">
                                    Currently Sold Out
                                </a>
                            <?php else: ?>
                                <a href="room-detail.php?id=<?php echo $room['id']; ?>" class="btn btn-outline" style="margin-top:1rem;width:100%;text-align:center;">View Details</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card-carousel-nav">
            <button class="card-nav-btn" id="cardPrevBtn" aria-label="Previous Cards">&#10094;</button>
            <div class="card-carousel-dots" id="cardDots"></div>
            <button class="card-nav-btn" id="cardNextBtn" aria-label="Next Cards">&#10095;</button>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>