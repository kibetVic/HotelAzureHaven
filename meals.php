<?php
require_once 'includes/functions.php';
$pdo = getDBConnection();

$categoryFilter = $_GET['category'] ?? '';
$message = '';
$error = '';

// Check if user is logged in (Optional)
$isLoggedIn = isLoggedIn();
$loggedInUser = null;

if ($isLoggedIn && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $loggedInUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Meal Order Submission with M-Pesa Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'inquire_meal') {
    $meal_title = sanitize($_POST['meal_title']);
    $unit_price = (float)$_POST['meal_price'];
    $quantity = max(1, (int)$_POST['quantity']);
    $total_price = $unit_price * $quantity;
    
    $deliver_to = sanitize($_POST['deliver_to']); // 'room' or 'table'
    $location_no = sanitize($_POST['location_no']);
    $customer_name = sanitize($_POST['customer_name']);
    $phone = sanitize($_POST['phone']);
    $special_notes = sanitize($_POST['special_notes']);

    // Format phone number to 2547XXXXXXXX
    $phone = preg_replace('/^0/', '254', preg_replace('/[^0-9]/', '', $phone));
    if (strlen($phone) === 9) {
        $phone = '254' . $phone;
    }

    if (empty($customer_name)) {
        $error = "Please enter your full name.";
    } else if (strlen($phone) !== 12 || !str_starts_with($phone, '254')) {
        $error = "Please enter a valid M-Pesa phone number (e.g., 0712345678 or 254712345678).";
    } else {
        // Trigger M-Pesa STK Push before confirming delivery
        $mpesaResponse = triggerMpesaSTKPush($phone, $total_price, "Meal - " . $quantity . "x " . $meal_title);

        if (isset($mpesaResponse['ResponseCode']) && $mpesaResponse['ResponseCode'] == '0') {
            $message = "STK Push sent to <strong>" . htmlspecialchars($phone) . "</strong>. Complete payment on your phone to finalize your order of <strong>{$quantity}x {$meal_title}</strong> ($" . number_format($total_price, 2) . ") to " . ucfirst($deliver_to) . " <strong>#{$location_no}</strong> for <strong>" . htmlspecialchars($customer_name) . "</strong>.";
        } else {
            $error = $mpesaResponse['errorMessage'] ?? "Payment initiation failed. Payment is required before order delivery.";
        }
    }
}

// 24 Default Meals & Beverages Collection
$defaultMeals = [
    ['id' => 1, 'category' => 'Main Dish', 'title' => 'Grilled Lobster Tail', 'description' => 'Fresh ocean lobster served with garlic herb butter and wild rice.', 'price' => 45.00, 'image_url' => 'https://images.unsplash.com/photo-1553240799-36bbf332a5c3?auto=format&fit=crop&w=600&q=80'],
    ['id' => 2, 'category' => 'Main Dish', 'title' => 'Prime Ribeye Steak', 'description' => 'Aged beef ribeye grilled to perfection with truffled mashed potatoes.', 'price' => 38.50, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80'],
    ['id' => 3, 'category' => 'Main Dish', 'title' => 'Pan-Seared Atlantic Salmon', 'description' => 'Crispy skin salmon served over asparagus and citrus lemon glaze.', 'price' => 32.00, 'image_url' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=600&q=80'],
    ['id' => 4, 'category' => 'Main Dish', 'title' => 'Truffle Mushroom Pasta', 'description' => 'Handmade fettuccine tossing wild mushrooms and creamy black truffle sauce.', 'price' => 26.00, 'image_url' => 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?auto=format&fit=crop&w=600&q=80'],
    ['id' => 5, 'category' => 'Main Dish', 'title' => 'Coastal Seafood Paella', 'description' => 'Saffron-infused rice loaded with prawns, mussels, calamari, and chorizo.', 'price' => 34.00, 'image_url' => 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?auto=format&fit=crop&w=600&q=80'],
    ['id' => 6, 'category' => 'Appetizer', 'title' => 'Crispy Calamari Rings', 'description' => 'Tender calamari lightly fried with zesty spicy aioli dipping sauce.', 'price' => 16.50, 'image_url' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?auto=format&fit=crop&w=600&q=80'],
    ['id' => 7, 'category' => 'Appetizer', 'title' => 'Artisan Cheese & Charcuterie', 'description' => 'Assorted local cheeses, cured meats, nuts, and honeycomb.', 'price' => 24.00, 'image_url' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=600&q=80'],
    ['id' => 8, 'category' => 'Appetizer', 'title' => 'Bruschetta Trio', 'description' => 'Toasted sourdough topped with tomatoes, basil, olive oil, and balsamic.', 'price' => 14.00, 'image_url' => 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?auto=format&fit=crop&w=600&q=80'],
    ['id' => 9, 'category' => 'Breakfast', 'title' => 'Full Azure Breakfast', 'description' => 'Eggs to order, smoked bacon, sausages, baked beans, and sourdough.', 'price' => 22.00, 'image_url' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?auto=format&fit=crop&w=600&q=80'],
    ['id' => 10, 'category' => 'Breakfast', 'title' => 'Avocado & Poached Egg Toast', 'description' => 'Crushed avocado, organic poached eggs, and chili flakes on artisan sourdough.', 'price' => 18.00, 'image_url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80'],
    ['id' => 11, 'category' => 'Breakfast', 'title' => 'Belgian Berry Waffles', 'description' => 'Crispy waffles topped with maple syrup, fresh berries, and whipped cream.', 'price' => 16.00, 'image_url' => 'https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=600&q=80'],
    ['id' => 12, 'category' => 'Dessert', 'title' => 'Valrhona Chocolate Lava Cake', 'description' => 'Warm chocolate cake with a molten center, served with vanilla bean gelato.', 'price' => 14.50, 'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=600&q=80'],
    ['id' => 13, 'category' => 'Dessert', 'title' => 'Classic Italian Tiramisu', 'description' => 'Espresso-soaked ladyfingers layered with rich mascarpone cream.', 'price' => 12.00, 'image_url' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=600&q=80'],
    ['id' => 14, 'category' => 'Beverage', 'title' => 'Tropical Mango Passion Smoothie', 'description' => 'Fresh mango, passion fruit, Greek yogurt, and coconut water blend.', 'price' => 9.50, 'image_url' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?auto=format&fit=crop&w=600&q=80'],
    ['id' => 15, 'category' => 'Beverage', 'title' => 'Azure Ocean Mojito', 'description' => 'White rum, fresh lime juice, mint leaves, blue curaçao, and sparkling water.', 'price' => 15.00, 'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80'],
    ['id' => 16, 'category' => 'Beverage', 'title' => 'Chardonnay Reserve Glass', 'description' => 'Crisp oak-aged white wine with notes of green apple and vanilla.', 'price' => 13.00, 'image_url' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=600&q=80'],
    ['id' => 17, 'category' => 'Beverage', 'title' => 'Artisan Cold Brew Espresso', 'description' => 'Slow-steeped dark roast coffee served over ice with vanilla foam.', 'price' => 7.00, 'image_url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80'],
    ['id' => 18, 'category' => 'Beverage', 'title' => 'Fresh Coconut Water', 'description' => 'Served cold straight in a fresh young coconut.', 'price' => 8.00, 'image_url' => 'https://images.unsplash.com/photo-1525385133512-2f3bdd039054?auto=format&fit=crop&w=600&q=80'],
    ['id' => 19, 'category' => 'Main Dish', 'title' => 'Gourmet Wagyu Burger', 'description' => 'Wagyu beef patty, smoked cheddar, caramelized onions, and truffle fries.', 'price' => 28.00, 'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80'],
    ['id' => 20, 'category' => 'Appetizer', 'title' => 'Fresh Pacific Oysters (6pcs)', 'description' => 'Chilled oysters served on crushed ice with mignonette and lemon.', 'price' => 27.00, 'image_url' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=600&q=80'],
    ['id' => 21, 'category' => 'Dessert', 'title' => 'Mango Coconut Panna Cotta', 'description' => 'Silky coconut cream dessert topped with fresh mango puree.', 'price' => 11.00, 'image_url' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80'],
    ['id' => 22, 'category' => 'Appetizer', 'title' => 'Seared Ahi Tuna Tartare', 'description' => 'Sustainably caught tuna with avocado, sesame oil, and crispy wontons.', 'price' => 21.50, 'image_url' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=600&q=80'],
    ['id' => 23, 'category' => 'Breakfast', 'title' => 'Smoked Salmon Eggs Benedict', 'description' => 'Poached eggs, wild smoked salmon, and hollandaise sauce on English muffins.', 'price' => 20.00, 'image_url' => 'https://images.unsplash.com/photo-1608039829572-78524f79c4c7?auto=format&fit=crop&w=600&q=80'],
    ['id' => 24, 'category' => 'Beverage', 'title' => 'Sparkling Berry Sangria', 'description' => 'Chilled prosecco infused with fresh raspberries, blueberries, and citrus.', 'price' => 14.00, 'image_url' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80']
];

// Query DB or fallback
$dbMeals = [];
try {
    $sql = "SELECT * FROM meals WHERE status = 'available'";
    if ($categoryFilter) {
        $sql .= " AND category = :cat";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cat' => $categoryFilter]);
    } else {
        $stmt = $pdo->query($sql);
    }
    $dbMeals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $dbMeals = [];
}

$displayMeals = !empty($dbMeals) ? $dbMeals : array_filter($defaultMeals, function($dm) use ($categoryFilter) {
    return !$categoryFilter || strtolower($dm['category']) === strtolower($categoryFilter);
});

include 'includes/header.php';
?>

<style>
.meals-fullwidth-wrapper {
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  padding: 6rem 2rem 4rem;
  background-color: var(--cream, #fdfbf7);
}

.meals-header-banner {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 3rem;
}

.meals-header-banner h1 {
  font-size: 3rem;
  color: var(--navy, #0a192f);
  margin-bottom: 0.75rem;
}

.meals-filter-bar {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 3rem;
}

.meals-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  width: 100%;
}

/* Order Modal */
.modal-overlay {
  display: none;
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(10, 25, 47, 0.75);
  z-index: 2000;
  justify-content: center;
  align-items: center;
  padding: 1rem;
}
.modal-overlay.active { display: flex; }
.modal-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 2rem;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
</style>

<div class="meals-fullwidth-wrapper">
    <div class="meals-header-banner">
        <h1>Gourmet Dining & Beverages</h1>
        <p>Order luxury culinary dishes and freshly prepared beverages straight to your hotel room or restaurant table.</p>
    </div>

    <?php if ($message): ?>
        <div style="max-width:800px; margin:0 auto 2rem; padding:1rem; background:#dcfce7; color:#15803d; border-radius:6px; border:1px solid #bbf7d0; text-align:center;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="max-width:800px; margin:0 auto 2rem; padding:1rem; background:#fef2f2; color:#b91c1c; border-radius:6px; border:1px solid #fecaca; text-align:center;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Category Filter Bar -->
    <form method="GET" class="meals-filter-bar">
        <select name="category" class="form-control" style="max-width:280px; background:#fff;">
            <option value="">All Categories</option>
            <option value="Main Dish" <?php echo $categoryFilter === 'Main Dish' ? 'selected' : ''; ?>>Main Dishes</option>
            <option value="Appetizer" <?php echo $categoryFilter === 'Appetizer' ? 'selected' : ''; ?>>Appetizers</option>
            <option value="Breakfast" <?php echo $categoryFilter === 'Breakfast' ? 'selected' : ''; ?>>Breakfast</option>
            <option value="Dessert" <?php echo $categoryFilter === 'Dessert' ? 'selected' : ''; ?>>Desserts</option>
            <option value="Beverage" <?php echo $categoryFilter === 'Beverage' ? 'selected' : ''; ?>>Beverages</option>
        </select>
        <button type="submit" class="btn btn-gold">Filter Menu</button>
    </form>

    <!-- 24 Items Grid Display -->
    <div class="meals-grid">
        <?php foreach ($displayMeals as $item): ?>
            <div class="card" style="background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                <img src="<?php echo $item['image_url']; ?>" class="card-img" style="width:100%; height:200px; object-fit:cover;" alt="<?php echo sanitize($item['title']); ?>">
                <div class="card-body" style="padding:1.25rem; display:flex; flex-direction:column; justify-content:space-between; min-height:220px;">
                    <div>
                        <span style="font-size:0.75rem; text-transform:uppercase; color:var(--gold, #c5a059); font-weight:700; letter-spacing:1px;">
                            <?php echo sanitize($item['category']); ?>
                        </span>
                        <h3 style="margin: 0.25rem 0 0.5rem; font-size:1.2rem; color:var(--navy, #0a192f);"><?php echo sanitize($item['title']); ?></h3>
                        <p style="font-size:0.88rem; color:#64748b; margin-bottom:1rem;">
                            <?php echo sanitize($item['description']); ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-weight:700; color:var(--navy, #0a192f); font-size:1.3rem; margin-bottom:0.75rem;">
                            $<?php echo number_format($item['price'], 2); ?>
                        </p>
                        <button type="button" class="btn btn-gold" style="width:100%;" 
                                onclick="openInquiryModal('<?php echo sanitize(addslashes($item['title'])); ?>', <?php echo floatval($item['price']); ?>)">
                            Order Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Order & Payment Modal -->
<div id="mealInquiryModal" class="modal-overlay">
    <div class="modal-card">
        <h2 style="color:var(--navy, #0a192f); margin-bottom:0.25rem;" id="modalMealTitleDisplay">Order Item</h2>
        <p style="color:#64748b; font-size:0.95rem; margin-bottom:1.25rem;">No login required. Complete details & pay via M-Pesa prior to delivery.</p>

        <form method="POST" action="meals.php">
            <input type="hidden" name="action" value="inquire_meal">
            <input type="hidden" name="meal_title" id="modalMealTitle">
            <input type="hidden" name="meal_price" id="modalMealPrice">

            <!-- Customer Full Name -->
            <div class="form-group" style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.3rem;">Full Name</label>
                <input type="text" name="customer_name" class="form-control" 
                       value="<?php echo htmlspecialchars($loggedInUser['name'] ?? ''); ?>" 
                       placeholder="e.g. John Doe" required style="width:100%; padding:0.6rem; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1rem;">
                <div class="form-group">
                    <label style="display:block; font-weight:600; margin-bottom:0.3rem;">Delivery Point</label>
                    <select name="deliver_to" class="form-control" required style="width:100%; padding:0.6rem; border:1px solid #cbd5e1; border-radius:6px;">
                        <option value="room">Hotel Room</option>
                        <option value="table">Restaurant Table</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:block; font-weight:600; margin-bottom:0.3rem;">Room / Table #</label>
                    <input type="text" name="location_no" class="form-control" placeholder="e.g. 302 or 12" required style="width:100%; padding:0.6rem; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.3rem;">Quantity</label>
                <input type="number" id="modalQuantity" name="quantity" class="form-control" value="1" min="1" max="20" required style="width:100%; padding:0.6rem; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.3rem;">M-Pesa Phone Number</label>
                <input type="text" name="phone" class="form-control" 
                       value="<?php echo htmlspecialchars($loggedInUser['phone'] ?? ''); ?>" 
                       placeholder="07XXXXXXXX or 2547XXXXXXXX" required style="width:100%; padding:0.6rem; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.3rem;">Special Instructions</label>
                <textarea name="special_notes" class="form-control" rows="2" placeholder="e.g. Extra ice, low spice..." style="width:100%; padding:0.6rem; border:1px solid #cbd5e1; border-radius:6px;"></textarea>
            </div>

            <!-- Total Calculation Display -->
            <div style="background:#f8fafc; padding:0.85rem; border-radius:6px; border:1px solid #e2e8f0; margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:600; color:#64748b;">Total Amount Due:</span>
                <span style="font-size:1.2rem; font-weight:700; color:var(--navy, #0a192f);">$<span id="modalTotalPrice">0.00</span></span>
            </div>

            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-gold" style="flex:1; padding:0.75rem;">Pay & Place Order</button>
                <button type="button" class="btn btn-outline" style="flex:1; padding:0.75rem;" onclick="closeInquiryModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentUnitPrice = 0;

function openInquiryModal(title, price) {
    currentUnitPrice = parseFloat(price);
    document.getElementById('modalMealTitle').value = title;
    document.getElementById('modalMealTitleDisplay').innerText = 'Order ' + title;
    document.getElementById('modalMealPrice').value = currentUnitPrice.toFixed(2);
    document.getElementById('modalQuantity').value = 1;
    
    updateOrderTotal();
    document.getElementById('mealInquiryModal').classList.add('active');
}

function updateOrderTotal() {
    const qty = parseInt(document.getElementById('modalQuantity').value) || 1;
    const total = currentUnitPrice * qty;
    document.getElementById('modalTotalPrice').innerText = total.toFixed(2);
}

document.getElementById('modalQuantity').addEventListener('input', updateOrderTotal);

function closeInquiryModal() {
    document.getElementById('mealInquiryModal').classList.remove('active');
}
</script>

<?php include 'includes/footer.php'; ?>