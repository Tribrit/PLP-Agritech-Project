<?php
session_start();
// No automatic redirect - let users choose their path
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Connect Farmers & Buyers</title>
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
            line-height: 1.6;
            color: var(--dark-gray);
            background-color: var(--white);
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            color: var(--white);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            padding: 0 20px;
            position: relative;
        }
        
        .video-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--white);
        }
        
        .logo span {
            color: var(--light-green);
        }
        
        nav {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: var(--light-green);
        }
        
        .header-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .header-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            background: var(--accent-orange);
            color: var(--white);
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: var(--dark-green);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        /* Login Section */
        .login-section {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            margin: 30px auto 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .login-section h2 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--dark-green);
        }
        
        .login-options {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .login-box {
            flex: 1;
            min-width: 300px;
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .login-box:hover {
            transform: translateY(-5px);
        }
        
        .login-box h3 {
            color: var(--primary-green);
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.5rem;
        }
        
        .login-box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .login-box button {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-green);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-box button:hover {
            background-color: var(--dark-green);
        }
        
        .login-box p {
            text-align: center;
            margin-top: 15px;
        }
        
        .login-box a {
            color: var(--accent-orange);
            text-decoration: none;
        }
        
        .login-box a:hover {
            text-decoration: underline;
        }
        
        /* Features Section */
        .features {
            padding: 80px 20px;
            background-color: var(--light-gray);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--dark-green);
            margin-bottom: 15px;
        }
        
        .section-title p {
            max-width: 700px;
            margin: 0 auto;
            color: var(--dark-gray);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-green);
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            color: var(--dark-green);
        }
        
        /* How It Works */
        .how-it-works {
            padding: 80px 20px;
            background-color: var(--white);
        }
        
        .steps {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .step {
            flex: 1;
            min-width: 250px;
            text-align: center;
            position: relative;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background-color: var(--accent-orange);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 25px;
            right: -15px;
            width: 30px;
            height: 2px;
            background-color: var(--primary-green);
        }
        
        .step h3 {
            margin-bottom: 15px;
            color: var(--dark-green);
        }
        
        /* Testimonials */
        .testimonials {
            padding: 80px 20px;
            background-color: var(--light-gray);
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .testimonial-card {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        
        .author-info h4 {
            color: var(--dark-green);
            margin-bottom: 5px;
        }
        
        .author-info p {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
        
        /* Stats Section */
        .stats {
            padding: 60px 20px;
            background: linear-gradient(rgba(46, 139, 87, 0.9), rgba(46, 139, 87, 0.9)), url('https://images.unsplash.com/photo-1464226184884-fa280b87c399?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            color: var(--white);
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .stat-item p {
            font-size: 1.2rem;
        }
        
        /* Call to Action */
        .cta {
            padding: 80px 20px;
            background-color: var(--white);
            text-align: center;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            color: var(--dark-green);
            margin-bottom: 20px;
        }
        
        .cta p {
            margin-bottom: 30px;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark-green);
            color: var(--white);
            padding: 60px 20px 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-column h3 {
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: var(--lighter-green);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-column ul li a:hover {
            color: var(--white);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-links a {
            color: var(--white);
            font-size: 1.5rem;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: var(--light-green);
        }
        
        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content h1 {
                font-size: 2.5rem;
            }
            
            .login-options {
                flex-direction: column;
            }
            
            .login-box {
                width: 100%;
            }
            
            nav ul {
                display: none;
            }
            
            .step:not(:last-child)::after {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .header-content h1 {
                font-size: 2rem;
            }
            
            .header-content p {
                font-size: 1rem;
            }
            
            .btn {
                padding: 10px 20px;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section with Video Background -->
    <header>
        <!-- Optional: Uncomment to use video background -->
       <video autoplay muted loop class="video-bg">
            <source src="farm-field.mp4" type="video/mp4">
        </video> 
        
        <div class="logo">Agri<span>Market</span></div>
        
        <nav>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
        
        <div class="header-content">
            <h1>Empowering Farmers, Connecting Markets</h1>
            <p>AgriMarket bridges the gap between small-scale farmers and buyers with real-time market data and direct connections</p>
            <a href="#login" class="btn">Get Started</a>
        </div>
    </header>
    
    <!-- Login Section -->
    <section id="login" class="login-section">
        <h2>Join Our Agricultural Network</h2>
        <div class="login-options">
            <!-- Farmer Section -->
            <div class="login-box">
                <h3>Farmer Login</h3>
                <form action="login.php?type=farmer" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <p>New farmer? <a href="register.php?type=farmer">Register here</a></p>
            </div>
            
            <!-- Buyer Section -->
            <div class="login-box">
                <h3>Buyer Login</h3>
                <form action="login.php?type=buyer" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <p>New buyer? <a href="register.php?type=buyer">Register here</a></p>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="features">
        <div class="section-title">
            <h2>Why Choose AgriMarket?</h2>
            <p>Our platform provides comprehensive solutions for farmers and buyers to connect and thrive in the agricultural market</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Real-Time Market Data</h3>
                <p>Access up-to-date pricing information from local markets to make informed selling decisions.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Direct Connections</h3>
                <p>Connect directly with verified buyers and farmers without middlemen taking your profits.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Smart Alerts</h3>
                <p>Get notifications when prices change or when buyers are looking for your products.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Location Services</h3>
                <p>Find the nearest markets and buyers based on your current location.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Access our platform from any device, even with limited internet connectivity.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure Transactions</h3>
                <p>Our verification system ensures safe and reliable transactions for all users.</p>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="section-title">
            <h2>How AgriMarket Works</h2>
            <p>Simple steps to connect farmers with the right buyers and markets</p>
        </div>
        
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Create Your Profile</h3>
                <p>Register as a farmer or buyer and set up your profile with relevant details.</p>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <h3>List Your Products</h3>
                <p>Farmers can list available produce, buyers can specify what they're looking for.</p>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <h3>Find Matches</h3>
                <p>Our algorithm connects you with the most suitable partners based on your needs.</p>
            </div>
            
            <div class="step">
                <div class="step-number">4</div>
                <h3>Connect & Transact</h3>
                <p>Communicate directly and arrange transactions that work for both parties.</p>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials">
        <div class="section-title">
            <h2>Success Stories</h2>
            <p>Hear from farmers and buyers who have transformed their businesses with AgriMarket</p>
        </div>
        
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <div class="testimonial-text">
                    "AgriMarket helped me find reliable buyers for my organic vegetables at fair prices. My income has increased by 40% since I started using the platform."
                </div>
                <div class="testimonial-author">
                    <img src="image.png" alt="Britney Trizer." class="author-img">
                    <div class="author-info">
                        <h4>Britney Trizer</h4>
                        <p>Small-scale Farmer</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-text">
                    "As a restaurant owner, finding fresh local produce was always challenging. Now with AgriMarket, I connect directly with farmers and get the best quality ingredients."
                </div>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="James T." class="author-img">
                    <div class="author-info">
                        <h4>James T.</h4>
                        <p>Restaurant Owner</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-text">
                    "The market price alerts have been game-changing for our cooperative. We now know exactly when and where to sell for maximum profit."
                </div>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Amina B." class="author-img">
                    <div class="author-info">
                        <h4>Amina B.</h4>
                        <p>Farm Cooperative Leader</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>5,000+</h3>
                <p>Farmers Connected</p>
            </div>
            
            <div class="stat-item">
                <h3>1,200+</h3>
                <p>Verified Buyers</p>
            </div>
            
            <div class="stat-item">
                <h3>85%</h3>
                <p>Increased Farmer Income</p>
            </div>
            
            <div class="stat-item">
                <h3>24/7</h3>
                <p>Market Access</p>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="cta">
        <div class="cta-content">
            <h2>Ready to Transform Your Agricultural Business?</h2>
            <p>Join thousands of farmers and buyers who are already benefiting from direct connections and real-time market data.</p>
            <a href="#login" class="btn">Get Started Today</a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-column">
                <h3>About AgriMarket</h3>
                <p>Bridging the gap between small-scale farmers and buyers through technology and innovation.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#login">Login</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Resources</h3>
                <ul>
                    <li><a href="#">Farmer Guides</a></li>
                    <li><a href="#">Market Trends</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contact Us</h3>
                <ul>
                    <li><i class="fas fa-envelope"></i> info@agrimarket.com</li>
                    <li><i class="fas fa-phone"></i> +1 (234) 567-8900</li>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Farm Lane, Agriculture City</li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; 2023 AgriMarket. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>