<?php
require_once 'includes/functions.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF validation failed.";
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $pdo = getDBConnection();
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (:n, :e, :p, :ph)");
            $stmt->execute(['n' => $name, 'e' => $email, 'p' => $password, 'ph' => $phone]);
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $error = "Email address is already registered.";
        }
    }
}
include 'includes/header.php';
?>

<!-- Centering Wrapper -->
<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 2rem 1rem;">
    <div class="container" style="max-width: 450px; width: 100%; text-align: center;">
        <h2 style="margin-bottom: 1.5rem;">Register Account</h2>
        <?php if ($error): ?><p style="color:red; margin-bottom: 1rem;"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST" style="text-align: left;">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Full Name</label>
                <input type="text" name="name" class="form-control" required style="width: 100%;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Email</label>
                <input type="email" name="email" class="form-control" required style="width: 100%;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Phone</label>
                <input type="text" name="phone" class="form-control" style="width: 100%;">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Password</label>
                <input type="password" name="password" class="form-control" required style="width: 100%;">
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;">Register</button>

            <!-- Login Prompt Link -->
            <p style="margin-top: 1.25rem; text-align: center; font-size: 0.95rem; color: #64748b;">
                Have an account? 
                <a href="login.php" style="color: var(--gold, #c5a059); text-decoration: none; font-weight: 600;">Login here</a>
            </p>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>