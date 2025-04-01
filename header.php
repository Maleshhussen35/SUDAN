
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>samurai </title>
    
    <style>
        :root {
            --primary: #0F172A;
            --secondary: #1E293B;
            --accent: #7C3AED;
            --text: #F8FAFC;
            --glass: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        .header {
            background: var(--glass);
            backdrop-filter: blur(12px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .logo-img {
            height: 40px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo-text {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #7C3AED, #A855F7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link.active {
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--primary);
                flex-direction: column;
                padding: 1rem;
            }

            .nav-links.active {
                display: flex;
            }

            .hamburger {
                display: block;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="/" class="logo">
            <img src="click2" alt="CLICK-EVENTS" class="logo-img">

                <span class="logo-text"><I><b>CLICK EVENTS</b></I></span>
            </a>

            
            <div class="nav-links">
            <a href="Home.php" class="nav-link">Home</a>
                <a href="events.php" class="nav-link">Events</a>
                <a href="gallery.php" class="nav-link">Gallery</a>
                <a href="book.php" class="nav-link">Book</a>
                <?php if(isset($_SESSION['admin'])): ?>
                    <a href="manage_evenst.php" class="nav-link">manages_event</a>
                    <a href="view_booking.php" class="nav-link">view_booking</a>
                    <a href="dashboard.php" class="nav-link">manage_gallery</a>
                     <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Script -->
    <script>
        document.querySelector('.hamburger').addEventListener('click', () => {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>
