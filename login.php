<?php
// Set session configurations FIRST
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable if using HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Start session before any output
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hardcoded credentials (CHANGE THESE VALUES)
define('ADMIN_USER', 'ClickM+*1270');
define('ADMIN_PASS_HASH', password_hash('+*7862Male!!', PASSWORD_BCRYPT));

// Redirect if already logged in
if(isset($_SESSION['admin_authenticated'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle login attempt
$error = '';
$attempt_delay = 2; // Seconds delay on failed attempts

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Rate limiting check
    if(isset($_SESSION['last_attempt'])) {
        $elapsed = time() - $_SESSION['last_attempt'];
        if($elapsed < $attempt_delay) {
            sleep($attempt_delay - $elapsed);
        }
    }

    // CSRF validation (fixed missing parenthesis)
    if(!isset($_POST['csrf_token'])) {
        $error = "Security token missing. Please refresh the page.";
    } elseif($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Credential validation
        if($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            
            // Clear CSRF token
            unset($_SESSION['csrf_token']);
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials";
            $_SESSION['last_attempt'] = time();
        }
    }
}

// Generate new CSRF token for each form
if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php include 'header.php'; ?>

<main class="login-page">
    <style>
        :root {
            --primary: #7C3AED;
            --primary-dark: #6D28D9;
            --text: #F8FAFC;
            --background: #0F172A;
            --glass: rgba(15, 23, 42, 0.6);
            --accent: #A855F7;
        }

        .login-page {
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text);
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.95) 0%,
                rgba(31, 41, 55, 0.9) 100%),
                url('assets/images/login-bg.jpg') center/cover;
            padding: 2rem;
        }

        .login-form {
            background: var(--glass);
            backdrop-filter: blur(12px);
            padding: 3rem 2.5rem;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 440px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .login-form:hover {
            transform: translateY(-4px);
        }

        .form-title {
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .input-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text);
            opacity: 0.6;
            font-size: 1.1rem;
        }

        .form-input {
            width: 100%;
            padding: 1.1rem 1rem 1.1rem 3rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.75rem;
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: var(--text);
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
        }

        .error-message {
            color: #FF4747;
            background: rgba(255, 71, 71, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 71, 71, 0.2);
            display: <?= $error ? 'flex' : 'none' ?>;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .error-message i {
            font-size: 1.2rem;
        }

        @media (max-width: 480px) {
            .login-form {
                padding: 2rem 1.5rem;
                border-radius: 1rem;
            }

            .form-title {
                font-size: 1.8rem;
            }

            .form-input {
                padding: 1rem 1rem 1rem 2.8rem;
                font-size: 0.95rem;
            }
        }
    </style>

    <div class="login-container">
        <form class="login-form" method="POST" action="login.php">
            <h2 class="form-title">Admin Portal</h2>
            
            <?php if($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES) ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">

            <div class="input-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" 
                       name="username" 
                       class="form-input" 
                       placeholder="Username" 
                       required
                       autocomplete="username"
                       autocapitalize="none"
                       spellcheck="false">
            </div>

            <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" 
                       name="password" 
                       class="form-input" 
                       placeholder="Password" 
                       required
                       autocomplete="current-password"
                       minlength="12"
                       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{12,}$"
                       title="Must contain: 12+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special character">
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-sign-in-alt"></i>
                Access Dashboard
            </button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
    // Enhanced client-side validation
    document.querySelector('.login-form').addEventListener('submit', function(e) {
        const form = e.target;
        const inputs = form.querySelectorAll('.form-input');
        let valid = true;

        // Reset error states
        inputs.forEach(input => input.style.borderColor = 'rgba(255, 255, 255, 0.15)');

        // Check empty fields
        inputs.forEach(input => {
            if(!input.value.trim()) {
                valid = false;
                input.style.borderColor = '#FF4747';
            }
        });

        // Password complexity check
        const password = form.querySelector('[name="password"]');
        if(password && !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{12,}$/.test(password.value)) {
            valid = false;
            password.style.borderColor = '#FF4747';
        }

        if(!valid) {
            e.preventDefault();
            const errorDiv = form.querySelector('.error-message');
            errorDiv.textContent = 'Please check your inputs';
            errorDiv.style.display = 'flex';
        }
    });
</script>