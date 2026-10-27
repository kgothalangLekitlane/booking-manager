<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$rawId = $_GET['id'] ?? $_POST['id'] ?? '';
if (!is_string($rawId) || !ctype_digit($rawId) || (int) $rawId < 1) {
    http_response_code(400);
    exit('Please provide a valid booking ID.');
}

$bookingId = (int) $rawId;
$errors = [];
$customerName = '';
$email = '';
$bookingDate = '';
$status = 'Pending';

$findStmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id');
$findStmt->execute(['id' => $bookingId]);
$existingBooking = $findStmt->fetch(PDO::FETCH_ASSOC);

if ($existingBooking === false) {
    http_response_code(404);
    exit('Booking not found.');
}

$customerName = $existingBooking['customer_name'];
$email = $existingBooking['email'];
$bookingDate = $existingBooking['booking_date'];
$status = $existingBooking['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = is_string($_POST['customer_name'] ?? null) ? trim($_POST['customer_name']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $bookingDate = is_string($_POST['booking_date'] ?? null) ? trim($_POST['booking_date']) : '';
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
    $csrfToken = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Your form session expired. Refresh the page and try again.';
    }

    if ($customerName === '') {
        $errors[] = 'Customer name is required.';
    } elseif (mb_strlen($customerName) > 100) {
        $errors[] = 'Customer name must be 100 characters or fewer.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Enter a valid email address (maximum 150 characters).';
    }

    $dateParts = explode('-', $bookingDate);
    if (
        count($dateParts) !== 3 ||
        !ctype_digit($dateParts[0]) ||
        !ctype_digit($dateParts[1]) ||
        !ctype_digit($dateParts[2]) ||
        !checkdate((int) $dateParts[1], (int) $dateParts[2], (int) $dateParts[0])
    ) {
        $errors[] = 'Enter a valid booking date.';
    }

    if (!in_array($status, ['Pending', 'Confirmed', 'Cancelled'], true)) {
        $errors[] = 'Choose a valid booking status.';
    }

    if ($errors === []) {
        try {
            $sql = 'UPDATE bookings
                    SET customer_name = :customer_name,
                        email = :email,
                        booking_date = :booking_date,
                        status = :status
                    WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'customer_name' => $customerName,
                'email' => $email,
                'booking_date' => $bookingDate,
                'status' => $status,
                'id' => $bookingId,
            ]);

            $_SESSION['flash_message'] = 'Booking updated successfully.';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'We could not update the booking. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking | Booking Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Edit booking #<?= $bookingId ?></h1>
        <p>Update the customer's details, booking date, or status.</p>

        <?php if ($errors !== []): ?>
            <div class="message message-error" role="alert">
                <p>Please fix the following:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="edit-booking.php">
            <input type="hidden" name="id" value="<?= $bookingId ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <label for="customer_name">Customer name</label>
            <input type="text" id="customer_name" name="customer_name" maxlength="100"
                value="<?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" maxlength="150"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="booking_date">Booking date</label>
            <input type="date" id="booking_date" name="booking_date"
                value="<?= htmlspecialchars($bookingDate, ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="status">Booking status</label>
            <select id="status" name="status" required>
                <?php foreach (['Pending', 'Confirmed', 'Cancelled'] as $option): ?>
                    <option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>>
                        <?= $option ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="form-actions">
                <button type="submit">Save changes</button>
                <a href="index.php">Cancel and return to bookings</a>
            </div>
        </form>
    </main>
</body>
</html>
