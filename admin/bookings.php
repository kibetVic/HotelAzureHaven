<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Admin Direct Booking (No Payment Required)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_book') {
    $guestName  = sanitize($_POST['guest_name']);
    $guestEmail = sanitize($_POST['guest_email']);
    $roomId     = (int)$_POST['room_id'];
    $checkIn    = sanitize($_POST['check_in']);
    $checkOut   = sanitize($_POST['check_out']);
    $status     = sanitize($_POST['status'] ?? 'confirmed');
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

    // Fetch room price + total quantity for availability check
    $stmtRoom = $pdo->prepare("SELECT price, total_quantity FROM rooms WHERE id = :id");
    $stmtRoom->execute(['id' => $roomId]);
    $room = $stmtRoom->fetch();

    if ($room) {
        // Check availability for the requested date range
        $stmtAvail = $pdo->prepare(
            "SELECT COALESCE(SUM(quantity), 0) FROM bookings
             WHERE room_id = :room_id
               AND status IN ('confirmed','pending')
               AND check_in < :check_out
               AND check_out > :check_in"
        );
        $stmtAvail->execute([
            'room_id'   => $roomId,
            'check_in'  => $checkIn,
            'check_out' => $checkOut
        ]);
        $alreadyBooked = (int)$stmtAvail->fetchColumn();
        $remaining = (int)$room['total_quantity'] - $alreadyBooked;

        if ($quantity > $remaining) {
            $error = "Only $remaining unit(s) available for the selected dates.";
        } else {
            $days = max(1, (strtotime($checkOut) - strtotime($checkIn)) / 86400);
            $totalPrice = $room['price'] * $days * $quantity;
            $userId = $_SESSION['user_id'] ?? null;

            try {
                $stmt = $pdo->prepare("INSERT INTO bookings (user_id, guest_name, guest_email, room_id, quantity, check_in, check_out, total_price, status, created_at) 
                                       VALUES (:user_id, :guest_name, :guest_email, :room_id, :quantity, :check_in, :check_out, :total_price, :status, NOW())");
                $stmt->execute([
                    'user_id'     => $userId,
                    'guest_name'  => $guestName,
                    'guest_email' => $guestEmail,
                    'room_id'     => $roomId,
                    'quantity'    => $quantity,
                    'check_in'    => $checkIn,
                    'check_out'   => $checkOut,
                    'total_price' => $totalPrice,
                    'status'      => $status
                ]);
                $msg = "Room successfully booked for $guestName!";
            } catch (PDOException $e) {
                $error = "Failed to book room: " . $e->getMessage();
            }
        }
    } else {
        $error = "Selected room not found.";
    }
}

// Update Booking Status Handler
if (isset($_GET['action']) && isset($_GET['id'])) {
    $bookingId = (int)$_GET['id'];
    $action = sanitize($_GET['action']);

    if (in_array($action, ['confirmed', 'cancelled', 'pending'])) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $action, 'id' => $bookingId]);
    }
    header("Location: bookings.php");
    exit();
}

// Fetch active rooms for dropdown selection (grouped model)
$availableRooms = $pdo->query("SELECT id, room_group, title, price, total_quantity FROM rooms WHERE status = 'available'")->fetchAll();

// Fetch all bookings with room_group
$bookings = $pdo->query("SELECT b.*, 
                                u.name  AS user_name, 
                                u.email AS user_email, 
                                r.title AS room_title, 
                                r.room_group,
                                r.total_quantity
                         FROM bookings b 
                         LEFT JOIN users u ON b.user_id = u.id 
                         LEFT JOIN rooms r ON b.room_id = r.id 
                         ORDER BY b.created_at DESC")->fetchAll();

// Compute remaining units per room group for the *current* table display (based on total bookings)
// We show static remaining = total_quantity - sum(active bookings), which is a snapshot.
$remainingMap = [];
try {
    $rows = $pdo->query(
        "SELECT r.id, r.total_quantity,
                COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending') THEN b.quantity ELSE 0 END), 0) AS booked
         FROM rooms r
         LEFT JOIN bookings b ON b.room_id = r.id
         GROUP BY r.id"
    )->fetchAll();
    foreach ($rows as $row) {
        $remainingMap[(int)$row['id']] = max(0, (int)$row['total_quantity'] - (int)$row['booked']);
    }
} catch (Exception $e) {
    $remainingMap = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings | Azure Haven Admin</title>
    <link rel="icon" type="image/png" href="assets/images/logos.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/logos.png">
    <link rel="apple-touch-icon" href="assets/images/logos.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; }
        th, td { padding: 0.75rem; border: 1px solid #e2e8f0; text-align: left; }
        th { background: var(--navy); color: #fff; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.85rem; }
        .badge-confirmed { background: #c6f6d5; color: #22543d; }
        .badge-pending { background: #feebc8; color: #744210; }
        .badge-cancelled { background: #fed7d7; color: #742a2a; }
        .booking-card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); margin-bottom: 2rem; border: 1px solid #e2e8f0; }
        .booking-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .units-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 700; }
        .units-ok   { background: #d1fae5; color: #065f46; }
        .units-low  { background: #fef3c7; color: #92400e; }
        .units-none { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body style="padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Reservations & Bookings Log</h1>
        <div>
            <a href="index.php" class="btn btn-outline">Dashboard</a>
            <a href="rooms.php" class="btn btn-outline">Manage Rooms</a>
            <a href="logout.php" class="btn btn-gold">Logout</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <p style="padding: 1rem; background: #c6f6d5; color: #22543d; border-radius: 4px; margin-bottom: 1.5rem;"><?php echo $msg; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="padding: 1rem; background: #fed7d7; color: #742a2a; border-radius: 4px; margin-bottom: 1.5rem;"><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- Direct Admin Booking Section -->
    <div class="booking-card">
        <h3>Create Direct Room Booking (No Payment Required)</h3>
        <form method="POST" action="bookings.php" class="booking-form">
            <input type="hidden" name="action" value="admin_book">
            
            <input type="text" name="guest_name" placeholder="Guest Full Name" class="form-control" required>
            <input type="email" name="guest_email" placeholder="Guest Email" class="form-control" required>
            
            <select name="room_id" class="form-control" required>
                <option value="">-- Select Room Group --</option>
                <?php foreach ($availableRooms as $rm): ?>
                    <option value="<?php echo $rm['id']; ?>">
                        <?php echo sanitize($rm['room_group']); ?> - <?php echo sanitize($rm['title']); ?> 
                        ($<?php echo number_format($rm['price'], 2); ?>/night | <?php echo (int)$rm['total_quantity']; ?> units total)
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="quantity" class="form-control" min="1" value="1" placeholder="Units" required>

            <input type="date" name="check_in" class="form-control" required>
            <input type="date" name="check_out" class="form-control" required>

            <select name="status" class="form-control">
                <option value="confirmed">Status: Confirmed</option>
                <option value="pending">Status: Pending</option>
            </select>

            <button type="submit" class="btn btn-gold" style="grid-column: 1 / -1; justify-self: start;">Book Room Now</button>
        </form>
    </div>

    <h2>All Reservations</h2>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Guest Details</th>
                <th>Room Group</th>
                <th>Units</th>
                <th>Remaining / Total</th>
                <th>Dates</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <?php
                    $totalQty = (int)($b['total_quantity'] ?? 0);
                    $remain   = $remainingMap[(int)$b['room_id']] ?? $totalQty;
                    $cls      = $remain <= 0 ? 'units-none' : ($remain <= 2 ? 'units-low' : 'units-ok');
                ?>
                <tr>
                    <td>#<?php echo $b['id']; ?></td>
                    <td>
                        <strong><?php echo sanitize($b['user_name'] ?? $b['guest_name'] ?? 'Guest User'); ?></strong><br>
                        <small><?php echo sanitize($b['user_email'] ?? $b['guest_email'] ?? 'N/A'); ?></small>
                    </td>
                    <td>
                        <?php echo sanitize($b['room_group'] ?? 'N/A'); ?> - <?php echo sanitize($b['room_title'] ?? 'Unassigned'); ?>
                    </td>
                    <td><strong><?php echo (int)($b['quantity'] ?? 1); ?></strong></td>
                    <td>
                        <span class="units-badge <?php echo $cls; ?>">
                            <?php echo $remain; ?> / <?php echo $totalQty; ?>
                        </span>
                    </td>
                    <td>
                        <?php echo $b['check_in']; ?> to <?php echo $b['check_out']; ?>
                    </td>
                    <td>$<?php echo number_format($b['total_price'], 2); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $b['status']; ?>">
                            <?php echo strtoupper($b['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($b['status'] !== 'confirmed'): ?>
                            <a href="bookings.php?action=confirmed&id=<?php echo $b['id']; ?>" style="color: green; margin-right: 0.5rem;">Confirm</a>
                        <?php endif; ?>
                        <?php if ($b['status'] !== 'cancelled'): ?>
                            <a href="bookings.php?action=cancelled&id=<?php echo $b['id']; ?>" style="color: red;">Cancel</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>