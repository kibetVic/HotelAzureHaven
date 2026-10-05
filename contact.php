<?php
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF verification failed.";
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid email address.";
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'message' => $message
            ]);
            $success = "Thank you for reaching out! Our team will respond shortly.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="margin-top: 4rem; margin-bottom: 4rem;">
    <h1 style="color: var(--navy, #0a192f); margin-bottom: 2rem; text-align: center; font-family: 'Playfair Display', serif;">Get in Touch</h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3rem; align-items: start;">
        <!-- Contact Form -->
        <div style="background: #fff; padding: 2.5rem; border-radius: 8px; box-shadow: var(--shadow, 0 10px 30px rgba(0,0,0,0.08));">
            <h3 style="margin-bottom: 1.5rem; color: var(--navy, #0a192f); font-family: 'Playfair Display', serif;">Send Us a Message</h3>

            <?php if ($error): ?>
                <div style="color: #e53e3e; background: #fff5f5; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="color: #38a169; background: #f0fff4; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="contact.php">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="name" style="display: block; margin-bottom: 0.4rem; font-weight: 600; color: var(--navy, #0a192f);">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;" required>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="email" style="display: block; margin-bottom: 0.4rem; font-weight: 600; color: var(--navy, #0a192f);">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;" required>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="message" style="display: block; margin-bottom: 0.4rem; font-weight: 600; color: var(--navy, #0a192f);">Message</label>
                    <textarea id="message" name="message" class="form-control" rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;" required></textarea>
                </div>

                <button type="submit" class="btn btn-gold" style="width: 100%; padding: 0.85rem; background: var(--gold, #c5a059); color: #0a192f; font-weight: 700; border: none; border-radius: 6px; cursor: pointer;">Send Message</button>
            </form>
        </div>

        <!-- Contact Information & Map -->
        <div>
            <div style="background: var(--navy, #0a192f); color: var(--cream, #f8fafc); padding: 2rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: var(--shadow, 0 10px 30px rgba(0,0,0,0.08));">
                <h3 style="color: var(--gold, #c5a059); margin-bottom: 1rem; font-family: 'Playfair Display', serif;">Hotel Location</h3>
                <p style="margin-bottom: 0.75rem;"><strong>Address:</strong> 205-20210 Ocean Drive, Mombasa Kenya</p>
                <p style="margin-bottom: 0.75rem;"><strong>Direct Line:</strong> +254 700276320</p>
                <p style="margin-bottom: 0.75rem;"><strong>Concierge Email:</strong> kibetvictor0603@gmail.com</p>
                <p style="margin-bottom: 0;"><strong>Reservations:</strong> bookings@azurehaven.com</p>
            </div>

            <!-- Clean, Valid Google Map Embed -->
            <div style="border-radius: 8px; overflow: hidden; box-shadow: var(--shadow, 0 10px 30px rgba(0,0,0,0.08)); line-height: 0;">
                <iframe 
                    title="Hotel Map Location"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.83543450937!2d144.9537295153167!3d-37.81627977975171!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d4c2b349649%3A0xb6899234e561db11!2sEnvato!5e0!3m2!1sen!2s!4v1614131234567!5m2!1sen!2s" 
                    width="100%" 
                    height="320" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>