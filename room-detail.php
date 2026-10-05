<?php
require_once 'includes/functions.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

$pdo = getDBConnection();
$room = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $room = $stmt->fetch();
} catch (Exception $e) {
    $room = null;
}

// Compute remaining units for this room (snapshot)
$remainingUnits = null;
$totalUnits     = null;
if ($room) {
    try {
        $stmtRem = $pdo->prepare(
            "SELECT r.total_quantity,
                    COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending','pending_payment') THEN b.quantity ELSE 0 END), 0) AS booked
             FROM rooms r
             LEFT JOIN bookings b ON b.room_id = r.id
             WHERE r.id = :id
             GROUP BY r.id"
        );
        $stmtRem->execute(['id' => $id]);
        $row = $stmtRem->fetch();
        if ($row) {
            $totalUnits     = (int)$row['total_quantity'];
            $remainingUnits = max(0, $totalUnits - (int)$row['booked']);
        }
    } catch (Exception $e) {
        $remainingUnits = null;
        $totalUnits     = null;
    }
}

// 12 Fallback Data Repository with detailed images matching index.php
$defaultCards = [
    1 => ['id' => 1, 'type' => 'Suite', 'title' => 'Deluxe Oceanfront Suite', 'description' => 'Immerse yourself in coastal grandeur with panoramic balcony views, a handcrafted king-size bed, private dining pavilion, and an artisan marble bathroom with rainfall shower.', 'price' => 245.00, 'capacity' => 2, 'beds' => '1 King Bed', 'size_sqm' => 55, 'amenities' => 'Ocean View, Free Wi-Fi, Private Balcony, Espresso Machine, Mini Bar, Room Service', 'images' => [
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80'
    ]],
    2 => ['id' => 2, 'type' => 'Standard', 'title' => 'Coastal Studio Room', 'description' => 'Cozy modern studio tailored for solo travelers or couples seeking a calm getaway with soft minimalist aesthetics and immediate garden access.', 'price' => 102.00, 'capacity' => 2, 'beds' => '1 Queen Bed', 'size_sqm' => 30, 'amenities' => 'Garden Access, High Speed Wi-Fi, Smart TV, Air Conditioning, Work Desk', 'images' => [
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1616046229478-9901c5536a45?auto=format&fit=crop&w=1200&q=80'
    ]],
    3 => ['id' => 3, 'type' => 'Deluxe', 'title' => 'Executive Bay View', 'description' => 'Spacious accommodation equipped with dedicated executive work area, expansive private terrace, and sweeping twilight views across the azure bay.', 'price' => 175.00, 'capacity' => 3, 'beds' => '1 King Bed + 1 Sofa Bed', 'size_sqm' => 45, 'amenities' => 'Bay Terrace, Executive Desk, Coffee Station, Luxury Toiletries, Safe Box', 'images' => [
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80'
    ]],
    4 => ['id' => 4, 'type' => 'Standard', 'title' => 'Sunset Palm Haven', 'description' => 'Relax with floor-to-ceiling windows overlooking tropical palm trees and warm coastal sunsets. Features custom teakwood finishings and plush bedding.', 'price' => 135.00, 'capacity' => 2, 'beds' => '1 King Bed', 'size_sqm' => 35, 'amenities' => 'Sunset View, Wi-Fi, Lounge Armchairs, Premium Audio System, Mini Fridge', 'images' => [
        'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=1200&q=80'
    ]],
    5 => ['id' => 5, 'type' => 'Presidential', 'title' => 'Seaside Villa Suite', 'description' => 'Our flagship villa experience featuring multi-room grandeur, private plunge pool, outdoor terrace lounge, and direct private beach gateway.', 'price' => 250.00, 'capacity' => 4, 'beds' => '2 King Beds', 'size_sqm' => 90, 'amenities' => 'Plunge Pool, Direct Beach Access, Personal Butler Service, Fully Stocked Bar, Jacuzzi Bath', 'images' => [
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=1200&q=80'
    ]],
    6 => ['id' => 6, 'type' => 'Standard', 'title' => 'Azure Breeze Chamber', 'description' => 'Quiet corner suite offering constant ocean breeze, oversized windows, and an airy coastal aesthetic designed for peaceful rejuvenation.', 'price' => 118.00, 'capacity' => 2, 'beds' => '1 Queen Bed', 'size_sqm' => 32, 'amenities' => 'Ocean Breeze Balcony, Wi-Fi, HD Television, Ceiling Fan, Coffee Maker', 'images' => [
        'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1616046229478-9901c5536a45?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80'
    ]],
    7 => ['id' => 7, 'type' => 'Presidential', 'title' => 'Royal Horizon Penthouse', 'description' => 'Top-floor architectural marvel featuring 360-degree shoreline panoramas, private open-air Jacuzzi, sun loungers, and personal dining service.', 'price' => 240.00, 'capacity' => 4, 'beds' => '2 Super King Beds', 'size_sqm' => 110, 'amenities' => 'Rooftop Jacuzzi, 360 Degree Views, Butler Service, Wine Cellar, VIP Lounge Access', 'images' => [
        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1200&q=80'
    ]],
    8 => ['id' => 8, 'type' => 'Standard', 'title' => 'Garden Coast Retreat', 'description' => 'Peaceful ground-floor haven situated right adjacent to our private blooming botanical garden and shaded reading patio.', 'price' => 110.00, 'capacity' => 2, 'beds' => '1 Double Bed', 'size_sqm' => 34, 'amenities' => 'Private Garden Patio, Botanical Views, Wi-Fi, Rain Shower, Tea Station', 'images' => [
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1616046229478-9901c5536a45?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80'
    ]],
    9 => ['id' => 9, 'type' => 'Family', 'title' => 'Coral Reef Family Suite', 'description' => 'Designed specifically for families or groups, offering dual queen bedding, expanded living area, interactive entertainment center, and kids beach kits.', 'price' => 198.00, 'capacity' => 5, 'beds' => '2 Queen Beds + 1 Rollaway', 'size_sqm' => 65, 'amenities' => 'Connected Family Layout, Games Console, Extra Storage, Beach Kits, Breakfast Included', 'images' => [
        'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=1200&q=80'
    ]],
    10 => ['id' => 10, 'type' => 'Deluxe', 'title' => 'Nautical Vista Balcony', 'description' => 'Elevated room featuring maritime-inspired artisan décor, custom hardwood flooring, and a glass-paneled balcony facing the harbor.', 'price' => 162.00, 'capacity' => 2, 'beds' => '1 King Bed', 'size_sqm' => 40, 'amenities' => 'Harbor Balcony View, Wi-Fi, Artisan Coffee Bar, Plush Bathrobes, Bluetooth Speaker', 'images' => [
        'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80'
    ]],
    11 => ['id' => 11, 'type' => 'Suite', 'title' => 'Serenity Ocean Cottage', 'description' => 'Charming standalone wooden cottage surrounded by gentle dunes, located directly on the pathway to white sand beaches.', 'price' => 215.00, 'capacity' => 3, 'beds' => '1 King Bed', 'size_sqm' => 50, 'amenities' => 'Private Dune Porch, Hammock, Outdoor Shower, Daily Fresh Fruit, Organic Toiletries', 'images' => [
        'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80'
    ]],
    12 => ['id' => 12, 'type' => 'Standard', 'title' => 'Minimalist Dune Studio', 'description' => 'Sleek contemporary studio emphasizing natural light, clean lines, serene pastel tones, and efficient luxury layout.', 'price' => 105.00, 'capacity' => 2, 'beds' => '1 Double Bed', 'size_sqm' => 28, 'amenities' => 'Minimalist Design, Wi-Fi, Climate Control, Work Nook, USB Charging Hubs', 'images' => [
        'https://images.unsplash.com/photo-1616046229478-9901c5536a45?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80'
    ]]
];

$selectedData = $defaultCards[$id] ?? $defaultCards[1];

// Merge with DB record if present
if ($room) {
    $selectedData['title'] = $room['title'] ?? $selectedData['title'];
    $selectedData['description'] = $room['description'] ?? $selectedData['description'];
    $selectedData['capacity'] = $room['capacity'] ?? $selectedData['capacity'];
    $selectedData['beds'] = $room['beds'] ?? $selectedData['beds'];
    $selectedData['amenities'] = $room['amenities'] ?? $selectedData['amenities'];

    $rawPrice = floatval($room['price'] ?? 0);
    if ($rawPrice >= 102 && $rawPrice <= 250) {
        $selectedData['price'] = $rawPrice;
    }
}

// Fallback availability if DB didn't give us numbers
if ($remainingUnits === null) {
    $remainingUnits = 10;
    $totalUnits     = 10;
}

include 'includes/header.php';
?>

<style>
/* Full Screen Edge-to-Edge Layout for Room Details */
.detail-fullwidth-wrapper {
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  padding: 5rem 2rem 4rem;
  background-color: var(--cream);
}

.detail-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 3rem;
  max-width: 1400px;
  margin: 0 auto;
}

.gallery-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.main-image-frame {
  width: 100%;
  height: 480px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow);
  background: var(--navy);
}

.main-image-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  cursor: pointer;
  transition: transform 0.4s ease;
}

.main-image-frame img:hover {
  transform: scale(1.02);
}

.thumbnails-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.thumb-img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  opacity: 0.65;
  transition: var(--transition);
  border: 2px solid transparent;
}

.thumb-img:hover, .thumb-img.active-thumb {
  opacity: 1;
  border-color: var(--gold);
}

.detail-info-panel {
  background: var(--white);
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: var(--shadow);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.amenity-tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin: 1.5rem 0;
}

.amenity-tag {
  background: var(--cream);
  color: var(--navy);
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  border: 1px solid #e2e8f0;
}

.avail-badge {
  display: inline-block;
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
  margin-bottom: 1rem;
}
.avail-ok   { background: #d1fae5; color: #065f46; }
.avail-low  { background: #fef3c7; color: #92400e; }
.avail-none { background: #fee2e2; color: #991b1b; }

.btn-disabled {
  background: #cbd5e1 !important;
  color: #64748b !important;
  cursor: not-allowed !important;
  pointer-events: none;
}

@media (max-width: 992px) {
  .detail-grid { grid-template-columns: 1fr; }
  .main-image-frame { height: 350px; }
}
</style>

<div class="detail-fullwidth-wrapper">
    <div class="detail-grid">
        
        <!-- Gallery Section -->
        <div class="gallery-container">
            <div class="main-image-frame">
                <img id="primaryDisplayImg" src="<?php echo $selectedData['images'][0]; ?>" class="lightbox-trigger" alt="<?php echo sanitize($selectedData['title']); ?>">
            </div>
            
            <div class="thumbnails-row">
                <?php foreach ($selectedData['images'] as $idx => $imgUrl): ?>
                    <img src="<?php echo $imgUrl; ?>" 
                         class="thumb-img <?php echo $idx === 0 ? 'active-thumb' : ''; ?>" 
                         alt="Thumbnail <?php echo $idx + 1; ?>"
                         onclick="switchMainImage(this, '<?php echo $imgUrl; ?>')">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Details & Booking Info Panel -->
        <div class="detail-info-panel">
            <div>
                <span style="text-transform:uppercase; font-size:0.85rem; color:var(--gold); font-weight:700; letter-spacing:1px;">
                    <?php echo sanitize($selectedData['type']); ?>
                </span>
                <h1 style="color:var(--navy); font-size:2.4rem; margin:0.25rem 0 0.5rem;"><?php echo sanitize($selectedData['title']); ?></h1>

                <?php
                    $rCls   = $remainingUnits <= 0 ? 'avail-none' : ($remainingUnits <= 2 ? 'avail-low' : 'avail-ok');
                    $rLabel = $remainingUnits <= 0
                        ? 'Sold Out'
                        : $remainingUnits . ' of ' . $totalUnits . ' units available';
                ?>
                <span class="avail-badge <?php echo $rCls; ?>"><?php echo $rLabel; ?></span>
                
                <p style="font-size:2rem; color:var(--gold); font-weight:700; margin-bottom:1.5rem;">
                    $<?php echo number_format($selectedData['price'], 2); ?> 
                    <span style="font-size:1rem; color:var(--text-muted); font-weight:normal;">/ night</span>
                </p>

                <p style="color:var(--text-dark); line-height:1.7; margin-bottom:1.5rem; font-size:1.05rem;">
                    <?php echo sanitize($selectedData['description']); ?>
                </p>

                <hr style="border:0; border-top:1px solid #edf2f7; margin:1.5rem 0;">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                    <div>
                        <strong style="color:var(--navy);">Max Guest Capacity:</strong>
                        <p style="color:var(--text-muted);"><?php echo $selectedData['capacity']; ?> Guests</p>
                    </div>
                    <div>
                        <strong style="color:var(--navy);">Bed Setup:</strong>
                        <p style="color:var(--text-muted);"><?php echo $selectedData['beds']; ?></p>
                    </div>
                    <div>
                        <strong style="color:var(--navy);">Room Dimensions:</strong>
                        <p style="color:var(--text-muted);"><?php echo $selectedData['size_sqm']; ?> m²</p>
                    </div>
                    <div>
                        <strong style="color:var(--navy);">View Type:</strong>
                        <p style="color:var(--text-muted);">Ocean & Sunset View</p>
                    </div>
                </div>

                <strong style="color:var(--navy);">Included Key Amenities:</strong>
                <div class="amenity-tag-list">
                    <?php 
                    $amenityArray = explode(',', $selectedData['amenities']);
                    foreach ($amenityArray as $amenity): 
                    ?>
                        <span class="amenity-tag"><?php echo sanitize(trim($amenity)); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($remainingUnits > 0): ?>
                <a href="booking.php?room_id=<?php echo $selectedData['id']; ?>" class="btn btn-gold" style="width:100%; text-align:center; padding:1rem; font-size:1.1rem; margin-top:1rem;">
                    Proceed to Online Reservation
                </a>
            <?php else: ?>
                <button class="btn btn-gold btn-disabled" style="width:100%; text-align:center; padding:1rem; font-size:1.1rem; margin-top:1rem;" disabled>
                    Currently Sold Out
                </button>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function switchMainImage(thumbElement, newSrc) {
    const mainImg = document.getElementById('primaryDisplayImg');
    mainImg.src = newSrc;

    // Update active state on thumbnails
    document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active-thumb'));
    thumbElement.classList.add('active-thumb');
}
</script>

<?php include 'includes/footer.php'; ?>