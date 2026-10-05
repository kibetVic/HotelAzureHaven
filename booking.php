<?php
require_once 'includes/functions.php';
requireLogin();

$pdo = getDBConnection();

/* ------------------------------------------------------------------
 * AJAX endpoint: GET booking.php?ajax_avail=1&room_id=..&check_in=..&check_out=..
 * Returns JSON { remaining: N }
 * ------------------------------------------------------------------ */
if (isset($_GET['ajax_avail'])) {
    header('Content-Type: application/json');
    $ajaxRoomId = (int)($_GET['room_id'] ?? 0);
    $ajaxIn     = $_GET['check_in']  ?? '';
    $ajaxOut    = $_GET['check_out'] ?? '';

    if (!$ajaxRoomId || !$ajaxIn || !$ajaxOut || strtotime($ajaxIn) >= strtotime($ajaxOut)) {
        echo json_encode(['remaining' => 0]);
        exit();
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT r.total_quantity,
                    COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending','pending_payment')
                                      AND b.check_in < :co AND b.check_out > :ci
                                 THEN b.quantity ELSE 0 END), 0) AS booked
             FROM rooms r
             LEFT JOIN bookings b ON b.room_id = r.id
             WHERE r.id = :id
             GROUP BY r.id"
        );
        $stmt->execute(['id' => $ajaxRoomId, 'ci' => $ajaxIn, 'co' => $ajaxOut]);
        $row = $stmt->fetch();
        $remaining = $row ? max(0, (int)$row['total_quantity'] - (int)$row['booked']) : 0;
        echo json_encode(['remaining' => $remaining]);
    } catch (Exception $e) {
        echo json_encode(['remaining' => 0]);
    }
    exit();
}

$error = '';
$success = '';

$roomId = $_GET['room_id'] ?? $_POST['room_id'] ?? null;

// Default fallback to "Executive Bay View" if no room_id is provided
if (!$roomId) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE title LIKE :title LIMIT 1");
    $stmt->execute(['title' => '%Executive Bay View%']);
    $room = $stmt->fetch();
    
    if ($room) {
        $roomId = $room['id'];
    } else {
        $stmt = $pdo->query("SELECT * FROM rooms LIMIT 1");
        $room = $stmt->fetch();
        $roomId = $room['id'] ?? null;
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->execute(['id' => $roomId]);
    $room = $stmt->fetch();
}

if (!$room) {
    header("Location: rooms.php");
    exit();
}

$roomCapacity = (int)($room['capacity'] ?? 2);

// Compute *current* max available units (snapshot — no date filter yet)
$maxUnits = 10;
try {
    $stmtMax = $pdo->prepare(
        "SELECT r.total_quantity,
                COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending','pending_payment')
                             THEN b.quantity ELSE 0 END), 0) AS booked
         FROM rooms r
         LEFT JOIN bookings b ON b.room_id = r.id
         WHERE r.id = :id
         GROUP BY r.id"
    );
    $stmtMax->execute(['id' => $roomId]);
    $rowMax = $stmtMax->fetch();
    if ($rowMax) {
        $maxUnits = max(0, (int)$rowMax['total_quantity'] - (int)$rowMax['booked']);
    }
} catch (Exception $e) {
    $maxUnits = 10;
}
if ($maxUnits < 1) $maxUnits = 1; // still allow UI; server will reject if truly 0

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF verification failed.";
    } else {
        $checkIn = sanitize($_POST['check_in']);
        $checkOut = sanitize($_POST['check_out']);
        $guests = (int)$_POST['guests'];
        $numRooms = (int)($_POST['num_rooms'] ?? 1);
        $phone = sanitize($_POST['phone']);

        // Format phone number to 2547XXXXXXXX
        $phone = preg_replace('/^0/', '254', preg_replace('/[^0-9]/', '', $phone));
        if (strlen($phone) === 9) {
            $phone = '254' . $phone;
        }

        // Live availability for the requested dates
        $availableForDates = 0;
        try {
            $stmtAvail = $pdo->prepare(
                "SELECT r.total_quantity,
                        COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending','pending_payment')
                                          AND b.check_in < :co AND b.check_out > :ci
                                     THEN b.quantity ELSE 0 END), 0) AS booked
                 FROM rooms r
                 LEFT JOIN bookings b ON b.room_id = r.id
                 WHERE r.id = :id
                 GROUP BY r.id"
            );
            $stmtAvail->execute(['id' => $roomId, 'ci' => $checkIn, 'co' => $checkOut]);
            $rowAvail = $stmtAvail->fetch();
            if ($rowAvail) {
                $availableForDates = max(0, (int)$rowAvail['total_quantity'] - (int)$rowAvail['booked']);
            }
        } catch (Exception $e) {
            $availableForDates = 0;
        }

        if (strlen($phone) !== 12 || !str_starts_with($phone, '254')) {
            $error = "Please enter a valid M-Pesa phone number (e.g., 0712345678 or 254712345678).";
        } else if (strtotime($checkIn) >= strtotime($checkOut)) {
            $error = "Check-out date must be after check-in date.";
        } else if ($guests > $roomCapacity) {
            $error = "Number of guests cannot exceed room capacity of " . $roomCapacity . " guests per room.";
        } else if ($numRooms < 1) {
            $error = "Please select at least 1 room.";
        } else if ($numRooms > $availableForDates) {
            $error = "Only $availableForDates room(s) available for the selected dates. Please reduce the number of rooms.";
        } else if (!checkRoomAvailability($roomId, $checkIn, $checkOut)) {
            $error = "Sorry, room is unavailable for selected dates.";
        } else {
            $pricePerNight = (float)$room['price'];
            $nights = (strtotime($checkOut) - strtotime($checkIn)) / 86400;
            $totalPrice = $nights * $pricePerNight * $numRooms;

            // Initiate M-Pesa STK Push
            $mpesaResponse = triggerMpesaSTKPush($phone, $totalPrice, "Booking - " . $numRooms . "x " . $room['title']);

            if (isset($mpesaResponse['ResponseCode']) && $mpesaResponse['ResponseCode'] == '0') {
                $insert = $pdo->prepare(
                    "INSERT INTO bookings (user_id, room_id, quantity, check_in, check_out, guests, total_price, status) 
                     VALUES (:u, :r, :q, :ci, :co, :g, :tp, 'pending_payment')"
                );

                // Save bookings for each room requested
                for ($i = 0; $i < $numRooms; $i++) {
                    $insert->execute([
                        'u'  => $_SESSION['user_id'],
                        'r'  => $roomId,
                        'q'  => 1, // one unit per row, per-night stretch captured by dates
                        'ci' => $checkIn,
                        'co' => $checkOut,
                        'g'  => $guests,
                        'tp' => ($nights * $pricePerNight)
                    ]);
                }

                $success = "STK Push sent to <strong>" . htmlspecialchars($phone) . "</strong> for " . $numRooms . " room(s). Complete payment on your phone to finalize booking.";
            } else {
                $error = $mpesaResponse['errorMessage'] ?? "Failed to initiate M-Pesa payment. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<!-- Centered Outer Wrapper -->
<div style="display: flex; justify-content: center; align-items: center; min-height: 85vh; padding: 4rem 1rem;">
    <div style="max-width: 550px; width: 100%; background: #ffffff; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow, 0 10px 25px rgba(0,0,0,0.1)); border: 1px solid #e2e8f0;">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <span style="color: var(--gold, #c5a059); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1.5px; font-weight: 700;">
                <?php echo sanitize($room['type'] ?? 'Luxury Suite'); ?>
            </span>
            <h2 style="color: var(--navy, #0a192f); margin-top: 0.25rem; font-size: 1.8rem;">
                Reserve <?php echo sanitize($room['title']); ?>
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; margin-top: 0.5rem;">
                Rate: <strong style="color: var(--gold, #c5a059); font-size: 1.1rem;">$<?php echo number_format($room['price'], 2); ?></strong> / night | 
                Max Capacity: <strong><?php echo $roomCapacity; ?> Guests</strong>
            </p>

            <!-- Live availability badge -->
            <p style="margin-top: 0.75rem;">
                <span id="availBadge" style="display:inline-block; padding:0.35rem 0.85rem; border-radius:20px; font-size:0.85rem; font-weight:700; background:#d1fae5; color:#065f46;">
                    <?php echo $maxUnits; ?> units currently available
                </span>
            </p>
        </div>

        <?php if ($error): ?>
            <div style="color: #b91c1c; margin-bottom: 1.5rem; padding: 0.85rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; font-size: 0.95rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="color: #15803d; margin-bottom: 1.5rem; padding: 0.85rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 0.95rem; text-align: center;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="booking.php?room_id=<?php echo urlencode($roomId); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="room_id" value="<?php echo sanitize($roomId); ?>">

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.4rem; color: var(--navy, #0a192f); font-weight: 600;">Check-in Date</label>
                <input type="date" id="check_in" name="check_in" class="form-control" min="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; margin-bottom: 0.4rem; color: var(--navy, #0a192f); font-weight: 600;">Check-out Date</label>
                <input type="date" id="check_out" name="check_out" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.4rem; color: var(--navy, #0a192f); font-weight: 600;">Number of Rooms</label>
                    <input type="number" id="num_rooms" name="num_rooms" class="form-control" value="1" min="1" max="<?php echo $maxUnits; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <small style="display:block; color:#64748b; font-size:0.8rem; margin-top:0.25rem;">
                        Max available: <span id="maxUnitsHint"><?php echo $maxUnits; ?></span>
                    </small>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.4rem; color: var(--navy, #0a192f); font-weight: 600;">Guests (Max <?php echo $roomCapacity; ?>)</label>
                    <input type="number" id="guests" name="guests" class="form-control" value="1" min="1" max="<?php echo $roomCapacity; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.4rem; color: var(--navy, #0a192f); font-weight: 600;">M-Pesa Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="07XXXXXXXX or 2547XXXXXXXX" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <!-- Real-time Cost Calculation Box -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #64748b; margin-bottom: 0.5rem;">
                    <span>Duration:</span>
                    <strong id="nights_count" style="color: var(--navy, #0a192f);">0 Nights</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #64748b; margin-bottom: 0.5rem;">
                    <span>Rooms Selected:</span>
                    <strong id="rooms_summary" style="color: var(--navy, #0a192f);">1 Room</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #64748b; margin-bottom: 0.5rem;">
                    <span>Units Available for Dates:</span>
                    <strong id="units_avail" style="color: var(--navy, #0a192f);"><?php echo $maxUnits; ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 700; color: var(--navy, #0a192f); border-top: 1px solid #cbd5e1; padding-top: 0.75rem;">
                    <span>Total Amount:</span>
                    <span style="color: var(--gold, #c5a059);">$<span id="total_price">0.00</span></span>
                </div>
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; padding: 0.85rem; font-size: 1.05rem; font-weight: 600;">
                Pay with M-Pesa & Book
            </button>
        </form>
    </div>
</div>

<!-- Live Calculation + Availability Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkInInput   = document.getElementById('check_in');
    const checkOutInput  = document.getElementById('check_out');
    const numRoomsInput  = document.getElementById('num_rooms');
    const guestsInput    = document.getElementById('guests');
    const nightsCount    = document.getElementById('nights_count');
    const roomsSummary   = document.getElementById('rooms_summary');
    const totalPrice     = document.getElementById('total_price');
    const maxUnitsHint   = document.getElementById('maxUnitsHint');
    const unitsAvail     = document.getElementById('units_avail');
    const availBadge     = document.getElementById('availBadge');

    const pricePerNight = <?php echo floatval($room['price']); ?>;
    const maxCapacity   = <?php echo $roomCapacity; ?>;
    const roomId        = <?php echo (int)$roomId; ?>;
    let   liveRemaining = <?php echo (int)$maxUnits; ?>;

    function setBadge(remaining) {
        availBadge.textContent = remaining <= 0
            ? 'Sold Out for Selected Dates'
            : remaining + ' unit(s) available for selected dates';
        availBadge.style.background = remaining <= 0 ? '#fee2e2' : (remaining <= 2 ? '#fef3c7' : '#d1fae5');
        availBadge.style.color      = remaining <= 0 ? '#991b1b' : (remaining <= 2 ? '#92400e' : '#065f46');
    }

    function calculateTotal() {
        const checkInDate  = new Date(checkInInput.value);
        const checkOutDate = new Date(checkOutInput.value);
        let rooms          = parseInt(numRoomsInput.value) || 1;

        // Enforce max guests limit in JS
        if (parseInt(guestsInput.value) > maxCapacity) {
            guestsInput.value = maxCapacity;
            alert('Maximum guests allowed for this room type is ' + maxCapacity);
        }

        // Clamp rooms to live remaining
        if (rooms > liveRemaining) {
            rooms = Math.max(1, liveRemaining);
            numRoomsInput.value = rooms;
            alert('Only ' + liveRemaining + ' room(s) available for the selected dates.');
        }
        numRoomsInput.max = Math.max(1, liveRemaining);
        maxUnitsHint.textContent = Math.max(0, liveRemaining);

        roomsSummary.textContent = rooms + (rooms === 1 ? ' Room' : ' Rooms');

        if (checkInInput.value && checkOutInput.value && checkOutDate > checkInDate) {
            const diffTime = Math.abs(checkOutDate - checkInDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            nightsCount.textContent = diffDays + (diffDays === 1 ? ' Night' : ' Nights');
            totalPrice.textContent  = (diffDays * pricePerNight * rooms).toFixed(2);
        } else {
            nightsCount.textContent = '0 Nights';
            totalPrice.textContent  = '0.00';
        }
    }

    // Query live availability whenever dates change
    function refreshAvailability() {
        if (!checkInInput.value || !checkOutInput.value) return;
        const ci = checkInInput.value;
        const co = checkOutInput.value;
        if (new Date(co) <= new Date(ci)) return;

        fetch('booking.php?ajax_avail=1&room_id=' + roomId + '&check_in=' + encodeURIComponent(ci) + '&check_out=' + encodeURIComponent(co))
            .then(r => r.json())
            .then(data => {
                if (typeof data.remaining !== 'undefined') {
                    liveRemaining = data.remaining;
                    unitsAvail.textContent = liveRemaining;
                    setBadge(liveRemaining);
                    if (parseInt(numRoomsInput.value) > liveRemaining) {
                        numRoomsInput.value = Math.max(1, liveRemaining);
                    }
                    numRoomsInput.max = Math.max(1, liveRemaining);
                    maxUnitsHint.textContent = liveRemaining;
                    calculateTotal();
                }
            })
            .catch(() => { /* silent */ });
    }

    checkInInput.addEventListener('change', () => { refreshAvailability(); calculateTotal(); });
    checkOutInput.addEventListener('change', () => { refreshAvailability(); calculateTotal(); });
    numRoomsInput.addEventListener('input', calculateTotal);
    guestsInput.addEventListener('input', calculateTotal);
});
</script>

<?php include 'includes/footer.php'; ?>