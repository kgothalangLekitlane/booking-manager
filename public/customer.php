<?php
require_once __DIR__ . '/../config/database.php';
$email = $_GET['email'] ?? '';
if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('A valid customer email is required.');
}
$stmt = $pdo->prepare('SELECT customer_name, email, booking_date, status, total_amount, id FROM bookings WHERE email = :email ORDER BY booking_date DESC, id DESC');
$stmt->execute(['email' => $email]);
$bookings = $stmt->fetchAll();
if (!$bookings) { http_response_code(404); exit('Customer not found.'); }
$totalSpent = 0;
foreach ($bookings as $booking) {
    if ($booking['status'] === 'Confirmed') $totalSpent += (float)$booking['total_amount'];
}
function h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Customer profile</title><link rel="stylesheet" href="style.css"></head>
<body><main class="container"><nav class="top-nav"><a href="index.php">Dashboard</a><a href="customers.php">Customers</a><a href="calendar.php">Calendar</a></nav>
<h1><?= h($bookings[0]['customer_name']) ?></h1><p><?= h($email) ?></p>
<div class="stats-grid"><article class="stat-card"><span class="stat-label">Bookings</span><strong class="stat-number"><?= count($bookings) ?></strong></article><article class="stat-card"><span class="stat-label">Confirmed revenue</span><strong class="stat-number">R<?= number_format($totalSpent,2) ?></strong></article></div>
<h2>Booking history</h2><div class="table-wrapper"><table><thead><tr><th>ID</th><th>Date</th><th>Status</th><th>Amount</th><th>Action</th></tr></thead><tbody>
<?php foreach ($bookings as $booking): ?><tr><td><?= (int)$booking['id'] ?></td><td><?= h($booking['booking_date']) ?></td><td><?= h($booking['status']) ?></td><td>R<?= number_format((float)$booking['total_amount'],2) ?></td><td><a class="action-link" href="edit-booking.php?id=<?= (int)$booking['id'] ?>">Edit</a></td></tr><?php endforeach; ?>
</tbody></table></div></main></body></html>