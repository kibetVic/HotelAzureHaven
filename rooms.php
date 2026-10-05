<?php
require_once 'includes/functions.php';
$pdo = getDBConnection();

$typeFilter = $_GET['type'] ?? '';

// Retrieve DB Rooms
$dbRooms = [];
try {
    $sql = "SELECT * FROM rooms WHERE status = 'available'";
    $params = [];
    if ($typeFilter) {
        $sql .= " AND type = :type";
        $params['type'] = $typeFilter;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dbRooms = $stmt->fetchAll();
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

// 12 Fallback Room Dataset Matching index.php
$defaultCards = [
    ['id' => 1, 'type' => 'Suite', 'title' => 'Deluxe Oceanfront Suite', 'description' => 'Panoramic balcony views with personal king bed and marble bath.', 'price' => 245.00, 'capacity' => 2, 'size_sqm' => 55, 'img' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'],
    ['id' => 2, 'type' => 'Standard', 'title' => 'Coastal Studio Room', 'description' => 'Cozy modern space perfect for solo travelers or couples.', 'price' => 102.00, 'capacity' => 2, 'size_sqm' => 30, 'img' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80'],
    ['id' => 3, 'type' => 'Deluxe', 'title' => 'Executive Bay View', 'description' => 'Spacious accommodation equipped with work area and private terrace.', 'price' => 175.00, 'capacity' => 3, 'size_sqm' => 45, 'img' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80'],
    ['id' => 4, 'type' => 'Standard', 'title' => 'Sunset Palm Haven', 'description' => 'Relax with floor-to-ceiling windows overlooking tropical palm trees.', 'price' => 135.00, 'capacity' => 2, 'size_sqm' => 35, 'img' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=600&q=80'],
    ['id' => 5, 'type' => 'Presidential', 'title' => 'Seaside Villa Suite', 'description' => 'Premium multi-room layout with direct resort beach access.', 'price' => 250.00, 'capacity' => 4, 'size_sqm' => 90, 'img' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80'],
    ['id' => 6, 'type' => 'Standard', 'title' => 'Azure Breeze Chamber', 'description' => 'Quiet corner suite offering constant ocean breeze and plush seating.', 'price' => 118.00, 'capacity' => 2, 'size_sqm' => 32, 'img' => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=600&q=80'],
    ['id' => 7, 'type' => 'Presidential', 'title' => 'Royal Horizon Penthouse', 'description' => 'Top-floor luxury complete with private Jacuzzi and sun loungers.', 'price' => 240.00, 'capacity' => 4, 'size_sqm' => 110, 'img' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=600&q=80'],
    ['id' => 8, 'type' => 'Standard', 'title' => 'Garden Coast Retreat', 'description' => 'Peaceful ground room adjacent to our lush private coastal garden.', 'price' => 110.00, 'capacity' => 2, 'size_sqm' => 34, 'img' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=600&q=80'],
    ['id' => 9, 'type' => 'Family', 'title' => 'Coral Reef Family Suite', 'description' => 'Double queen bedding setup tailored for family getaways.', 'price' => 198.00, 'capacity' => 5, 'size_sqm' => 65, 'img' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=600&q=80'],
    ['id' => 10, 'type' => 'Deluxe', 'title' => 'Nautical Vista Balcony', 'description' => 'Elevated room featuring panoramic harbor views and artisan décor.', 'price' => 162.00, 'capacity' => 2, 'size_sqm' => 40, 'img' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=600&q=80'],
    ['id' => 11, 'type' => 'Suite', 'title' => 'Serenity Ocean Cottage', 'description' => 'Charming standalone cottage steps away from white sand shoreline.', 'price' => 215.00, 'capacity' => 3, 'size_sqm' => 50, 'img' => 'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=600&q=80'],
    ['id' => 12, 'type' => 'Standard', 'title' => 'Minimalist Dune Studio', 'description' => 'Sleek contemporary design emphasizing light, simplicity, and calm.', 'price' => 105.00, 'capacity' => 2, 'size_sqm' => 28, 'img' => 'https://images.unsplash.com/photo-1616046229478-9901c5536a45?auto=format&fit=crop&w=600&q=80']
];

// Apply category filter to dataset
$rooms = [];
for ($i = 0; $i < 12; $i++) {
    $item = isset($dbRooms[$i]) ? $dbRooms[$i] : $defaultCards[$i];
    $item['img'] = $defaultCards[$i]['img'];

    // Fallback price logic matching index.php
    $rawPrice = floatval($item['price'] ?? 0);
    if ($rawPrice < 102 || $rawPrice > 250) {
        $item['price'] = 102 + (($i * 13.5) % 148);
    }

    // Attach availability info (fallback to default total_quantity = 10)
    $rid = (int)$item['id'];
    $item['remaining_units'] = $remainingMap[$rid] ?? 10;
    $item['total_units']     = $totalQtyMap[$rid]  ?? 10;

    if (!$typeFilter || (isset($item['type']) && strtolower($item['type']) === strtolower($typeFilter))) {
        $rooms[] = $item;
    }
}

include 'includes/header.php';
?>

<style>
/* Full Screen Edge-to-Edge Container for Rooms */
.rooms-fullwidth-wrapper {
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  padding: 6rem 2rem 4rem;
  background-color: var(--cream);
}

.rooms-header-banner {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 3rem;
}

.rooms-header-banner h1 {
  font-size: 3rem;
  color: var(--navy);
  margin-bottom: 0.75rem;
}

.rooms-header-banner p {
  color: var(--text-muted);
  font-size: 1.15rem;
}

.rooms-filter-bar {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 3rem;
}

.rooms-grid-edge {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  width: 100%;
}

/* Availability badge */
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
</style>

<div class="rooms-fullwidth-wrapper">
    <div class="rooms-header-banner">
        <h1>Accommodations & Suites</h1>
        <p>Choose from our luxury beachside sanctuaries. Select any suite to view detailed gallery images, amenities, and availability.</p>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="rooms-filter-bar">
        <select name="type" class="form-control" style="max-width:280px; background:var(--white);">
            <option value="">All Categories</option>
            <option value="Deluxe" <?php echo $typeFilter === 'Deluxe' ? 'selected' : ''; ?>>Deluxe</option>
            <option value="Suite" <?php echo $typeFilter === 'Suite' ? 'selected' : ''; ?>>Suite</option>
            <option value="Standard" <?php echo $typeFilter === 'Standard' ? 'selected' : ''; ?>>Standard</option>
            <option value="Family" <?php echo $typeFilter === 'Family' ? 'selected' : ''; ?>>Family</option>
            <option value="Presidential" <?php echo $typeFilter === 'Presidential' ? 'selected' : ''; ?>>Presidential</option>
        </select>
        <button type="submit" class="btn btn-gold">Filter Accommodations</button>
    </form>

    <div class="rooms-grid-edge">
        <?php foreach ($rooms as $r): ?>
            <?php
                $remain = (int)$r['remaining_units'];
                $total  = (int)$r['total_units'];
                $cls    = $remain <= 0 ? 'avail-none' : ($remain <= 2 ? 'avail-low' : 'avail-ok');
                $label  = $remain <= 0 ? 'Sold Out' : ($remain . ' of ' . $total . ' units available');
            ?>
            <div class="card">
                <img src="<?php echo $r['img']; ?>" class="card-img" alt="<?php echo sanitize($r['title']); ?>">
                <div class="card-body">
                    <div>
                        <span style="font-size:0.75rem; text-transform:uppercase; color:var(--gold); font-weight:700; letter-spacing:1px;">
                            <?php echo sanitize($r['type'] ?? 'Luxury Suite'); ?>
                        </span>
                        <h3 style="margin: 0.25rem 0 0.5rem; font-size:1.2rem; color:var(--navy);"><?php echo sanitize($r['title']); ?></h3>

                        <span class="avail-badge <?php echo $cls; ?>"><?php echo $label; ?></span>

                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.75rem;">
                            Capacity: <?php echo $r['capacity'] ?? 2; ?> Guests | Size: <?php echo $r['size_sqm'] ?? 35; ?> m²
                        </p>
                        <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:1rem;">
                            <?php echo sanitize(mb_strimwidth($r['description'], 0, 75, '...')); ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-weight:700; color:var(--navy); font-size:1.25rem; margin-bottom:1rem;">
                            $<?php echo number_format($r['price'], 2); ?> <span style="font-size:0.85rem; font-weight:normal; color:var(--text-muted);">/ night</span>
                        </p>
                        <a href="room-detail.php?id=<?php echo $r['id']; ?>" class="btn btn-gold" style="display:block; text-align:center;">View Details & Book</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>