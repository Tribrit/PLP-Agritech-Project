<?php
session_start();
include('../includes/db_connect.php');


if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'farmer' && $_SESSION['user_type'] !== 'both')) {
    header("Location: ../login.php?type=farmer");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop = $_POST['crop_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $organic = isset($_POST['organic']) ? 1 : 0;
    $premium = isset($_POST['premium']) ? 1 : 0;
    $description = $_POST['description'];

    try {
        $stmt = $pdo->prepare("INSERT INTO produce_listings (farmer_id, crop_name, quantity, price, location, organic, premium, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $crop, $quantity, $price, $location, $organic, $premium, $description]);

        header("Location: dashboard.php?posted=1");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to post produce: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Your Produce | Agritech Market Match</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Base Styles */
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --secondary-color: #FF9800;
            --secondary-dark: #F57C00;
            --accent-color: #8BC34A;
            --dark-color: #2E7D32;
            --light-color: #F1F8E9;
            --text-dark: #333;
            --text-medium: #555;
            --text-light: #777;
            --white: #fff;
            --gray-light: #f5f5f5;
            --gray-medium: #e0e0e0;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.1);
            --border-radius: 8px;
            --border-radius-sm: 4px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background-color: var(--light-color);
            line-height: 1.6;
            padding: 0;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            height: 40px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logo-text span {
            color: var(--secondary-color);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            padding: 40px 0;
            min-height: calc(100vh - 130px);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 2rem;
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: var(--text-medium);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Form Styles */
        .form-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 1.5rem;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: var(--text-medium);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-col {
            flex: 1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-label.required::after {
            content: ' *';
            color: #f44336;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-medium);
            border-radius: var(--border-radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-check-input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 0.9rem;
            color: var(--text-medium);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: inherit;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--shadow-sm);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-medium);
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-danger {
            background-color: #FFEBEE;
            color: #C62828;
            border-left: 4px solid #F44336;
        }

        .alert-success {
            background-color: #E8F5E9;
            color: #2E7D32;
            border-left: 4px solid #4CAF50;
        }

        /* Tag Styles */
        .tags-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .tag.organic {
            background-color: #E8F5E9;
            color: #2E7D32;
        }

        .tag.premium {
            background-color: #FFF3E0;
            color: #E65100;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-col {
                width: 100%;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .form-title {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
            
            .form-container {
                padding: 20px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="https://via.placeholder.com/40x40" alt="Agritech Market Match Logo">
                <span class="logo-text">Agri<span>Match</span></span>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            <div class="page-header fade-in">
                <h1 class="page-title">Sell Your Agricultural Produce</h1>
                <p class="page-subtitle">Connect directly with buyers and get the best prices for your farm products</p>
            </div>

            <div class="form-container fade-in" style="animation-delay: 0.2s">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="form-header">
                    <h2 class="form-title">Create New Listing</h2>
                    <p class="form-subtitle">Fill in the details of your produce to attract potential buyers</p>
                </div>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="crop_name" class="form-label required">Crop Name</label>
                                <select name="crop_name" id="crop_name" class="form-control" required>
                                    <option value="">Select Crop</option>
                                    <option value="Maize">Maize</option>
                                    <option value="Beans">Beans</option>
                                    <option value="Wheat">Wheat</option>
                                    <option value="Tomatoes">Tomatoes</option>
                                    <option value="Potatoes">Potatoes</option>
                                    <option value="Coffee">Coffee</option>
                                    <option value="Tea">Tea</option>
                                    <option value="Avocado">Avocado</option>
                                    <option value="Mangoes">Mangoes</option>
                                    <option value="Bananas">Bananas</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="quantity" class="form-label required">Quantity</label>
                                <input type="text" name="quantity" id="quantity" class="form-control" 
                                       placeholder="e.g., 50kg or 100 bags" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="price" class="form-label required">Price per Unit (Ksh)</label>
                                <input type="number" name="price" id="price" class="form-control" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="location" class="form-label required">Location (County)</label>
                                <select name="location" id="location" class="form-control" required>
                                    <option value="">Select County</option>
                                    <option value="Nairobi">Nairobi</option>
                                    <option value="Mombasa">Mombasa</option>
                                    <option value="Kisumu">Kisumu</option>
                                    <option value="Nakuru">Nakuru</option>
                                    <option value="Eldoret">Eldoret</option>
                                    <option value="Thika">Thika</option>
                                    <option value="Meru">Meru</option>
                                    <option value="Nyeri">Nyeri</option>
                                    <option value="Machakos">Machakos</option>
                                    <option value="Kakamega">Kakamega</option>
                                    <option value="Kisii">Kisii</option>
                                    <option value="Kericho">Kericho</option>
                                    <option value="Bungoma">Bungoma</option>
                                    <option value="Busia">Busia</option>
                                    <option value="Kiambu">Kiambu</option>
                                    <option value="Murang'a">Murang'a</option>
                                    <option value="Kirinyaga">Kirinyaga</option>
                                    <option value="Nyandarua">Nyandarua</option>
                                    <option value="Nandi">Nandi</option>
                                    <option value="Uasin Gishu">Uasin Gishu</option>
                                    <option value="Trans Nzoia">Trans Nzoia</option>
                                    <option value="West Pokot">West Pokot</option>
                                    <option value="Baringo">Baringo</option>
                                    <option value="Laikipia">Laikipia</option>
                                    <option value="Narok">Narok</option>
                                    <option value="Kajiado">Kajiado</option>
                                    <option value="Kilifi">Kilifi</option>
                                    <option value="Kwale">Kwale</option>
                                    <option value="Taita Taveta">Taita Taveta</option>
                                    <option value="Tana River">Tana River</option>
                                    <option value="Lamu">Lamu</option>
                                    <option value="Garissa">Garissa</option>
                                    <option value="Wajir">Wajir</option>
                                    <option value="Mandera">Mandera</option>
                                    <option value="Marsabit">Marsabit</option>
                                    <option value="Isiolo">Isiolo</option>
                                    <option value="Meru">Meru</option>
                                    <option value="Tharaka-Nithi">Tharaka-Nithi</option>
                                    <option value="Embu">Embu</option>
                                    <option value="Kitui">Kitui</option>
                                    <option value="Makueni">Makueni</option>
                                    <option value="Nyamira">Nyamira</option>
                                    <option value="Homa Bay">Homa Bay</option>
                                    <option value="Migori">Migori</option>
                                    <option value="Siaya">Siaya</option>
                                    <option value="Vihiga">Vihiga</option>
                                    <option value="Bomet">Bomet</option>
                                    <option value="Narok">Narok</option>
                                    <option value="Kajiado">Kajiado</option>
                                    <option value="Turkana">Turkana</option>
                                    <option value="West Pokot">West Pokot</option>
                                    <option value="Samburu">Samburu</option>
                                    <option value="Trans Nzoia">Trans Nzoia</option>
                                    <option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Product Description</label>
                        <textarea name="description" id="description" class="form-control" 
                                  placeholder="Add details about your produce (quality, packaging, harvest date, etc.)"></textarea>
                    </div>

                    <div class="tags-container">
                        <div class="form-check">
                            <input type="checkbox" name="organic" id="organic" class="form-check-input" value="1">
                            <label for="organic" class="form-check-label">Organic Certified</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="premium" id="premium" class="form-check-input" value="1">
                            <label for="premium" class="form-check-label">Premium Quality</label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Post Listing
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer" style="background-color: var(--white); padding: 20px 0; text-align: center; color: var(--text-medium); font-size: 0.9rem;">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Agritech Market Match Platform. All rights reserved.</p>
            <p>Helping farmers connect with buyers since 2023</p>
        </div>
    </footer>

    <script>
        // Simple animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            
            fadeElements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100);
            });
        });

        // Enhance select elements
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('focus', function() {
                this.style.borderColor = 'var(--primary-color)';
                this.style.boxShadow = '0 0 0 2px var(--primary-light)';
            });
            
            select.addEventListener('blur', function() {
                this.style.borderColor = 'var(--gray-medium)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>