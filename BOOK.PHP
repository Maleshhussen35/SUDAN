<?php
session_start();
// Database configuration
require_once 'db.php'; // Include database connection
$whatsapp_number = "+254757919189";
$page_title = "Event Booking System";

// Process form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $event = filter_input(INPUT_POST, 'event', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $guests = filter_input(INPUT_POST, 'guests', FILTER_SANITIZE_NUMBER_INT);
    $food = filter_input(INPUT_POST, 'food', FILTER_SANITIZE_STRING);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $payment_details = filter_input(INPUT_POST, 'payment_details', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($event) || empty($date) || empty($guests) || empty($food) || empty($payment_method) || empty($payment_details)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $error = "Invalid phone number!";
    } elseif (!is_numeric($guests) || $guests <= 0) {
        $error = "Please enter a valid number of guests!";
    } else {
        try {
            // Store in database
            $stmt = $pdo->prepare("INSERT INTO bookings (name, email, phone, event_type, event_date, guests, food_type, payment_method, payment_details) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $event, $date, $guests, $food, $payment_method, $payment_details]);
            
            // Format WhatsApp message
            $message = "New Booking Request:%0A%0A";
            $message .= "*Name:* " . urlencode($name) . "%0A";
            $message .= "*Email:* " . urlencode($email) . "%0A";
            $message .= "*Phone:* " . urlencode($phone) . "%0A";
            $message .= "*Event Type:* " . urlencode(ucfirst($event)) . "%0A";
            $message .= "*Date:* " . urlencode(date('F j, Y', strtotime($date))) . "%0A";
            $message .= "*Expected Guests:* " . urlencode($guests) . "%0A";
            $message .= "*Food Type:* " . urlencode($food) . "%0A";
            $message .= "*Payment Method:* " . urlencode($payment_method) . "%0A";
            $message .= "*Payment Details:* " . urlencode($payment_details);

            // Redirect to WhatsApp
            header("Location: https://wa.me/{$whatsapp_number}?text={$message}");
            exit();
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0F172A;
            --secondary: #1E293B;
            --accent: #7C3AED;
            --accent-light: #8B5CF6;
            --text: #F8FAFC;
            --text-light: #E2E8F0;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.1);
            --success: #10B981;
            --error: #EF4444;
            --warning: #F59E0B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--primary);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: radial-gradient(circle at 10% 20%, rgba(28, 25, 51, 0.8) 0%, rgba(15, 23, 42, 1) 90%);
        }

        .booking-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: var(--glass);
            backdrop-filter: blur(12px);
            border-radius: 1.5rem;
            border: 1px solid var(--glass-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .booking-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .booking-container h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: var(--text);
            text-align: center;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .booking-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--accent);
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--glass-border);
            border-radius: 0.75rem;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
            background: rgba(15, 23, 42, 0.9);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23E2E8F0' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }

        .error-alert {
            padding: 1.2rem;
            background: rgba(239, 68, 68, 0.15);
            color: #FECACA;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(239, 68, 68, 0.3);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .error-alert::before {
            content: '!';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: var(--error);
            color: white;
            border-radius: 50%;
            font-weight: bold;
            flex-shrink: 0;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: white;
            padding: 1.2rem 2rem;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }

        .submit-btn:active {
            transform: translateY(1px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .booking-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .booking-container h2 {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 0.8rem 1rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .booking-container {
            animation: fadeIn 0.5s ease-out;
        }

        .form-group {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }
        .form-group:nth-child(7) { animation-delay: 0.7s; }
        .form-group:nth-child(8) { animation-delay: 0.8s; }
        .form-group:nth-child(9) { animation-delay: 0.9s; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="booking-container">
        <h2>Event Booking Form</h2>
        
        <?php if($error): ?>
            <div class="error-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required
                       placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required
                       placeholder="Enter your email address">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required
                       placeholder="Format: +254XXXXXXXXX">
            </div>

            <div class="form-group">
                <label for="event">Select Event</label>
                <select id="event" name="event" class="form-control" required>
                    <option value="">Choose Event Type</option>
                    <option value="wedding" <?= ($_POST['event'] ?? '') === 'wedding' ? 'selected' : '' ?>>Wedding</option>
                    <option value="conference" <?= ($_POST['event'] ?? '') === 'conference' ? 'selected' : '' ?>>Conference</option>
                    <option value="party" <?= ($_POST['event'] ?? '') === 'party' ? 'selected' : '' ?>>Party</option>
                    <option value="corporate">Corporate Event</option>
                    <option value="birthday">Birthday Celebration</option>
                </select>
            </div>

            <div class="form-group">
                <label for="date">Booking Date</label>
                <input type="date" id="date" name="date" class="form-control"
                       value="<?= htmlspecialchars($_POST['date'] ?? '') ?>" required
                       min="<?= date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="guests">Expected Guests</label>
                <input type="number" id="guests" name="guests" class="form-control"
                       value="<?= htmlspecialchars($_POST['guests'] ?? '') ?>" required min="1"
                       placeholder="Number of attendees">
            </div>

            <div class="form-group">
                <label for="food">Type of Food</label>
                <select id="food" name="food" class="form-control" required>
                    <option value="">Select Food Option</option>
                    <option value="buffet" <?= ($_POST['food'] ?? '') === 'buffet' ? 'selected' : '' ?>>Buffet</option>
                    <option value="set_menu" <?= ($_POST['food'] ?? '') === 'set_menu' ? 'selected' : '' ?>>Set Menu</option>
                    <option value="finger_food" <?= ($_POST['food'] ?? '') === 'finger_food' ? 'selected' : '' ?>>Finger Food</option>
                    <option value="plated">Plated Dinner</option>
                    <option value="custom">Custom Menu</option>
                </select>
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" class="form-control" required>
                    <option value="">Select Payment Method</option>
                    <option value="till_number" <?= ($_POST['payment_method'] ?? '') === 'till_number' ? 'selected' : '' ?>>Till Number</option>
                    <option value="paypal" <?= ($_POST['payment_method'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                    <option value="equity_bank" <?= ($_POST['payment_method'] ?? '') === 'equity_bank' ? 'selected' : '' ?>>Equity Bank Account</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="credit_card">Credit Card</option>
                </select>
            </div>

            <div class="form-group">
                <label for="payment_details">Payment Details</label>
                <input type="text" id="payment_details" name="payment_details" class="form-control"
                       value="<?= htmlspecialchars($_POST['payment_details'] ?? '') ?>" required
                       placeholder="e.g., Till number 123456">
            </div>

            <button type="submit" class="submit-btn">Complete Booking</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
