<?php
session_start();
include "../db/config.php";

// Initialize default variables (Default path for guests or students)
$searchAction = "../../../Student/MVC/php/SearchOutput.php"; 

// Check if the user is logged in
if (isset($_SESSION['user_name'])) {
    $username = $_SESSION['user_name'];
    
    $safe_username = mysqli_real_escape_string($conn, $username);

    $sql = "SELECT role FROM users WHERE Username = '$safe_username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $role = $row['role'];

        if ($role == 'Reviewer') {
            // Reviewer Dashboard Path
            $dashboardLink = "../../../Reviewer/MVC/php/ReviewerDashboard.php"; 
            
            // ⚠️ EDIT THIS PATH: Reviewer Search Output Path
            $searchAction = "../../../Reviewer/MVC/php/SearchOutput.php"; 

        } elseif ($role == 'Student') {
            // Student Dashboard Path
            $dashboardLink = "../../../Student/MVC/php/UserDashboard.php";
            $searchAction = "../../../Student/MVC/php/SearchOutput.php";
        }
                elseif ($role == 'UniRep') {
            // University Representative Dashboard Path
            $dashboardLink = "../../../UniversityRepresentative/MVC/php/UniversityRepDashboard.php"; 
            $searchAction = "../../../UniversityRepresentative/MVC/php/SearchOutput.php"; 
    }
    elseif ($role == 'Admin') {
            // Admin Dashboard Path
            $dashboardLink = "../../../Admin/MVC/php/AdminDashboard.php"; 
        }
    }
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/homepage.css">
</head>
<body>
<?php if (isset($_GET['login']) && $_GET['login'] == 'success'): ?>
    <script>
        window.history.replaceState(null, null, window.location.pathname);
    </script>
<?php endif; ?>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo-wrapper">
                <div class="logo-icon">
                    <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">

                </div>
                <span class="logo-text">Rate Your Grader</span>
            </div>
            
<div class="nav-links">
        <a href="#how-it-works">How it works</a>
        <a href="#features">Features</a>
        <a href="javascript:void(0)" onclick="goToSearch()">Search Graders</a>

        <?php if (isset($_SESSION['user_name'])): ?>
            <a href="<?php echo $dashboardLink; ?>" style="text-decoration:none;">
                <span style="margin-right: 15px; font-weight: bold; color: inherit;">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </a>
            <a href="Logout.php" class="btn btn-primary" style="background-color: #dc3545;color: white;">Logout</a>
        <?php else: ?>
            <a href="Login.php" class="btn btn-primary" style="color: white;">Sign Up Free</a>
        <?php endif; ?>
    </div>
    </nav>

    <section class="hero-section">
        <div class="float-shape shape-1"></div>
        <div class="float-shape shape-2 floating"></div>

        <div class="container hero-container">
            <div class="hero-content fade-in">
                <h1>
                    Rate Your
                    <span class="gradient-text-yellow">Graders</span>
                </h1>
                <p class="hero-subtitle">
                    Honest reviews. Fair grading. Real transparency. 
                    <span class="highlight">Join 500K+ students already rating.</span>
                </p>
                <div class="hero-buttons">
                    <button class="btn btn-white" onclick="goToSearch()">
                        <img src="../images/search_blue.png" alt="Logo"> Search Graders
                    </button>

                </div>
                                
            </div>

            <div class="hero-slider fade-in">
<div class="slider-box">
        <div class="slider-track">
            
            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">Challenging but rewarding, this physics course builds strong problem-solving skills while making complex concepts feel surprisingly intuitive.</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/aiden.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Khaled Mahamud</h3>
                        <p>Physics 101</p>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">Comprehensive curriculum. The mentorship program connected me with real professionals.</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/sofia.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Tasnim Jara</h3>
                        <p>Data Science 202</p>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">The supportive community made the journey enjoyable. I've now built three apps!</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/caleb.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Khorshed Alom</h3>
                        <p>Web Development</p>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">Clear, practical, and well-structured, this database course makes complex concepts easy to understand through hands-on examples.</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/destiny.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Sadia Afrin</h3>
                        <p>Database Managemnet System</p>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">Challenging yet fascinating, the Theory of Computation course sharpens logical thinking and reveals the mathematical foundations of computer science.</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/jessica.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Riazul Islam</h3>
                        <p>Theory of Computation</p>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">Comprehensive curriculum. The mentorship program connected me with real professionals.</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/maria.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Sirajum Munira</h3>
                        <p>Machine Learning</p>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">Beginner-friendly and engaging, the Introduction to Programming course builds strong fundamentals through clear explanations and practical exercises.</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/ryan.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Oishi Sultana</h3>
                        <p>Introduction to programming</p>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="quote-icon">"</div>
                <p class="review-text">Well-structured and practical, the Electrical Circuit course clearly explains fundamentals while strengthening analytical and problem-solving skills.</p>
                <div class="rating">★★★★★</div>
                <div class="student-info">
                    <img src="Figures/micah.png" alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <h3>Mousumi Bala</h3>
                        <p>Electrical Circuits</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
            </div>

            
        </div>
    </section>

    <section id="search" class="section-search">
        <div class="container fade-in text-center"><center>
            <h2>Ready to discover fair graders?</h2></center>
            <p class="cta-subtitle">Search your graders and grade them like how they did for you.</p>
            
<form action="<?php echo $searchAction; ?>" method="GET" class="search-bar-wrapper" onsubmit="return validateSearch()">
    <div class="search-bar">
        <input type="text" id="mainSearchInput" name="q" placeholder="Search by name, course, or department...">
        <button type="submit" class="btn-search">
            <img src="../images/search_black.png" alt="Logo"> Search Now
        </button>
    </div>
</form>
            
            <div class="trust-indicators">
                <div><img src="../images/verify.png" alt="Logo"> Verified Students</div>
                <div><img src="../images/verify.png" alt="Logo"> Anonymous Reviews</div>
                <div><img src="../images/verify.png" alt="Logo"> Real-Time Updates</div>
                <div><img src="../images/verify.png" alt="Logo"> Campus Verified</div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="section-white">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="gradient-text-navy">How It Works</h2>
                <p>Three simple steps to rate and find the best graders on campus</p>
            </div>
            
            <div class="steps-grid">
                <div class="step-card fade-in hover-scale group search">
                    <div class="icon-box">
                        <img src="../images/search_32.png" alt="Logo">
                    </div>
                    <h3>1. Search</h3>
                    <p>Find your grader by name, course, or department. See real student ratings and reviews.</p>
                </div>
                
                <div class="step-card fade-in hover-scale group rate">
                    <div class="icon-box">
                        <img src="../images/rate.png" alt="Logo" class="logo-img">
                    </div>
                    <h3>2. Rate</h3>
                    <p>Share your honest experience. Rate fairness, speed, and accuracy. Help others make smart choices.</p>
                </div>
                
                <div class="step-card fade-in hover-scale group explore">
                    <div class="icon-box">
                        <img src="../images/explore.png" alt="Logo">
                    </div>
                    <h3>3. Discover</h3>
                    <p>Use ratings to choose classes with fair graders. Make informed decisions for your academic success.</p>
                </div>
            </div>
        </div>
    </section>


    <section id="features" class="section-features">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="gradient-text-navy">Everything You Need</h2>
                <p>Built for students, by students</p>
            </div>

            <div class="features-grid">
                <div class="features-list fade-in">
                    <div class="feature-card glass-light hover-scale">
                        <div class="feature-icon blue-bg">
                            <img src="../images/shield.png" alt="Logo">
                        </div>
                        <div class="feature-text">
                            <h4>Anonymous & Safe</h4>
                            <p>Your identity is protected. Rate honestly without fear of retaliation.</p>
                        </div>
                    </div>
                    
                    <div class="feature-card glass-light hover-scale">
                        <div class="feature-icon green-bg">
                            <img src="../images/details.png" alt="Logo">
                        </div>
                        <div class="feature-text">
                            <h4>Detailed Ratings</h4>
                            <p>Rate on fairness, grading speed, accuracy, and helpfulness. Get the full picture.</p>
                        </div>
                    </div>
                    
                    <div class="feature-card glass-light hover-scale">
                        <div class="feature-icon purple-bg">
                            <img src="../images/mobile.png" alt="Logo">
                        </div>
                        <div class="feature-text">
                            <h4>Campus Verified</h4>
                            <p>Only verified students can rate. Real reviews from real students on your campus.</p>
                        </div>
                    </div>
                </div>
                <div class="features-list features-list-right fade-in">
                    <div class="feature-card glass-light hover-scale">
                        <div class="feature-icon blue-bg">
                            <img src="../images/search_white.png" alt="Logo">
                        </div>
                        <div class="feature-text">
                            <h4>Smart Search Filters</h4>
                            <p>Find graders by course, department, or campus to get the exact insight you need.</p>
                        </div>
                    </div>
                    
                    <div class="feature-card glass-light hover-scale">
                        <div class="feature-icon green-bg">
                            <img src="../images/verify_white.png" alt="Logo">
                        </div>
                        <div class="feature-text">
                            <h4>Real-Time Updates</h4>
                            <p>New ratings and reviews appear instantly so you always have current feedback.</p>
                        </div>
                    </div>
                    
                    <div class="feature-card glass-light hover-scale">
                        <div class="feature-icon purple-bg">
                            <img src="../images/explore_white.png" alt="Logo">
                        </div>
                        <div class="feature-text">
                            <h4>Community Standards</h4>
                            <p>Helpful reviews rise to the top while moderation keeps feedback honest and fair.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo-wrapper mb-2">
                        <div class="logo-icon small">
                            <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
                        </div>
                        <span class="footer-logo-text">Rate Your Grader</span>
                    </div>
                    <p>Empowering students with transparent grading information since 2024.</p>
                </div>

                <div class="footer-actions">
                    <h5>Apply</h5>
                    <div class="footer-buttons">
                        <a href="#" class="footer-nav-link">Apply for Reviewer</a>
                        <a href="#" class="footer-nav-link">Apply for University Representative</a>
                    </div>
                </div>

                <div class="footer-socials">
                    <h5>Our Socials</h5>
                    <div class="social-icons">
                        <a href="https://www.facebook.com" aria-label="Facebook">
                            <img src="../images/facebook.png" alt="Facebook" class="social-icon">
                        </a>
                        <a href="https://www.instagram.com" aria-label="Instagram">
                            <img src="../images/instagram.png" alt="Instagram" class="social-icon">
                        </a>
                        <a href="https://www.twitter.com" aria-label="Twitter">
                            <img src="../images/twitter.png" alt="Twitter" class="social-icon">
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/homepage.js"></script>

    <script>
        function goToSearch() {
            // Scroll to the search section with ID 'search'
            const searchSection = document.getElementById('search');
            searchSection.scrollIntoView({ behavior: 'smooth' });
            
            // Focus on the input field so user can type immediately
            setTimeout(() => {
                document.getElementById('mainSearchInput').focus();
            }, 500); // Small delay to allow scroll to finish
        }

        function validateSearch() {
            const input = document.getElementById('mainSearchInput').value;
            // Check if input is empty or just whitespace
            if (!input || input.trim() === "") {
                alert("Please enter a Professor name, University, or Course to search.");
                return false; // Prevent form submission
            }
            return true; // Allow form submission
        }
    </script>
</body>
</html>