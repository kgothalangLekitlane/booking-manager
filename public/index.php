<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$flashMessage = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

$sql = 'SELECT * FROM bookings ORDER BY booking_date ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Booking Manager</h1>
        <p>All bookings currently stored in the database.</p>

        <p><a class="button-link" href="add-booking.php">+ Add booking</a></p>

        <?php if ($flashMessage !== ''): ?>
            <div class="message message-success" role="status">
                <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (count($bookings) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Booking date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $booking['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($booking['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($booking['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($booking['booking_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No bookings found yet. Add your first booking using the button above.</p>
        <?php endif; ?>
    </main>
</body>
</html>
