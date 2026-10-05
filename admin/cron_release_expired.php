<?php
/**
 * admin/cron_release_expired.php
 *
 * Auto-releases rooms whose bookings have passed their checkout date.
 *
 * RELEASE RULE:
 *   A booking is "expired" once check_out < today,
 *   OR when check_out = today AND the current time is >= 09:00 hrs (Africa/Nairobi).
 *
 * WHAT IT DOES:
 *   Marks expired active bookings (confirmed | pending | pending_payment) as
 *   "completed". Every availability query filters on the active set, so units
 *   automatically return to inventory.
 *
 * SCHEDULE (cron):  run at 09:00 daily
 *   0 9 * * *  php /path/to/admin/cron_release_expired.php >> /var/log/azure_haven_cron.log 2>&1
 *
 * Can also be triggered:
 *   - In browser: /admin/cron_release_expired.php (admin session required)
 *   - With token: /admin/cron_release_expired.php?token=YOUR_CRON_SECRET
 *   - As an include (from index.php): set $CRON_CAPTURE = true first;
 *     the script then returns the log string instead of echoing.
 */

date_default_timezone_set('Africa/Nairobi');

require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();

// ------------------------------------------------------------------
// Capture mode (used when included from another PHP page)
// ------------------------------------------------------------------
$isCapture = !empty($CRON_CAPTURE);

// ------------------------------------------------------------------
// Access control: CLI, admin session, or secret token
// (skipped entirely in capture mode — caller already authorized)
// ------------------------------------------------------------------
if (!$isCapture) {
    $isCli = (php_sapi_name() === 'cli');

    $cronSecret = defined('CRON_SECRET') ? CRON_SECRET : 'change-me-in-config';
    $secretOk   = isset($_GET['token']) && hash_equals($cronSecret, $_GET['token']);

    if (!$isCli) {
        if (!$secretOk) {
            if (!function_exists('isAdmin') || !isAdmin()) {
                http_response_code(403);
                header('Content-Type: text/plain');
                echo "Forbidden.\n";
                exit();
            }
        }
        header('Content-Type: text/plain; charset=utf-8');
    }
}

// ------------------------------------------------------------------
// Cutoff computation
// ------------------------------------------------------------------
$now         = new DateTime('now');
$today       = $now->format('Y-m-d');
$nowTime     = $now->format('H:i:s');
$releaseHour = '09:00:00';

$log = [];
$log[] = "=== Azure Haven — Auto-Release Cron ===";
$log[] = "Run at: {$now->format('Y-m-d H:i:s')} (Africa/Nairobi)";
$log[] = "Rule: release bookings where check_out < today, OR (check_out = today AND now >= 09:00)";
$log[] = "";

try {
    // ------------------------------------------------------------------
    // Each named placeholder is unique.
    //   :today_lt  used for check_out <  X
    //   :today_eq  used for check_out =  X
    // (PDO + MySQL native prepares cannot reuse :today twice.)
    // ------------------------------------------------------------------
    $sel = $pdo->prepare(
        "SELECT id, room_id, quantity, check_in, check_out, status, guest_name, user_id
         FROM bookings
         WHERE status IN ('confirmed','pending','pending_payment')
           AND (
                check_out < :today_lt
                OR (check_out = :today_eq AND :now_time >= :release_hour)
           )
         ORDER BY check_out ASC, id ASC"
    );
    $sel->execute([
        'today_lt'     => $today,
        'today_eq'     => $today,
        'now_time'     => $nowTime,
        'release_hour' => $releaseHour,
    ]);
    $expired = $sel->fetchAll(PDO::FETCH_ASSOC);

    if (!$expired) {
        $log[] = "No expired bookings found. Nothing to do.";
    } else {
        $log[] = "Found " . count($expired) . " booking(s) to release:";
        $log[] = str_repeat('-', 100);
        $log[] = sprintf('%-6s %-8s %-6s %-12s %-12s %-18s %-20s',
                         'ID', 'Room', 'Qty', 'Check-In', 'Check-Out', 'Status', 'Guest');
        $log[] = str_repeat('-', 100);

        foreach ($expired as $b) {
            $guest = $b['guest_name'] ?: ('User #' . ($b['user_id'] ?? '?'));
            $log[] = sprintf('%-6s %-8s %-6s %-12s %-12s %-18s %-20s',
                $b['id'], $b['room_id'], $b['quantity'],
                $b['check_in'], $b['check_out'], $b['status'], $guest);
        }
        $log[] = str_repeat('-', 100);

        // Bulk update — use positional (?) to avoid named-param clash
        $ids = array_column($expired, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $upd = $pdo->prepare(
            "UPDATE bookings
                SET status = 'completed'
              WHERE id IN ($placeholders)
                AND status IN ('confirmed','pending','pending_payment')"
        );
        $upd->execute($ids);
        $affected = $upd->rowCount();

        $log[] = "";
        $log[] = "✅ Marked {$affected} booking(s) as COMPLETED.";
        $log[] = "Rooms have been automatically restored to inventory.";
    }

    // ------------------------------------------------------------------
    // Inventory snapshot after update
    // ------------------------------------------------------------------
    $log[] = "";
    $log[] = "Current inventory snapshot (active bookings vs total):";
    $log[] = str_repeat('-', 80);
    $log[] = sprintf('%-6s %-35s %-10s %-10s %-10s', 'ID', 'Room Group', 'Total', 'Booked', 'Free');
    $log[] = str_repeat('-', 80);

    $snap = $pdo->query(
        "SELECT r.id, r.room_group, r.total_quantity,
                COALESCE(SUM(CASE WHEN b.status IN ('confirmed','pending','pending_payment')
                                   AND b.check_out >= CURDATE()
                             THEN b.quantity ELSE 0 END), 0) AS active_booked
         FROM rooms r
         LEFT JOIN bookings b ON b.room_id = r.id
         GROUP BY r.id
         ORDER BY r.room_group"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($snap as $s) {
        $free = max(0, (int)$s['total_quantity'] - (int)$s['active_booked']);
        $log[] = sprintf('%-6s %-35s %-10s %-10s %-10s',
            $s['id'], $s['room_group'], $s['total_quantity'], $s['active_booked'], $free);
    }
    $log[] = str_repeat('-', 80);

} catch (Exception $e) {
    $log[] = "❌ ERROR: " . $e->getMessage();
}

$log[] = "";
$log[] = "=== Finished ===";

$output = implode(PHP_EOL, $log) . PHP_EOL;

// ------------------------------------------------------------------
// Output — either echo (direct run) or return (capture mode)
// ------------------------------------------------------------------
if ($isCapture) {
    return $output;
}
echo $output;