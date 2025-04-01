<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

require_once 'db.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    
    // Handle image upload
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/events/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['event_image']['tmp_name'], $uploadPath)) {
            // Insert event into database
            $stmt = $pdo->prepare("INSERT INTO events (title, description, image_path, event_date, location, price) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $description, $uploadPath, $event_date, $location, $price])) {
                $message = "Event added successfully!";
            } else {
                $message = "Error adding event to database.";
            }
        } else {
            $message = "Error uploading image.";
        }
    } else {
        $message = "Please select an image for the event.";
    }
}

// Fetch all events
$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .event-image-preview {
            max-width: 200px;
            max-height: 200px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Manage Events</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <h2>Add New Event</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="event_image" class="form-label">Event Image</label>
                        <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*" required>
                        <div class="mt-2">
                            <img id="imagePreview" src="#" alt="Image Preview" class="event-image-preview d-none">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="event_date" class="form-label">Event Date & Time</label>
                        <input type="datetime-local" class="form-control" id="event_date" name="event_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Ticket Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Event</button>
                </form>
            </div>
            
            <div class="col-md-6">
                <h2>Existing Events</h2>
                <div class="list-group">
                    <?php foreach ($events as $event): ?>
                        <div class="list-group-item mb-3">
                            <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                            <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="img-fluid mb-2" style="max-height: 100px;">
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                            <small class="text-muted">
                                <?php echo date('M j, Y g:i A', strtotime($event['event_date'])); ?> | 
                                <?php echo htmlspecialchars($event['location']); ?> | 
                                $<?php echo number_format($event['price'], 2); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('event_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>