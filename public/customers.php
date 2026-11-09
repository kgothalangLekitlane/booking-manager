<?php
require_once __DIR__ . '/../config/database.php';
$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$sql = 'SELECT email, MAX(customer_name) AS customer_name, COUNT(*) AS booking_count, MAX(booking_date) AS last_booking FROM bookings';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE customer_name LIKE :search OR email LIKE :search';
    $params['search'] = '%' . $search . '%';
}
$sql .= ' GROUP BY email ORDER BY customer_name ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
function h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Customers</title><link rel="stylesheet" href="style.css"></head>
<body><main class="container"><nav class="top-nav"><a href="index.php">Dashboard</a><a href="calendar.php">Calendar</a><a href="reports.php">Reports</a></nav>
<h1>Customer directory</h1><p>Customers are grouped by email address, with their booking activity in one place.</p>
<form method="get" class="search-form"><label for="q">Search customers</label><input id="q" name="q" value="<?= h($search) ?>" placeholder="Name or email"><div class="form-actions"><button type="submit">Search</button><a href="customers.php">Clear</a></div></form>
<div class="table-wrapper"><table><thead><tr><th>Customer</th><th>Email</th><th>Total bookings</th><th>Latest booking</th><th>Profile</th></tr></thead><tbody>
<?php foreach ($customers as $customer): ?><tr><td><?= h($customer['customer_name']) ?></td><td><?= h($customer['email']) ?></td><td><?= (int)$customer['booking_count'] ?></td><td><?= h($customer['last_booking']) ?></td><td><a class="action-link" href="customer.php?email=<?= rawurlencode($customer['email']) ?>">View history</a></td></tr><?php endforeach; ?>
<?php if (!$customers): ?><tr><td colspan="5">No customers found.</td></tr><?php endif; ?>
</tbody></table></div></main></body></html>