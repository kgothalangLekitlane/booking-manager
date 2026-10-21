<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$flashMessage = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

$searchId = '';
$searchError = '';
$searching = false;
$bookings = [];

if (isset($_GET['id'])) {
    $searching = true;
    $rawId = $_GET['id'];

    if (!is_string($rawId)) {
        $searchError = 'Enter a valid booking ID.';
    } else {
        $searchId = trim($rawId);

        if ($searchId === '' || !ctype_digit($searchId) || (int) $searchId < 1) {
            $searchError = 'Enter a booking ID that is a positive whole number.';
        } else {
            $sql = 'SELECT * FROM bookings WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => (int) $searchId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking !== false) {
                $bookings[] = $booking;
            }
        }
    }
} else {
    $sql = 'SELECT * FROM bookings ORDER BY booking_date ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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
        <p>View all bookings or search for one by its ID.</p>

        <p><a class="button-link" href="add-booking.php">+ Add booking</a></p>

        <?php if ($flashMessage !== ''): ?>
            <div class="message message-success" role="status">
                <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="get" action="index.php" class="search-form">
            <label for="id">Search by booking ID</label>
            <input
                type="number"
                id="id"
                name="id"
                min="1"
                step="1"
                value="<?= htmlspecialchars($searchId, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="For example, 12"
                required
            >
            <div class="form-actions">
                <button type="submit">Search</button>
                <?php if ($searching): ?>
                    <a href="index.php">Show all bookings</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($searchError !== ''): ?>
            <div class="message message-error" role="alert">
                <?= htmlspecialchars($searchError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif ($searching && $bookings === []): ?>
            <p>No booking found with ID <?= htmlspecialchars($searchId, ENT_QUOTES, 'UTF-8') ?>.</p>
        <?php elseif ($bookings !== []): ?>
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
