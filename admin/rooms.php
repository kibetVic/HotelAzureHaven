<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo = getDBConnection();
$error = '';
$success = '';

// Handle Adding a New Room Group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_room') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF verification failed.";
    } else {
        $roomGroup     = sanitize($_POST['room_group']);
        $type          = sanitize($_POST['type']);
        $title         = sanitize($_POST['title']);
        $description   = sanitize($_POST['description']);
        $price         = (float)$_POST['price'];
        $capacity      = (int)$_POST['capacity'];
        $beds          = (int)$_POST['beds'];
        $sizeSqm       = (int)$_POST['size_sqm'];
        $totalQuantity = max(1, (int)$_POST['total_quantity']);
        $amenities     = sanitize($_POST['amenities']);
        $image         = sanitize($_POST['image'] ?? '');

        try {
            $stmt = $pdo->prepare("INSERT INTO rooms (room_group, type, title, description, price, capacity, beds, size_sqm, total_quantity, image, amenities, status) 
                                   VALUES (:rg, :type, :title, :desc, :price, :cap, :beds, :sqm, :qty, :img, :amen, 'available')");
            $stmt->execute([
                'rg'    => $roomGroup,
                'type'  => $type,
                'title' => $title,
                'desc'  => $description,
                'price' => $price,
                'cap'   => $capacity,
                'beds'  => $beds,
                'sqm'   => $sizeSqm,
                'qty'   => $totalQuantity,
                'img'   => $image,
                'amen'  => $amenities
            ]);
            $success = "Room group successfully added.";
        } catch (PDOException $e) {
            $error = "Failed to add room: Room group name must be unique.";
        }
    }
}

// Handle Room Status Updates (Available / Maintenance)
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $roomId = (int)$_GET['id'];
    $currentStatus = sanitize($_GET['toggle_status']);
    $newStatus = ($currentStatus === 'available') ? 'maintenance' : 'available';

    $stmt = $pdo->prepare("UPDATE rooms SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $newStatus, 'id' => $roomId]);
    header("Location: rooms.php");
    exit();
}

// Fetch rooms + computed booked/remaining
$rooms = $pdo->query(
    "SELECT r.*,
            COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending') THEN b.quantity ELSE 0 END), 0) AS booked_units
     FROM rooms r
     LEFT JOIN bookings b ON b.room_id = r.id
     GROUP BY r.id
     ORDER BY r.room_group ASC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms | Azure Haven Admin</title>
    <link rel="icon" type="image/png" href="assets/images/logos.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/logos.png">
    <link rel="apple-touch-icon" href="assets/images/logos.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; }
        th, td { padding: 0.75rem; border: 1px solid #e2e8f0; text-align: left; }
        th { background: var(--navy); color: #fff; }
        .units-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 700; }
        .units-ok   { background: #d1fae5; color: #065f46; }
        .units-low  { background: #fef3c7; color: #92400e; }
        .units-none { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body style="padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Room Inventory Management</h1>
        <div>
            <a href="index.php" class="btn btn-outline">Dashboard</a>
            <a href="bookings.php" class="btn btn-outline">Bookings</a>
            <a href="logout.php" class="btn btn-gold">Logout</a>
        </div>
    </div>

    <?php if ($error): ?><p style="color: red; margin-bottom: 1rem;"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color: green; margin-bottom: 1rem;"><?php echo $success; ?></p><?php endif; ?>

    <!-- Add New Room Group Form -->
    <div style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); margin-bottom: 2rem;">
        <h3>Add New Room Group</h3>
        <form method="POST" action="rooms.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <input type="hidden" name="action" value="add_room">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <input type="text" name="room_group" placeholder="Room Group (e.g. Standard Deluxe)" class="form-control" required>
            <input type="text" name="type" placeholder="Category (e.g. Deluxe)" class="form-control" required>
            <input type="text" name="title" placeholder="Room Title" class="form-control" required>
            <input type="number" step="0.01" name="price" placeholder="Price per Night ($)" class="form-control" required>
            <input type="number" name="capacity" placeholder="Max Capacity" class="form-control" required>
            <input type="number" name="beds" placeholder="Number of Beds" class="form-control" required>
            <input type="number" name="size_sqm" placeholder="Size (sqm)" class="form-control" required>
            <input type="number" name="total_quantity" placeholder="Total Units in Group" class="form-control" min="1" value="10" required>
            <input type="text" name="image" placeholder="Image filename (e.g. deluxe.jpg)" class="form-control">
            <input type="text" name="amenities" placeholder="Amenities (comma separated)" class="form-control" required>
            <textarea name="description" placeholder="Room Description" class="form-control" style="grid-column: 1 / -1;" required></textarea>

            <button type="submit" class="btn btn-gold" style="grid-column: 1 / -1;">Save Room Group</button>
        </form>
    </div>

    <!-- Room List Table -->
    <h2>Current Room Groups</h2>
    <table>
        <thead>
            <tr>
                <th>Room Group</th>
                <th>Title</th>
                <th>Type</th>
                <th>Price</th>
                <th>Capacity</th>
                <th>Total Units</th>
                <th>Booked</th>
                <th>Remaining</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
                <?php
                    $total    = (int)$room['total_quantity'];
                    $booked   = (int)$room['booked_units'];
                    $remain   = max(0, $total - $booked);
                    $cls      = $remain <= 0 ? 'units-none' : ($remain <= 2 ? 'units-low' : 'units-ok');
                ?>
                <tr>
                    <td><strong><?php echo sanitize($room['room_group']); ?></strong></td>
                    <td><?php echo sanitize($room['title']); ?></td>
                    <td><?php echo sanitize($room['type']); ?></td>
                    <td>$<?php echo number_format($room['price'], 2); ?></td>
                    <td><?php echo $room['capacity']; ?> Guests</td>
                    <td><?php echo $total; ?></td>
                    <td><?php echo $booked; ?></td>
                    <td>
                        <span class="units-badge <?php echo $cls; ?>">
                            <?php echo $remain; ?>
                        </span>
                    </td>
                    <td>
                        <span style="color: <?php echo $room['status'] === 'available' ? 'green' : 'red'; ?>; font-weight: bold;">
                            <?php echo ucfirst($room['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="rooms.php?toggle_status=<?php echo $room['status']; ?>&id=<?php echo $room['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">
                            Toggle Status
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>