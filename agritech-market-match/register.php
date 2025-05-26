<?php
include('includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $userType = $_POST['user_type'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, phone, location, password, user_type) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $phone, $location, $password, $userType]);
        
        header("Location: login.php?registered=1&type=$userType");
        exit();
    } catch (PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}

$userType = $_GET['type'] ?? 'farmer'; // Default to farmer if not specified
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AgriMarket</title>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
        
        .registration-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .registration-header {
            background-color: var(--primary-green);
            color: var(--white);
            padding: 20px;
            text-align: center;
        }
        
        .registration-header h1 {
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
        
        .registration-form {
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
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background-color: #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            background-color: #ff5252;
            transition: all 0.3s;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .registration-container {
                margin: 20px;
            }
            
            .registration-header h1 {
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
            
            .registration-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Agri<span>Market</span></div>
        <h1>Join Our Agricultural Network</h1>
    </header>
    
    <div class="container">
        <div class="registration-container">
            <div class="registration-header">
                <h1><?php echo ucfirst($userType); ?> Registration</h1>
                <div class="user-type-tabs">
                    <a href="register.php?type=farmer" class="user-type-tab <?php echo $userType === 'farmer' ? 'active' : ''; ?>">
                        <i class="fas fa-tractor"></i> Farmer
                    </a>
                    <a href="register.php?type=buyer" class="user-type-tab <?php echo $userType === 'buyer' ? 'active' : ''; ?>">
                        <i class="fas fa-store"></i> Buyer
                    </a>
                </div>
            </div>
            
            <div class="registration-form">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="user_type" value="<?php echo $userType; ?>">
                    
                    <div class="form-group input-icon">
                        <i class="fas fa-user"></i>
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group input-icon">
                        <i class="fas fa-envelope"></i>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required>
                    </div>
                    
                    <div class="form-group input-icon">
                        <i class="fas fa-phone"></i>
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your phone number">
                    </div>
                    
                    <div class="form-group input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <label for="location">Location (Town/Village)</label>
                        <input type="text" id="location" name="location" class="form-control" placeholder="Enter your location" required>
                    </div>
                    
                    <div class="form-group input-icon">
                        <i class="fas fa-lock"></i>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                        <div class="password-strength">
                            <div class="strength-meter" id="strength-meter"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            Register as <?php echo $userType; ?>
                        </button>
                    </div>
                </form>
                
                <div class="form-footer">
                    <p>Already have an account? <a href="login.php?type=<?php echo $userType; ?>">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strength-meter');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check for length
            if (password.length >= 8) strength += 1;
            
            // Check for uppercase letters
            if (password.match(/[A-Z]/)) strength += 1;
            
            // Check for numbers
            if (password.match(/[0-9]/)) strength += 1;
            
            // Check for special characters
            if (password.match(/[^A-Za-z0-9]/)) strength += 1;
            
            // Update strength meter
            switch(strength) {
                case 0:
                    strengthMeter.style.width = '0%';
                    strengthMeter.style.backgroundColor = '#ff5252';
                    break;
                case 1:
                    strengthMeter.style.width = '25%';
                    strengthMeter.style.backgroundColor = '#ff5252';
                    break;
                case 2:
                    strengthMeter.style.width = '50%';
                    strengthMeter.style.backgroundColor = '#ffab40';
                    break;
                case 3:
                    strengthMeter.style.width = '75%';
                    strengthMeter.style.backgroundColor = '#ffab40';
                    break;
                case 4:
                    strengthMeter.style.width = '100%';
                    strengthMeter.style.backgroundColor = '#4caf50';
                    break;
            }
        });
    </script>
</body>
</html>