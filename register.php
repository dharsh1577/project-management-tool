<?php
require_once "includes/config.php";
require_once "includes/auth.php";

if (isLoggedIn()) {
    redirect("dashboard.php");
}

$auth = new Auth($db);
$error = '';
$success = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        if ($auth->register($username, $email, $password)) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Username or email already exists!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Project Management Tool</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 450px;
            animation: slideUp 0.6s ease;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .register-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .register-subtitle {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .input-with-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.2rem;
        }

        .input-with-icon .form-control {
            padding-left: 45px;
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(39, 174, 96, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(39, 174, 96, 0.4);
        }

        .register-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e1e8ed;
        }

        .login-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link:hover {
            color: #764ba2;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .register-card {
                margin: 1rem;
                padding: 2rem;
            }
            
            .register-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo-container">
                    <img src="assets/logo.png" alt="Project Manager Logo" 
                          style="height: 55px; width: auto; background: linear-gradient(135deg, #667eea, #764ba2); border: 2px solid #667eea; border-radius: 50%; padding: 8px; box-shadow: 0 0 20px rgba(102, 126, 234, 0.4);">
                    <div>
                        <h1 class="register-title">Project Manager</h1>
                        <p class="register-subtitle">Create your account to get started</p>
                    </div>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="animation: slideUp 0.4s ease;">
                    <i class='bx bx-error-circle'></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="animation: slideUp 0.4s ease;">
                    <i class='bx bx-check-circle'></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class='bx bx-user'></i>
                        Username
                    </label>
                    <div class="input-with-icon">
                        <i class='bx bx-user input-icon'></i>
                        <input type="text" class="form-control" id="username" name="username" required 
                               placeholder="Choose a username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class='bx bx-envelope'></i>
                        Email Address
                    </label>
                    <div class="input-with-icon">
                        <i class='bx bx-envelope input-icon'></i>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="Enter your email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class='bx bx-lock-alt'></i>
                        Password
                    </label>
                    <div class="input-with-icon">
                        <i class='bx bx-lock-alt input-icon'></i>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Create a password">
                    </div>
                    <div class="password-strength">
                        <i class='bx bx-info-circle'></i>
                        Minimum 6 characters
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class='bx bx-lock'></i>
                        Confirm Password
                    </label>
                    <div class="input-with-icon">
                        <i class='bx bx-lock input-icon'></i>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm your password">
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class='bx bx-user-plus'></i>
                    Create Account
                </button>
            </form>

            <div class="register-footer">
                <p>Already have an account? 
                    <a href="login.php" class="login-link">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Add password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.style.borderColor = '#e74c3c';
                } else {
                    confirmPassword.style.borderColor = '#27ae60';
                }
            }
            
            confirmPassword.addEventListener('input', validatePassword);
            password.addEventListener('input', validatePassword);
        });
    </script>
</body>
</html>