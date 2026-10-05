<?php
require_once 'includes/functions.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF verification failed.";
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];

        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: " . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
include 'includes/header.php';
?>

<!-- Centering Wrapper -->
<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 2rem 1rem;">
    <div class="container" style="max-width: 450px; width: 100%; text-align: center;">
        <h2 style="margin-bottom: 1.5rem;">Guest Login</h2>
        <?php if ($error): ?><p style="color:red; margin-bottom: 1rem;"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST" style="text-align: left;">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Email</label>
                <input type="email" name="email" class="form-control" required style="width: 100%;">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Password</label>
                <input type="password" name="password" class="form-control" required style="width: 100%;">
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;">Login</button>
            
            <!-- Register Prompt Link -->
            <p style="margin-top: 1.25rem; text-align: center; font-size: 0.95rem; color: #64748b;">
                Don't have an account? 
                <a href="register.php" style="color: var(--gold, #c5a059); text-decoration: none; font-weight: 600;">Register here</a>
            </p>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>