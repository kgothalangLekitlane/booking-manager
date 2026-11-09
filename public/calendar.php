<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$monthInput = $_GET['month'] ?? date('Y-m');
if (!is_string($monthInput) || !preg_match('/^\d{4}-\d{2}$/', $monthInput)) {
    $monthInput = date('Y-m');
}
$monthDate = DateTime::createFromFormat('!Y-m-d', $monthInput . '-01');
if (!$monthDate || $monthDate->format('Y-m') !== $monthInput) {
    $monthDate = new DateTime('first day of this month');
}
$start = $monthDate->format('Y-m-01');
$end = (clone $monthDate)->modify('first day of next month')->format('Y-m-d');
$stmt = $pdo->prepare('SELECT id, customer_name, booking_date, status FROM bookings WHERE booking_date >= :start AND booking_date < :end ORDER BY booking_date, customer_name');
$stmt->execute(['start' => $start, 'end' => $end]);
$byDate = [];
foreach ($stmt->fetchAll() as $booking) {
    $byDate[$booking['booking_date']][] = $booking;
}
$firstWeekday = (int) $monthDate->format('N');
$daysInMonth = (int) $monthDate->format('t');
$previous = (clone $monthDate)->modify('-1 month')->format('Y-m');
$next = (clone $monthDate)->modify('+1 month')->format('Y-m');
function h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Booking Calendar</title><link rel="stylesheet" href="style.css"></head>
<body><main class="container">
<nav class="top-nav"><a href="index.php">Dashboard</a><a href="customers.php">Customers</a><a href="reports.php">Reports</a></nav>
<h1>Booking calendar</h1><div class="calendar-toolbar"><a class="button-link" href="?month=<?= h($previous) ?>">← Previous</a><h2><?= h($monthDate->format('F Y')) ?></h2><a class="button-link" href="?month=<?= h($next) ?>">Next →</a></div>
<div class="calendar-grid calendar-weekdays"><?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?><strong><?= $day ?></strong><?php endforeach; ?></div>
<div class="calendar-grid">
<?php for ($blank=1; $blank<$firstWeekday; $blank++): ?><div class="calendar-day empty"></div><?php endfor; ?>
<?php for ($day=1; $day<=$daysInMonth; $day++): $date=$monthDate->format('Y-m-') . str_pad((string)$day,2,'0',STR_PAD_LEFT); ?>
<div class="calendar-day"><strong><?= $day ?></strong>
<?php foreach (($byDate[$date] ?? []) as $booking): ?><a class="calendar-booking status-<?= strtolower(h($booking['status'])) ?>" href="edit-booking.php?id=<?= (int)$booking['id'] ?>">#<?= (int)$booking['id'] ?> · <?= h($booking['customer_name']) ?></a><?php endforeach; ?>
</div><?php endfor; ?>
</div><p class="muted">Select a booking to edit it. Each date shows all bookings scheduled for that day.</p>
</main></body></html>