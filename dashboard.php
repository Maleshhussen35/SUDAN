<?php

// [Keep all the existing PHP security code at the top]
// Start session securely
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Validate authentication
if (!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) {
    header("Location: login.php");
    exit();
}

// Validate session security parameters
if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || 
    $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} else {
    $interval = 60 * 15; // 15 minutes
    if (time() - $_SESSION['last_regeneration'] > $interval) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <?php include 'header.php'; ?>
    <style>
    /* variables.css */
:root {
  --primary: #7C3AED;
  --primary-light: rgba(124, 58, 237, 0.15);
  --primary-dark: #5B21B6;
  --danger: #FF4747;
  --success: #10B981;
  --dark-1: #0F172A;
  --dark-2: #1E293B;
  --light-1: #F8FAFC;
  --light-2: rgba(248, 250, 252, 0.85);
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  --rounded-sm: 0.125rem;
  --rounded-md: 0.375rem;
  --rounded-lg: 0.5rem;
  --rounded-xl: 0.75rem;
  --rounded-2xl: 1rem;
  --rounded-3xl: 1.5rem;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* base.css */
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: var(--dark-1);
  color: var(--light-1);
  line-height: 1.5;
}

h1, h2, h3, h4 {
  font-weight: 600;
  line-height: 1.25;
}

a {
  color: inherit;
  text-decoration: none;
  transition: var(--transition);
}

/* components/dashboard.css */
.dashboard-container {
  max-width: 1400px;
  margin: 3rem auto;
  padding: 2.5rem;
  background: linear-gradient(160deg, 
    rgba(15, 23, 42, 0.95) 0%,
    rgba(30, 41, 59, 0.98) 100%);
  border-radius: var(--rounded-3xl);
  box-shadow: var(--shadow-2xl);
}

.dashboard-header {
  margin-bottom: 3rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2.5rem;
}

.dashboard-card {
  background: linear-gradient(145deg,
    rgba(255, 255, 255, 0.1) 0%,
    rgba(255, 255, 255, 0.08) 100%);
  border: 2px solid rgba(124, 58, 237, 0.3);
  border-radius: var(--rounded-2xl);
  padding: 2.5rem 2rem;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.dashboard-card::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 50% 50%,
    rgba(124, 58, 237, 0.15) 0%,
    transparent 70%);
  pointer-events: none;
  transition: opacity 0.4s ease;
  opacity: 0;
}

.dashboard-card:hover {
  transform: translateY(-8px) scale(1.02);
  border-color: rgba(124, 58, 237, 0.6);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
}

.dashboard-card:hover::before {
  opacity: 1;
}

.dashboard-card__icon {
  font-size: 3rem;
  margin-bottom: 1.5rem;
  color: var(--primary);
  text-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
  transition: transform 0.3s ease;
}

.dashboard-card:hover .dashboard-card__icon {
  transform: scale(1.15);
}

.dashboard-card__title {
  color: var(--light-1);
  font-size: 1.3rem;
  font-weight: 600;
  letter-spacing: -0.5px;
  display: block;
  padding: 1.2rem 0;
  position: relative;
  z-index: 2;
}

.dashboard-card__description {
  color: var(--light-2);
  font-size: 1rem;
  line-height: 1.6;
  margin-top: 1rem;
  font-weight: 400;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: var(--rounded-lg);
  font-weight: 600;
  transition: var(--transition);
  border: 2px solid transparent;
}

.btn--logout {
  margin-top: 4rem;
  padding: 1.2rem 2.5rem;
  background: rgba(255, 71, 71, 0.15);
  color: #FF6B6B;
  border-color: rgba(255, 107, 107, 0.3);
}

.btn--logout:hover {
  background: rgba(255, 71, 71, 0.25);
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(255, 71, 71, 0.15);
  color: var(--danger);
}

.status-indicator {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 12px;
  height: 12px;
  background: var(--success);
  border-radius: 50%;
  box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
}

/* utilities.css */
.mt-1 { margin-top: 0.25rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mt-6 { margin-top: 1.5rem; }
.mt-8 { margin-top: 2rem; }

.text-center { text-align: center; }
</style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Welcome to Admin Dashboard</h1>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <i class="fas fa-calendar-alt"></i>
                <a href="manage_events.php">Manage Events</a>
                <p>Create, update, or delete upcoming events</p>
            </div>

            <div class="dashboard-card">
                <i class="fas fa-book"></i>
                <a href="view_booking.php">View Bookings</a>
                <p>Review and manage event reservations</p>
            </div>

            
        <div class="logout-container">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>