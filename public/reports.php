<?php
require_once __DIR__ . '/../config/database.php';
$stmt = $pdo->query("SELECT DATE_FORMAT(booking_date, '%Y-%m') AS month_key, COUNT(*) AS booking_count, SUM(CASE WHEN status = 'Confirmed' THEN total_amount ELSE 0 END) AS revenue, SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed_count FROM bookings GROUP BY DATE_FORMAT(booking_date, '%Y-%m') ORDER BY month_key DESC");
$months = $stmt->fetchAll();
$totals = $pdo->query("SELECT COUNT(*) AS bookings, SUM(CASE WHEN status = 'Confirmed' THEN total_amount ELSE 0 END) AS revenue, SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed FROM bookings")->fetch();
function h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Booking reports</title><link rel="stylesheet" href="style.css"></head>
<body><main class="container"><nav class="top-nav"><a href="index.php">Dashboard</a><a href="customers.php">Customers</a><a href="calendar.php">Calendar</a></nav>
<h1>Revenue & booking reports</h1><p>Revenue is calculated from confirmed bookings only.</p>
<div class="stats-grid"><article class="stat-card"><span class="stat-label">All bookings</span><strong class="stat-number"><?= (int)$totals['bookings'] ?></strong></article><article class="stat-card"><span class="stat-label">Confirmed bookings</span><strong class="stat-number"><?= (int)$totals['confirmed'] ?></strong></article><article class="stat-card"><span class="stat-label">Confirmed revenue</span><strong class="stat-number">R<?= number_format((float)$totals['revenue'],2) ?></strong></article></div>
<h2>Monthly performance</h2><div class="table-wrapper"><table><thead><tr><th>Month</th><th>Bookings</th><th>Confirmed</th><th>Revenue</th></tr></thead><tbody>
<?php foreach ($months as $month): ?><tr><td><?= h(date('F Y', strtotime($month['month_key'].'-01'))) ?></td><td><?= (int)$month['booking_count'] ?></td><td><?= (int)$month['confirmed_count'] ?></td><td>R<?= number_format((float)$month['revenue'],2) ?></td></tr><?php endforeach; ?>
<?php if (!$months): ?><tr><td colspan="4">No report data yet.</td></tr><?php endif; ?>
</tbody></table></div></main></body></html>