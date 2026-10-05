<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../login.php");
        exit();
    }
}

function checkRoomAvailability($roomId, $checkIn, $checkOut) {
    $pdo = getDBConnection();
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE room_id = :room_id 
              AND status IN ('pending', 'confirmed') 
              AND (check_in < :check_out AND check_out > :check_in)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'room_id'   => $roomId,
        'check_in'  => $checkIn,
        'check_out' => $checkOut
    ]);
    return $stmt->fetchColumn() == 0;
}

function sendConfirmationEmail($toEmail, $bookingDetails) {
    $subject = "Booking Confirmation - Azure Haven Hotel";
    $message = "Dear Customer,\n\nThank you for booking with Azure Haven Hotel!\n\nDetails:\n";
    $message .= "Check-in: {$bookingDetails['check_in']}\n";
    $message .= "Check-out: {$bookingDetails['check_out']}\n";
    $message .= "Total Amount: $" . number_format($bookingDetails['total_price'], 2) . "\n\n";
    $message .= "We look forward to hosting your luxurious escape.\n\nWarm regards,\nAzure Haven Team";
    $headers = "From: no-reply@azurehaven.com\r\nX-Mailer: PHP/" . phpversion();

    @mail($toEmail, $subject, $message, $headers);
}

function triggerMpesaSTKPush($phone, $amount, $accountReference) {
    $consumerKey = 'YOUR_CONSUMER_KEY'; 
    $consumerSecret = 'YOUR_CONSUMER_SECRET';
    $BusinessShortCode = '174379'; // Sandbox Paybill/Till
    $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    
    $timestamp = date('YmdHis');
    $password = base64_encode($BusinessShortCode . $Passkey . $timestamp);

    // Get OAuth Token
    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = json_decode(curl_exec($curl));
    curl_close($curl);

    if (!isset($result->access_token)) {
        return ['errorMessage' => 'Unable to authenticate with M-Pesa.'];
    }

    $accessToken = $result->access_token;

    // Initiate STK Push Request
    $stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $stkHeaders = ['Content-Type:application/json', 'Authorization:Bearer ' . $accessToken];

    $stkPayload = [
        'BusinessShortCode' => $BusinessShortCode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => round($amount),
        'PartyA' => $phone,
        'PartyB' => $BusinessShortCode,
        'PhoneNumber' => $phone,
        'CallBackURL' => 'https://yourdomain.com/mpesa_callback.php',
        'AccountReference' => $accountReference,
        'TransactionDesc' => 'Room Booking Payment'
    ];

    $curl = curl_init($stkUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stkHeaders);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stkPayload));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);

    return $response;
}

function maybeAutoReleaseExpiredBookings() {
    $pdo = getDBConnection();
    // Run at most once per hour
    $stmt = $pdo->query("SELECT MAX(run_at) FROM cron_log WHERE job = 'auto_release'");
    $last = $stmt->fetchColumn();
    if ($last && (time() - strtotime($last)) < 3600) return;

    try {
        $pdo->beginTransaction();

        $upd = $pdo->prepare(
            "UPDATE bookings
                SET status = 'completed'
              WHERE status IN ('confirmed','pending','pending_payment')
                AND (
                     check_out < CURDATE()
                     OR (check_out = CURDATE() AND CURTIME() >= '09:00:00')
                )"
        );
        $upd->execute();

        $pdo->prepare("INSERT INTO cron_log (job, run_at) VALUES ('auto_release', NOW())")
            ->execute();

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
}

function activeBookingStatuses() {
    return "'confirmed','pending','pending_payment'";
}

function activeBookingStatusesArray() {
    return ['confirmed','pending','pending_payment'];
}
