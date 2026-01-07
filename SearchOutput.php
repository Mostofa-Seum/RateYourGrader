<?php
include 'Config.php';

// 1. DEFINE IMAGES (Using your PNG files)
$iconUser  = '<img src="Images/iconUser.png" alt="User" style="width: 4rem; height: 4rem; object-fit: contain;">';
$starFull  = '<img src="Images/starFull.png" alt="Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starHalf  = '<img src="Images/starHalf.jpg" alt="Half Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starEmpty = '<img src="Images/zeroStar.png" alt="Empty Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$iconArrow = '<img src="Images/iconArrow.png" alt="Arrow" style="width: 1em; height: 1em; vertical-align: middle;">';

// 2. SEARCH LOGIC
$search_term = "";
$search_performed = false;
$result = null;
$count = 0;

if (isset($_GET['q'])) {
    $search_performed = true;
    $search_term = $_GET['q'];
    
    // Secure the input
    $safe_search = $conn->real_escape_string($search_term);
    
    // Run the query
    $sql = "SELECT * FROM professors WHERE Name LIKE '%$safe_search%' OR Department LIKE '%$safe_search%' OR University LIKE '%$safe_search%'";
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Query Failed: " . $conn->error);
    }
    $count = $result->num_rows;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="SearchOutput.css">
    <title>Find Your Grader</title>
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo-wrapper">
                <div class="logo-icon">
                    <img src="Images/scolar_cap.png" alt="Logo" class="logo-img">
                </div>
                <span class="logo-text">Rate Your Grader</span>
            </div>
            
            <div class="nav-links">
                <a href="#how-it-works">How it works</a>
                <a href="#features">Features</a>
                <button class="btn btn-primary">Sign Up Free</button>
            </div>
            
            <button class="mobile-menu-btn">
                <img src="Figures/menu.png" alt="Menu" class="mobile-menu-icon">
            </button>
        </div>
    </nav>
    <div style="margin-top: 80px;"></div>

    <section class="search-section">
        <div class="search-container">
            <div class="text-center" style="margin-bottom: 2rem;">
                <h2 class="search-title">Find Your Grader</h2>
                <p>Search for graders and read reviews from fellow students</p>
            </div>
            
            <form action="" method="GET" class="search-wrapper" onsubmit="return validateSearch()">
                <input 
                    type="text" 
                    id="searchBox" 
                    name="q"
                    value="<?php echo htmlspecialchars($search_term); ?>"
                    placeholder="Enter grader's name, dept, or university"
                    class="search-input"
                    required
                >
                <button type="submit" class="search-btn">
                    <img src="Images/SearchIcon.png" alt="Search Icon">
                </button>
            </form>
        </div>
    </section>

    <?php if ($search_performed): ?>
    <main class="results-section">
        <div class="container">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.5rem; font-weight: 700;">Search Results</h3>
                <p>Found <span id="resultCount"><?php echo $count; ?></span> graders matching your search</p>
            </div>

            <div id="graderResults" class="results-grid">
                <?php
                if ($count > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Generate random stats for display
                        $randomRating = number_format(rand(35, 50) / 10, 1);
                        $randomFairness = number_format(rand(35, 50) / 10, 1);
                        $randomClarity = number_format(rand(35, 50) / 10, 1);
                ?>
                
                <div class="card">
                    <div class="card-padding">
                        <div class="flex items-center" style="margin-bottom: 1rem;">
                            <div class="grader-avatar">
                                <?= $iconUser ?>
                            </div>
                            <div class="grader-info">
                                <h4><?php echo htmlspecialchars($row['Name']); ?></h4>
                                <p style="font-size: 0.9rem; color: #666;">
                                    <?php echo htmlspecialchars($row['Department']); ?> <br>
                                    <span style="font-size: 0.8rem; color: #888;">at <?php echo htmlspecialchars($row['University']); ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <div class="rating-header">
                                <span style="font-weight: 600;">Overall Rating</span>
                                <span class="rating-score"><?php echo $randomRating; ?></span>
                            </div>
                            <div class="stars-row">
                                <?= $starFull . $starFull . $starFull . $starFull . $starHalf ?>
                                <span class="review-count">(<?php echo rand(10, 150); ?> reviews)</span>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div class="rating-row">
                                    <span class="rating-label">Fairness</span>
                                    <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo ($randomFairness/5)*100; ?>%"></div></div>
                                    <span class="rating-val"><?php echo $randomFairness; ?></span>
                                </div>
                                <div class="rating-row">
                                    <span class="rating-label">Clarity</span>
                                    <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo ($randomClarity/5)*100; ?>%"></div></div>
                                    <span class="rating-val"><?php echo $randomClarity; ?></span>
                                </div>
                            </div>
                        </div>
                        
                      <div class="card-footer">
                                <a href="ProfessorReview.php?P_id=<?= $row['P_id'] ?>&name=<?= urlencode($row['Name']) ?>&dept=<?= urlencode($row['Department']) ?>&uni=<?= urlencode($row['University']) ?>" 
                               class="view-profile-btn" style="text-decoration: none;">
                             View Full Profile <?= $iconArrow ?>
                             </a>
                               </div>
                    </div>
                </div>
                <?php 
                    } // End While
                } else {
                    echo "<p>No results found for '" . htmlspecialchars($search_term) . "'</p>";
                }
                ?>
            </div>
        </div>
    </main>
    <?php endif; ?>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h5>About Rate My Grader</h5>
                    <p>Helping students make informed decisions about their graders since 2025.</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2026 Rate My Grader. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="SearchOutput.js"></script>
</body>
</html>