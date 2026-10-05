<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// ------------------------------------------------------------------
// Trigger manual auto-release
// ------------------------------------------------------------------
$cronMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_auto_release'])) {
    $CRON_CAPTURE = true;
    $cronMsg = include __DIR__ . '/cron_release_expired.php';
    $CRON_CAPTURE = false;
}

// ------------------------------------------------------------------
// Handle Admin Direct Booking (No Payment Required)
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_book') {
    $guestName  = sanitize($_POST['guest_name']);
    $guestEmail = sanitize($_POST['guest_email']);
    $roomId     = (int)$_POST['room_id'];
    $checkIn    = sanitize($_POST['check_in']);
    $checkOut   = sanitize($_POST['check_out']);
    $status     = sanitize($_POST['status'] ?? 'confirmed');
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

    $stmtRoom = $pdo->prepare("SELECT price, total_quantity FROM rooms WHERE id = :id");
    $stmtRoom->execute(['id' => $roomId]);
    $room = $stmtRoom->fetch();

    if ($room) {
        $stmtAvail = $pdo->prepare(
            "SELECT COALESCE(SUM(quantity), 0) FROM bookings
             WHERE room_id = :room_id
               AND status IN ('confirmed','pending','pending_payment')
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
                $msg = "Room successfully booked for $guestName (No Payment Required)!";
            } catch (PDOException $e) {
                $error = "Failed to book room: " . $e->getMessage();
            }
        }
    } else {
        $error = "Selected room not found.";
    }
}

// ------------------------------------------------------------------
// Safe query helper
// ------------------------------------------------------------------
function safeCountQuery($pdo, $sql) {
    try {
        $result = $pdo->query($sql);
        return $result ? $result->fetchColumn() : 0;
    } catch (Exception $e) {
        return 0;
    }
}

// ------------------------------------------------------------------
// Dashboard metrics
// ------------------------------------------------------------------
$totalBookings = safeCountQuery($pdo, "SELECT COUNT(*) FROM bookings");
$totalRooms    = safeCountQuery($pdo, "SELECT COALESCE(SUM(total_quantity),0) FROM rooms WHERE status = 'available'");
$totalMeals    = safeCountQuery($pdo, "SELECT COUNT(*) FROM meals");
$totalRevenue  = safeCountQuery($pdo, "SELECT SUM(total_price) FROM bookings WHERE status IN ('confirmed','completed')") ?: 0;

// ------------------------------------------------------------------
// Active rooms for dropdown
// ------------------------------------------------------------------
$availableRooms = [];
try {
    $availableRooms = $pdo->query("SELECT id, room_group, title, price, total_quantity FROM rooms WHERE status = 'available'")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $availableRooms = [];
}

// ------------------------------------------------------------------
// Recent bookings
// ------------------------------------------------------------------
$bookings = [];
try {
    $stmt = $pdo->query("SELECT b.*, 
                                COALESCE(u.name, b.guest_name, 'Guest / Admin Booking') as user_name, 
                                COALESCE(u.email, b.guest_email, 'N/A') as user_email, 
                                COALESCE(r.title, 'Unassigned Room') as room_title,
                                COALESCE(r.room_group, 'N/A') as room_group,
                                COALESCE(r.total_quantity, 0) as total_quantity
                         FROM bookings b 
                         LEFT JOIN users u ON b.user_id = u.id 
                         LEFT JOIN rooms r ON b.room_id = r.id 
                         ORDER BY b.created_at DESC LIMIT 10");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bookings = [];
}

// ------------------------------------------------------------------
// Remaining units per room — snapshot (only future-dated active bookings count)
// ------------------------------------------------------------------
$remainingMap = [];
try {
    $rows = $pdo->query(
        "SELECT r.id, r.total_quantity,
                COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending','pending_payment')
                                   AND b.check_out >= CURDATE()
                             THEN b.quantity ELSE 0 END), 0) AS booked
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Executive Dashboard | Azure Haven</title>
    <link rel="icon" type="image/png" href="assets/images/logos.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/logos.png">
    <link rel="apple-touch-icon" href="assets/images/logos.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --navy-dark: #0a192f;
            --gold-accent: #c5a059;
            --bg-light: #f8fafc;
        }
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            background-color: var(--bg-light);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #334155;
        }
        .dashboard-wrapper { padding: 2rem; max-width: 100%; box-sizing: border-box; }
        .admin-header {
            background: #ffffff;
            padding: 1.25rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid var(--gold-accent);
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .admin-header h1 { margin: 0; font-size: 1.5rem; color: var(--navy-dark); }
        .nav-links { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }

        /* Run auto-release button in header */
        .btn-auto-release {
            background: var(--navy-dark);
            color: #fff !important;
            border: none;
            padding: 0.55rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .btn-auto-release:hover { background: #1e3a5f; transform: translateY(-1px); }

        /* Cron output console panel */
        .cron-panel {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .cron-panel-header {
            background: #0f172a;
            color: #f1f5f9;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cron-panel-header button {
            background: transparent;
            border: 1px solid #475569;
            color: #cbd5e1;
            border-radius: 4px;
            padding: 0.15rem 0.5rem;
            font-size: 0.7rem;
            cursor: pointer;
        }
        .cron-panel pre {
            margin: 0;
            padding: 1rem 1.25rem;
            background: #0f172a;
            color: #e2e8f0;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.78rem;
            line-height: 1.5;
            max-height: 380px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }
        .metric-card .title {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .metric-card .value { font-size: 1.8rem; font-weight: 700; color: var(--navy-dark); }
        .booking-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .booking-card h2 { margin-top: 0; color: var(--navy-dark); font-size: 1.2rem; margin-bottom: 1rem; }
        .booking-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .table-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h2 { margin: 0; font-size: 1.2rem; color: var(--navy-dark); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 1rem 1.5rem;
        }
        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-confirmed  { background: #dcfce7; color: #15803d; }
        .badge-pending    { background: #fef3c7; color: #b45309; }
        .badge-cancelled  { background: #fee2e2; color: #b91c1c; }
        .badge-completed  { background: #e0e7ff; color: #3730a3; }
        .badge-pending_payment { background: #fef3c7; color: #b45309; }

        .units-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
        .units-ok   { background: #d1fae5; color: #065f46; }
        .units-low  { background: #fef3c7; color: #92400e; }
        .units-none { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>Azure Haven Admin Control Panel</h1>
        <div class="nav-links">
            <!-- Auto-Release Button (properly placed, styled) -->
            <form method="POST" style="margin:0;">
                <button type="submit" name="run_auto_release" class="btn-auto-release" title="Release expired bookings now">
                    ⚡ Run Auto-Release
                </button>
            </form> 

            <a href="rooms.php" class="btn btn-outline">Manage Rooms</a>
            <a href="bookings.php" class="btn btn-outline">Reservations</a>
            <a href="manage-meals.php" class="btn btn-outline">Meals & Dining</a>
            <a href="../index.php" class="btn btn-outline" target="_blank">View Website</a>
            <a href="../logout.php" class="btn btn-gold">Logout</a>
        </div>
    </header>

<div class="dashboard-wrapper">

    <!-- Cron Output Console Panel (shown only after running auto-release) -->
    <?php if (!empty($cronMsg)): ?>
        <div class="cron-panel">
            <div class="cron-panel-header">
                <span>⚙ Auto-Release Output</span>
                <button type="button" onclick="this.closest('.cron-panel').style.display='none';">✕ Close</button>
            </div>
            <pre><?php echo htmlspecialchars($cronMsg); ?></pre>
        </div>
    <?php endif; ?>

    <?php if ($msg): ?>
        <p style="padding: 1rem; background: #dcfce7; color: #15803d; border-radius: 6px; margin-bottom: 1.5rem;"><?php echo $msg; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="padding: 1rem; background: #fee2e2; color: #b91c1c; border-radius: 6px; margin-bottom: 1.5rem;"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="title">Total Revenue</div>
            <div class="value">$<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="metric-card">
            <div class="title">Total Reservations</div>
            <div class="value"><?php echo number_format($totalBookings); ?></div>
        </div>
        <div class="metric-card">
            <div class="title">Active Room Units</div>
            <div class="value"><?php echo number_format($totalRooms); ?></div>
        </div>
        <div class="metric-card">
            <div class="title">Food & Beverage Items</div>
            <div class="value"><?php echo number_format($totalMeals); ?></div>
        </div>
    </div>

    <!-- Admin Manual Booking Section (No Payment Required) -->
    <div class="booking-card">
        <h2>Direct Admin Room Booking (No Payment Required)</h2>
        <form method="POST" action="index.php" class="booking-form">
            <input type="hidden" name="action" value="admin_book">
            
            <input type="text" name="guest_name" placeholder="Guest Full Name" class="form-control" required>
            <input type="email" name="guest_email" placeholder="Guest Email" class="form-control" required>
            
            <select name="room_id" class="form-control" required>
                <option value="">-- Select Available Room Group --</option>
                <?php foreach ($availableRooms as $rm): ?>
                    <option value="<?php echo $rm['id']; ?>">
                        <?php echo sanitize($rm['room_group']); ?> - <?php echo sanitize($rm['title']); ?> 
                        ($<?php echo number_format($rm['price'], 2); ?>/night | <?php echo (int)$rm['total_quantity']; ?> units)
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="quantity" class="form-control" min="1" value="1" placeholder="Units" required>

            <input type="date" name="check_in" class="form-control" required>
            <input type="date" name="check_out" class="form-control" required>

            <select name="status" class="form-control">
                <option value="confirmed">Mark as Confirmed</option>
                <option value="pending">Mark as Pending</option>
            </select>

            <button type="submit" class="btn btn-gold" style="grid-column: 1 / -1; justify-self: start;">Book Room Now</button>
        </form>
    </div>

    <!-- Recent Booking Activity -->
    <div class="table-card">
        <div class="table-header">
            <h2>Recent Hotel Reservations</h2>
            <a href="bookings.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">View All Bookings</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Guest Details</th>
                    <th>Room Assigned</th>
                    <th>Units</th>
                    <th>Remaining / Total</th>
                    <th>Stay Dates</th>
                    <th>Total Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2.5rem;">
                            No reservations recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <?php
                            $totalQty = (int)($b['total_quantity'] ?? 0);
                            $remain   = $remainingMap[(int)$b['room_id']] ?? $totalQty;
                            $cls      = $remain <= 0 ? 'units-none' : ($remain <= 2 ? 'units-low' : 'units-ok');
                            $statusKey = strtolower($b['status']);
                        ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($b['id']); ?></strong></td>
                            <td>
                                <strong><?php echo sanitize($b['user_name']); ?></strong><br>
                                <small style="color: #64748b;"><?php echo sanitize($b['user_email']); ?></small>
                            </td>
                            <td>
                                <?php echo sanitize($b['room_title']); ?> 
                                <small style="color: #64748b;">(<?php echo sanitize($b['room_group']); ?>)</small>
                            </td>
                            <td><strong><?php echo (int)($b['quantity'] ?? 1); ?></strong></td>
                            <td>
                                <span class="units-badge <?php echo $cls; ?>">
                                    <?php echo $remain; ?> / <?php echo $totalQty; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($b['check_in']); ?> &rarr; <?php echo htmlspecialchars($b['check_out']); ?>
                            </td>
                            <td><strong>$<?php echo number_format($b['total_price'], 2); ?></strong></td>
                            <td>
                                <span class="badge badge-<?php echo $statusKey; ?>">
                                    <?php echo htmlspecialchars($b['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>