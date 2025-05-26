<?php
session_start();
include('includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userType = $_GET['type'] ?? 'farmer'; // Default to farmer if not specified

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type IN (?, 'both')");
        $stmt->execute([$email, $userType]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_location'] = $user['location'];
            
            // Redirect to appropriate dashboard
            $dashboard = ($userType === 'buyer') ? 'buyers/dashboard.php' : 'farmers/dashboard.php';
            header("Location: $dashboard");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}

$userType = $_GET['type'] ?? 'farmer'; // Default to farmer if not specified
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgriMarket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-green: #2e8b57;
            --dark-green: #1e5631;
            --light-green: #a4de6c;
            --lighter-green: #d9f2a3;
            --accent-orange: #ff8c42;
            --white: #ffffff;
            --light-gray: #f5f5f5;
            --dark-gray: #333333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        header {
            background: linear-gradient(rgba(46, 139, 87, 0.9), rgba(46, 139, 87, 0.9)), url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            color: var(--white);
            padding: 60px 20px;
            text-align: center;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .logo span {
            color: var(--light-green);
        }
        
        .login-container {
            max-width: 500px;
            width: 100%;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background-color: var(--primary-green);
            color: var(--white);
            padding: 20px;
            text-align: center;
            position: relative;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
        }
        
        .user-type-tabs {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
        
        .user-type-tab {
            padding: 8px 20px;
            margin: 0 5px;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .user-type-tab.active {
            background-color: var(--white);
            color: var(--primary-green);
            font-weight: bold;
        }
        
        .user-type-tab:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-green);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.2);
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-green);
            color: var(--white);
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .form-footer a {
            color: var(--accent-orange);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .form-footer a:hover {
            color: var(--dark-green);
            text-decoration: underline;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-green);
        }
        
        .input-icon input {
            padding-left: 40px;
        }
        
        .forgot-password {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: var(--accent-orange);
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                margin: 20px;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .user-type-tabs {
                flex-direction: column;
            }
            
            .user-type-tab {
                margin: 5px 0;
            }
            
            .login-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Agri<span>Market</span></div>
        <h1>Welcome Back to AgriMarket</h1>
    </header>
    
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h1><?php echo ucfirst($userType); ?> Login</h1>
                <div class="user-type-tabs">
                    <a href="login.php?type=farmer" class="user-type-tab <?php echo $userType === 'farmer' ? 'active' : ''; ?>">
                        <i class="fas fa-tractor"></i> Farmer
                    </a>
                    <a href="login.php?type=buyer" class="user-type-tab <?php echo $userType === 'buyer' ? 'active' : ''; ?>">
                        <i class="fas fa-store"></i> Buyer
                    </a>
                </div>
            </div>
            
            <div class="login-form">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> Registration successful! Please login.
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group input-icon">
                        <i class="fas fa-envelope"></i>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required>
                    </div>
                    
                    <div class="form-group input-icon">
                        <i class="fas fa-lock"></i>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot password?</a>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php?type=<?php echo $userType; ?>">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>