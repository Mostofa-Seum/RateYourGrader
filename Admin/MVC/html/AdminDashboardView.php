<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/AdminDashboard.css">
    <title>Admin Dashboard</title>
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
            </div>
            <span class="logo-text">Admin Panel</span>
        </div>
        <div class="nav-links">
            <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
        </div>
    </div>
</nav>

<main class="dashboard-page">
    <div class="container">
        <div class="header-section">
            <h2 class="page-title">System Overview</h2>
            <p class="subtitle">Manage users, faculty, and content moderation.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card" onclick="openSection('users')">
                <div class="stat-icon" style="background: #e3f2fd; color: #1e88e5;">👥</div>
                <div class="stat-number"><?php echo $cnt_users; ?></div>
                <div class="stat-label">Manage Users</div>
            </div>
            <div class="stat-card" onclick="openSection('reviewers')">
                <div class="stat-icon" style="background: #fff3e0; color: #fb8c00;">⚖️</div>
                <div class="stat-number"><?php echo $cnt_rev; ?></div>
                <div class="stat-label">Manage Reviewers</div>
            </div>
            <div class="stat-card" onclick="openSection('unireps')">
                <div class="stat-icon" style="background: #e8f5e9; color: #43a047;">🎓</div>
                <div class="stat-number"><?php echo $cnt_unirep; ?></div>
                <div class="stat-label">Manage Uni Reps</div>
            </div>
            <div class="stat-card" onclick="openSection('professors')">
                <div class="stat-icon" style="background: #f3e5f5; color: #8e24aa;">👨‍🏫</div>
                <div class="stat-number"><?php echo $cnt_prof; ?></div>
                <div class="stat-label">Manage Faculty</div>
            </div>
            <div class="stat-card" onclick="openSection('requests')">
                <div class="stat-icon" style="background: #ffebee; color: #e53935;">📩</div>
                <div class="stat-number"><?php echo $cnt_req; ?></div>
                <div class="stat-label">Role Requests</div>
            </div>
            <div class="stat-card" onclick="openSection('reviews')">
                <div class="stat-icon" style="background: #e0f7fa; color: #00acc1;">📝</div>
                <div class="stat-number">All</div>
                <div class="stat-label">Manage Reviews</div>
            </div>
        </div>

        <div id="admin-content-area" class="content-area" style="display:none;">
            <div class="section-header">
                <h3 id="section-title">Manage Section</h3>
                <button class="btn btn-secondary btn-sm" onclick="closeSection()">Close Panel</button>
            </div>
            <div id="dynamic-table-container">
                <div class="loader">Loading...</div>
            </div>
        </div>
    </div>
</main>

<div id="toast-box" class="toast-box"></div>

<div class="modal" id="confirmModal">
    <div class="modal-content confirmation-box">
        <div class="confirmation-icon">⚠️</div>
        <h3 id="confirm-title">Confirm Action</h3>
        <p id="confirm-msg" style="color:#666; margin-bottom:1.5rem;">Are you sure?</p>
        <div class="modal-actions confirmation-actions">
            <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn btn-danger" id="confirm-btn-action">Confirm</button>
        </div>
    </div>
</div>

<div class="modal" id="editProfModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Professor</h2>
            <button class="close-modal" onclick="closeModal('editProfModal')">×</button>
        </div>
        <form id="editProfForm" onsubmit="return false;">
            <input type="hidden" id="edit_p_id">
            <div class="form-group">
                <label>Name</label> <input type="text" id="edit_name">
            </div>
            <div class="form-group">
                <label>Department</label> <input type="text" id="edit_dept">
            </div>
            <div class="form-group">
                <label>University</label> <input type="text" id="edit_uni">
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="submitProfUpdate()">Save Changes</button>
            </div>
        </form>
    </div>
</div>

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
            <div class="footer-socials">
                <h5>Our Socials</h5>
                <div class="social-icons">
                        <a href="https://www.facebook.com"><img src="../images/facebook.png" alt="Facebook" class="social-icon"></a>
                        <a href="https://www.instagram.com"><img src="../images/instagram.png" alt="Instagram" class="social-icon"></a>
                        <a href="https://www.twitter.com"><img src="../images/twitter.png" alt="Twitter" class="social-icon"></a>
                    </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="../js/AdminDashboard.js"></script>
</body>
</html>