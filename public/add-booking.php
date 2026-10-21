<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$customerName = '';
$email = '';
$bookingDate = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

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

    if ($errors === []) {
        try {
            $sql = 'INSERT INTO bookings (customer_name, email, booking_date)
                    VALUES (:customer_name, :email, :booking_date)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'customer_name' => $customerName,
                'email' => $email,
                'booking_date' => $bookingDate,
            ]);

            $_SESSION['flash_message'] = 'Booking added successfully.';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'We could not save the booking. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Booking | Booking Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Add a booking</h1>
        <p>Enter the customer's details and choose a booking date.</p>

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

        <form method="post" action="add-booking.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="customer_name">Customer name</label>
            <input
                type="text"
                id="customer_name"
                name="customer_name"
                maxlength="100"
                value="<?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="booking_date">Booking date</label>
            <input
                type="date"
                id="booking_date"
                name="booking_date"
                value="<?= htmlspecialchars($bookingDate, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <div class="form-actions">
                <button type="submit">Save booking</button>
                <a href="index.php">Cancel and return to bookings</a>
            </div>
        </form>
    </main>
</body>
</html>
