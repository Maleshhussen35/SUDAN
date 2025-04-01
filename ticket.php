<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=ticket.php');
    exit();
}

// Initialize variables
$event = null;
$error = '';
$success = '';
$payment_method = '';

// Get event details if event_id is provided
if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        $error = "Event not found.";
    }
} else {
    $error = "No event specified.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $event_id = intval($_POST['event_id']);
    $quantity = intval($_POST['quantity']);
    $payment_method = $_POST['payment_method'];
    $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
    
    // Validate inputs
    if ($quantity < 1 || $quantity > 5) {
        $error = "Please select between 1-5 tickets.";
    } elseif (!in_array($payment_method, ['mpesa', 'paypal'])) {
        $error = "Please select a valid payment method.";
    } elseif ($payment_method === 'mpesa' && empty($phone_number)) {
        $error = "Please enter your M-Pesa phone number.";
    } else {
        // Get event price again to prevent tampering
        $stmt = $pdo->prepare("SELECT price FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($event) {
            $total_price = $event['price'] * $quantity;
            
            // Process payment based on method
            if ($payment_method === 'mpesa') {
                // Simulate M-Pesa payment processing
                // In a real implementation, you would call the M-Pesa API here
                $transaction_id = 'MPESA' . uniqid();
                $success = "M-Pesa payment request sent to $phone_number. Complete payment to receive your tickets.";
                
                // Record the transaction
                $stmt = $pdo->prepare("INSERT INTO tickets (event_id, user_id, quantity, total_price, payment_method, transaction_id) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $event_id,
                    $_SESSION['user_id'],
                    $quantity,
                    $total_price,
                    'mpesa',
                    $transaction_id
                ]);
                
            } elseif ($payment_method === 'paypal') {
                // For PayPal, we'll redirect to PayPal payment page
                // In a real implementation, you would generate a PayPal payment URL
                $_SESSION['pending_payment'] = [
                    'event_id' => $event_id,
                    'quantity' => $quantity,
                    'total_price' => $total_price,
                    'payment_method' => 'paypal'
                ];
                
                // Redirect to PayPal or show PayPal button
                // header('Location: process_paypal.php');
                // For this example, we'll simulate success
                $transaction_id = 'PAYPAL' . uniqid();
                $stmt = $pdo->prepare("INSERT INTO tickets (event_id, user_id, quantity, total_price, payment_method, transaction_id) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $event_id,
                    $_SESSION['user_id'],
                    $quantity,
                    $total_price,
                    'paypal',
                    $transaction_id
                ]);
                $success = "PayPal payment processed successfully. Your tickets have been issued.";
            }
        } else {
            $error = "Event not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .payment-method {
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .payment-method.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
        .mpesa-details, .paypal-details {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <div class="text-center mt-4">
                        <a href="my_tickets.php" class="btn btn-primary">View Your Tickets</a>
                        <a href="events.php" class="btn btn-outline-secondary">Back to Events</a>
                    </div>
                    <?php exit(); ?>
                <?php endif; ?>
                
                <?php if ($event): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Purchase Tickets</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                </div>
                                <div class="col-md-8">
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p><i class="bi bi-calendar-event"></i> <?php echo date('M j, Y g:i A', strtotime($event['event_date'])); ?></p>
                                    <p><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                    <p class="lead">Price: <strong>$<?php echo number_format($event['price'], 2); ?></strong> per ticket</p>
                                </div>
                            </div>
                            
                            <form method="POST" id="paymentForm">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Number of Tickets</label>
                                    <select class="form-select" id="quantity" name="quantity" required>
                                        <option value="1">1 Ticket - $<?php echo number_format($event['price'], 2); ?></option>
                                        <option value="2">2 Tickets - $<?php echo number_format($event['price'] * 2, 2); ?></option>
                                        <option value="3">3 Tickets - $<?php echo number_format($event['price'] * 3, 2); ?></option>
                                        <option value="4">4 Tickets - $<?php echo number_format($event['price'] * 4, 2); ?></option>
                                        <option value="5">5 Tickets - $<?php echo number_format($event['price'] * 5, 2); ?></option>
                                    </select>
                                </div>
                                
                                <h5 class="mt-4 mb-3">Select Payment Method</h5>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="card payment-method" id="mpesaCard" onclick="selectPayment('mpesa')">
                                            <div class="card-body text-center">
                                                <i class="bi bi-phone" style="font-size: 2rem; color: #6c0;"></i>
                                                <h5 class="card-title mt-2">M-Pesa Till</h5>
                                                <p class="card-text">Pay via M-Pesa mobile money</p>
                                                <input type="radio" name="payment_method" value="mpesa" style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card payment-method" id="paypalCard" onclick="selectPayment('paypal')">
                                            <div class="card-body text-center">
                                                <i class="bi bi-paypal" style="font-size: 2rem; color: #003087;"></i>
                                                <h5 class="card-title mt-2">PayPal</h5>
                                                <p class="card-text">Pay with PayPal account</p>
                                                <input type="radio" name="payment_method" value="paypal" style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="mpesaDetails" class="mpesa-details mb-4">
                                    <h5>M-Pesa Payment Details</h5>
                                    <div class="alert alert-info">
                                        <p>You will receive a payment request on your phone. Complete the transaction to receive your tickets.</p>
                                        <p><strong>Till Number:</strong> 123456</p>
                                        <p><strong>Business Name:</strong> EVENT TICKETS LTD</p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone_number" class="form-label">M-Pesa Phone Number</label>
                                        <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="e.g., 254712345678" pattern="[0-9]{12}" required>
                                        <small class="text-muted">Format: 254712345678 (start with 254)</small>
                                    </div>
                                </div>
                                
                                <div id="paypalDetails" class="paypal-details mb-4">
                                    <h5>PayPal Payment</h5>
                                    <div class="alert alert-info">
                                        <p>You will be redirected to PayPal to complete your payment.</p>
                                    </div>
                                    <!-- In a real implementation, you would include PayPal button here -->
                                    <div id="paypal-button-container"></div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="process_payment" class="btn btn-primary btn-lg" id="payButton" disabled>
                                        Complete Payment
                                    </button>
                                    <a href="events.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No event selected. Please go back to <a href="events.php">events page</a> and select an event.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle payment method selection
        function selectPayment(method) {
            // Update UI
            document.querySelectorAll('.payment-method').forEach(card => {
                card.classList.remove('selected');
            });
            document.getElementById(method + 'Card').classList.add('selected');
            
            // Set the radio button value
            document.querySelector(`input[value="${method}"]`).checked = true;
            
            // Show the appropriate details section
            document.querySelectorAll('.mpesa-details, .paypal-details').forEach(el => {
                el.style.display = 'none';
            });
            document.getElementById(method + 'Details').style.display = 'block';
            
            // Enable the payment button
            document.getElementById('payButton').disabled = false;
        }
        
        // Update total price when quantity changes
        document.getElementById('quantity').addEventListener('change', function() {
            const pricePerTicket = <?php echo $event ? $event['price'] : 0; ?>;
            const quantity = this.value;
            const options = this.options;
            
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === quantity) {
                    options[i].text = `${quantity} Ticket${quantity > 1 ? 's' : ''} - $${(pricePerTicket * quantity).toFixed(2)}`;
                }
            }
        });
        
        // In a real implementation, you would include PayPal JS SDK here
        /*
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $event ? $event['price'] : '0.00'; ?>'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Transaction completed by ' + details.payer.name.given_name);
                    document.getElementById('paymentForm').submit();
                });
            }
        }).render('#paypal-button-container');
        */
    </script>
</body>
</html>