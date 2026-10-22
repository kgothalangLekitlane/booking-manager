<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$flashMessage = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

// Dashboard summary: count all bookings by status in one database query.
$statsSql = "SELECT
    COUNT(*) AS total,
    COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) AS pending,
    COALESCE(SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END), 0) AS confirmed,
    COALESCE(SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END), 0) AS cancelled
    FROM bookings";
$statsStmt = $pdo->prepare($statsSql);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

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
        <p>View your booking overview, browse all bookings, or search by ID.</p>

        <p><a class="button-link" href="add-booking.php">+ Add booking</a></p>

        <?php if ($flashMessage !== ''): ?>
            <div class="message message-success" role="status">
                <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="dashboard" aria-labelledby="overview-heading">
            <h2 id="overview-heading">Booking overview</h2>
            <div class="stats-grid">
                <article class="stat-card">
                    <span class="stat-label">Total bookings</span>
                    <strong class="stat-number"><?= (int) $stats['total'] ?></strong>
                </article>
                <article class="stat-card">
                    <span class="stat-label">Pending</span>
                    <strong class="stat-number"><?= (int) $stats['pending'] ?></strong>
                </article>
                <article class="stat-card">
                    <span class="stat-label">Confirmed</span>
                    <strong class="stat-number"><?= (int) $stats['confirmed'] ?></strong>
                </article>
                <article class="stat-card">
                    <span class="stat-label">Cancelled</span>
                    <strong class="stat-number"><?= (int) $stats['cancelled'] ?></strong>
                </article>
            </div>
        </section>

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
                                <td><span class="status status-<?= strtolower(htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8')) ?>"><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
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
