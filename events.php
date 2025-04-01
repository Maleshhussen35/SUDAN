<?php
session_start();
require_once 'db.php';

// Handle ticket purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_ticket'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=events.php');
        exit();
    }
    
    $event_id = $_POST['event_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    
    // Get event price
    $stmt = $pdo->prepare("SELECT price FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        $total_price = $event['price'] * $quantity;
        
        // Insert ticket purchase
        $stmt = $pdo->prepare("INSERT INTO tickets (event_id, user_id, quantity, total_price) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$event_id, $user_id, $quantity, $total_price])) {
            $purchase_message = "Ticket purchased successfully!";
        } else {
            $purchase_message = "Error purchasing ticket.";
        }
    }
}

// Fetch all events
$events = $pdo->query("SELECT * FROM events WHERE event_date > NOW() ORDER BY event_date ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .event-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .event-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5">Upcoming Events</h1>
        
        <?php if (isset($purchase_message)): ?>
            <div class="alert alert-success"><?php echo $purchase_message; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-4">
                    <div class="card event-card">
                        <img src="<?php echo htmlspecialchars($event['image_path']); ?>" class="card-img-top event-image" alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item">
                                    <i class="bi bi-calendar-event"></i> 
                                    <?php echo date('M j, Y g:i A', strtotime($event['event_date'])); ?>
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-tag"></i> 
                                    $<?php echo number_format($event['price'], 2); ?>
                                </li>
                            </ul>
                            
                            <!-- Ticket Purchase Form -->
                            <form method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <div class="mb-3">
                                    <label for="quantity_<?php echo $event['id']; ?>" class="form-label">Quantity</label>
                                    <select class="form-select" id="quantity_<?php echo $event['id']; ?>" name="quantity" required>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                <button type="submit" name="purchase_ticket" class="btn btn-primary w-100">
                                    Buy Ticket
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($events)): ?>
            <div class="alert alert-info text-center">
                No upcoming events at the moment. Please check back later!
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>