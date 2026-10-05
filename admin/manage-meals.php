<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';

// Handle Add or Update Meal/Price in DB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $image_url = sanitize($_POST['image_url']);
    $status = sanitize($_POST['status']);

    if (!empty($_POST['meal_id'])) {
        // Update existing meal price and info
        $stmt = $pdo->prepare("UPDATE meals SET title = :title, category = :category, description = :description, price = :price, image_url = :image_url, status = :status WHERE id = :id");
        $stmt->execute([
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'price' => $price,
            'image_url' => $image_url,
            'status' => $status,
            'id' => intval($_POST['meal_id'])
        ]);
        $msg = "Meal price and details updated successfully!";
    } else {
        // Insert new meal
        $stmt = $pdo->prepare("INSERT INTO meals (title, category, description, price, image_url, status) VALUES (:title, :category, :description, :price, :image_url, :status)");
        $stmt->execute([
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'price' => $price,
            'image_url' => $image_url,
            'status' => $status
        ]);
        $msg = "New meal item saved to database!";
    }
}

// Delete item
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM meals WHERE id = :id");
    $stmt->execute(['id' => $delId]);
    header("Location: manage-meals.php");
    exit();
}

// Fetch all DB meals
$meals = $pdo->query("SELECT * FROM meals ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Meals & Prices</title>
    <!-- Favicon (Browser Title Bar Logo) -->
    <link rel="icon" type="image/png" href="assets/images/logos.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/logos.png">
    <link rel="apple-touch-icon" href="assets/images/logos.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background:var(--cream); padding:2rem;">

<div style="max-width:1100px; margin:0 auto; background:var(--white); padding:2rem; border-radius:8px; box-shadow:var(--shadow);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h1 style="color:var(--navy);">Manage Meals & Set DB Prices</h1>
        <a href="index.php" class="btn btn-outline">Back to Admin Dashboard</a>
    </div>

    <?php if ($msg): ?>
        <p style="padding:1rem; background:#d4edda; color:#155724; border-radius:4px; margin-bottom:1.5rem;"><?php echo $msg; ?></p>
    <?php endif; ?>

    <!-- Add/Edit Meal Form -->
    <form method="POST" style="background:var(--cream); padding:1.5rem; border-radius:8px; margin-bottom:2.5rem;">
        <h3 style="color:var(--navy); margin-bottom:1rem;">Add / Edit Food & Beverage Item</h3>
        <input type="hidden" name="meal_id" id="meal_id">
        
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" id="category" class="form-control" required>
                    <option value="Main Dish">Main Dish</option>
                    <option value="Appetizer">Appetizer</option>
                    <option value="Breakfast">Breakfast</option>
                    <option value="Dessert">Dessert</option>
                    <option value="Beverage">Beverage</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" required>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:2fr 1fr; gap:1rem;">
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="image_url" id="image_url" class="form-control" placeholder="https://..." required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="description" class="form-control" rows="2" required></textarea>
        </div>

        <button type="submit" class="btn btn-gold">Save Meal to Database</button>
    </form>

    <!-- List of Meals in DB -->
    <h3 style="color:var(--navy); margin-bottom:1rem;">Database Meal Registry</h3>
    <table style="width:100%; border-collapse:collapse; text-align:left;">
        <thead>
            <tr style="border-bottom:2px solid var(--navy); background:var(--cream);">
                <th style="padding:0.75rem;">Image</th>
                <th style="padding:0.75rem;">Title</th>
                <th style="padding:0.75rem;">Category</th>
                <th style="padding:0.75rem;">Price</th>
                <th style="padding:0.75rem;">Status</th>
                <th style="padding:0.75rem;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($meals)): ?>
                <tr><td colspan="6" style="padding:1rem;">No items found in the database yet. Use the form above to add one.</td></tr>
            <?php else: ?>
                <?php foreach ($meals as $m): ?>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:0.5rem;"><img src="<?php echo $m['image_url']; ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;"></td>
                        <td style="padding:0.5rem; font-weight:600;"><?php echo sanitize($m['title']); ?></td>
                        <td style="padding:0.5rem;"><?php echo sanitize($m['category']); ?></td>
                        <td style="padding:0.5rem; font-weight:700; color:var(--gold);">$<?php echo number_format($m['price'], 2); ?></td>
                        <td style="padding:0.5rem;"><?php echo ucfirst($m['status']); ?></td>
                        <td style="padding:0.5rem;">
                            <button class="btn btn-outline" style="padding:0.25rem 0.5rem; font-size:0.8rem;" 
                                    onclick='editMeal(<?php echo json_encode($m); ?>)'>Edit</button>
                            <a href="manage-meals.php?delete=<?php echo $m['id']; ?>" class="btn btn-gold" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Delete item?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function editMeal(item) {
    document.getElementById('meal_id').value = item.id;
    document.getElementById('title').value = item.title;
    document.getElementById('category').value = item.category;
    document.getElementById('price').value = item.price;
    document.getElementById('image_url').value = item.image_url;
    document.getElementById('status').value = item.status;
    document.getElementById('description').value = item.description;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

</body>
</html>