<?php
session_start();
include '../db/Config.php';

// --- HANDLE AJAX REQUESTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $current_admin_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    // --- 1. USER MANAGEMENT ---
    
    // Get All Users
    if ($action === 'get_users') {
        $sql = "SELECT s_id, Username, Email, Role FROM users WHERE Role = 'student' OR Role = 'User' OR ROLE = 'Reviewer'OR Role = 'UniRep'";
        $res = $conn->query($sql);
        $users = [];
        if($res) while ($row = $res->fetch_assoc()) $users[] = $row;
        echo json_encode(['status' => 'success', 'data' => $users]);
        exit;
    }

    // --- SEARCH USERS ---
    if ($action === 'search_users') {
        $query = $_POST['query'] ?? '';
        $searchTerm = "%" . $query . "%"; 
        
        $sql = "SELECT s_id, Username, Email, Role FROM users 
                WHERE (Role = 'student' OR Role = 'User' OR Role IS NULL OR Role = '') 
                AND Username LIKE ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $users = [];
        while ($row = $res->fetch_assoc()) $users[] = $row;
        
        echo json_encode(['status' => 'success', 'data' => $users]);
        exit;
    }

    // Assign Role
    if ($action === 'assign_role') {
        $s_id = intval($_POST['user_id']);
        $role = $_POST['role']; 
        
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE users SET Role = ? WHERE s_id = ?");
            $stmt->bind_param("si", $role, $s_id);
            $stmt->execute();

            if ($role === 'Reviewer') {
                $chk = $conn->query("SELECT RV_id FROM reviwer WHERE s_id = $s_id AND AR_id IS NULL AND RR_id IS NULL");
                if ($chk->num_rows == 0) {
                    $stmt2 = $conn->prepare("INSERT INTO reviwer (s_id) VALUES (?)");
                    $stmt2->bind_param("i", $s_id);
                    $stmt2->execute();
                }
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => "User promoted to $role"]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // Remove User
    if ($action === 'delete_user') {
        $s_id = intval($_POST['user_id']);
        $conn->query("DELETE FROM reviwer WHERE s_id = $s_id"); 
        $conn->query("DELETE FROM users WHERE s_id = $s_id");
        echo json_encode(['status' => 'success', 'message' => 'User removed successfully']);
        exit;
    }

    // --- 2. REVIEWER & UNIREP MANAGEMENT ---

    if ($action === 'get_reviewers') {
        $res = $conn->query("SELECT s_id, Username, Email FROM users WHERE Role = 'Reviewer'");
        $users = [];
        if($res) while ($row = $res->fetch_assoc()) $users[] = $row;
        echo json_encode(['status' => 'success', 'data' => $users]);
        exit;
    }

    if ($action === 'get_unireps') {
        $res = $conn->query("SELECT s_id, Username, Email FROM users WHERE Role = 'UniRep'");
        $users = [];
        if($res) while ($row = $res->fetch_assoc()) $users[] = $row;
        echo json_encode(['status' => 'success', 'data' => $users]);
        exit;
    }

    if ($action === 'demote_user') {
        $s_id = intval($_POST['user_id']);
        $conn->query("DELETE FROM reviwer WHERE s_id = $s_id");
        $conn->query("UPDATE users SET Role = 'Student' WHERE s_id = $s_id");
        echo json_encode(['status' => 'success', 'message' => 'User demoted successfully']);
        exit;
    }

    // --- 3. PROFESSOR MANAGEMENT ---
    
    // Get All Professors (Grouped)
    if ($action === 'get_professors') {
        $sql = "SELECT * FROM professors GROUP BY Name, Department, University ORDER BY Name ASC";
        $res = $conn->query($sql);
        $profs = [];
        if($res) while ($row = $res->fetch_assoc()) $profs[] = $row;
        echo json_encode(['status' => 'success', 'data' => $profs]);
        exit;
    }

    // Search Faculty (Grouped)
    if ($action === 'search_professors') {
        $query = $_POST['query'] ?? '';
        $searchTerm = "%" . $query . "%"; 
        
        $sql = "SELECT * FROM professors WHERE Name LIKE ? GROUP BY Name, Department, University ORDER BY Name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $profs = [];
        while ($row = $res->fetch_assoc()) $profs[] = $row;
        
        echo json_encode(['status' => 'success', 'data' => $profs]);
        exit;
    }

    if ($action === 'update_professor') {
        $p_id = intval($_POST['p_id']);
        $name = $_POST['name'];
        $dept = $_POST['department'];
        $uni = $_POST['university'];
        
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("UPDATE professors SET Name=?, Department=?, University=? WHERE P_id=?");
            $stmt1->bind_param("sssi", $name, $dept, $uni, $p_id);
            $stmt1->execute();
            
            $stmt2 = $conn->prepare("UPDATE university SET uni_name=? WHERE p_id=?");
            $stmt2->bind_param("si", $uni, $p_id);
            $stmt2->execute();

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Professor details updated']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // --- 4. REVIEW MANAGEMENT ---

    if ($action === 'get_reviews') {
        // UPDATED SQL:
        // 1. Checks 'u_act' (User who performed the Action) first.
        // 2. CASE statement: If that user has Role='Admin', display 'Admin'. Else display their Username.
        // 3. Fallback to 'u_assign' (Original Reviewer) if no action logger exists.
        
        $acc_sql = "SELECT ar.AR_id, ar.Review, r.r_id, r.`Overall Rating` as Overall_Rating, 'Approved' as status, 
                    CASE 
                        WHEN u_act.Role = 'Admin' THEN 'Admin'
                        WHEN u_act.Username IS NOT NULL THEN u_act.Username
                        WHEN u_assign.Role = 'Admin' THEN 'Admin'
                        ELSE COALESCE(u_assign.Username, 'Unknown')
                    END as ReviewerName 
                    FROM a_review ar 
                    JOIN review r ON ar.r_id = r.r_id 
                    -- Link 1: Action based (Who approved it?)
                    LEFT JOIN reviwer rv_act ON ar.AR_id = rv_act.AR_id 
                    LEFT JOIN users u_act ON rv_act.s_id = u_act.s_id
                    -- Link 2: Assignment based (Who was assigned?)
                    LEFT JOIN reviwer rv_assign ON r.Rv_id = rv_assign.RV_id
                    LEFT JOIN users u_assign ON rv_assign.s_id = u_assign.s_id";
        
        $rej_sql = "SELECT rr.RR_id, r.Review, r.r_id, r.`Overall Rating` as Overall_Rating, 'Rejected' as status, rr.Cause,
                    CASE 
                        WHEN u_act.Role = 'Admin' THEN 'Admin'
                        WHEN u_act.Username IS NOT NULL THEN u_act.Username
                        WHEN u_assign.Role = 'Admin' THEN 'Admin'
                        ELSE COALESCE(u_assign.Username, 'Unknown')
                    END as ReviewerName
                    FROM r_review rr 
                    JOIN review r ON rr.r_id = r.r_id 
                    -- Link 1: Action based (Who rejected it?)
                    LEFT JOIN reviwer rv_act ON rr.RR_id = rv_act.RR_id
                    LEFT JOIN users u_act ON rv_act.s_id = u_act.s_id
                    -- Link 2: Assignment based (Who was assigned?)
                    LEFT JOIN reviwer rv_assign ON r.Rv_id = rv_assign.RV_id
                    LEFT JOIN users u_assign ON rv_assign.s_id = u_assign.s_id";
        
        $reviews = [];
        $acc = $conn->query($acc_sql);
        if($acc) while ($row = $acc->fetch_assoc()) $reviews[] = $row;
        $rej = $conn->query($rej_sql);
        if($rej) while ($row = $rej->fetch_assoc()) $reviews[] = $row;
        
        echo json_encode(['status' => 'success', 'data' => $reviews]);
        exit;
    }

    // --- MODIFIED SECTION STARTS HERE ---
    if ($action === 'toggle_review') {
        $r_id = intval($_POST['r_id']);
        $current_status = $_POST['current_status'];
        
        $conn->begin_transaction();
        try {
            if ($current_status === 'Approved') {
                $get_ar = $conn->query("SELECT AR_id FROM a_review WHERE r_id = $r_id");
                if($row = $get_ar->fetch_assoc()) {
                    $old_ar_id = $row['AR_id'];
                    $conn->query("DELETE FROM reviwer WHERE AR_id = $old_ar_id");
                }
                $conn->query("DELETE FROM a_review WHERE r_id = $r_id");

                // Insert into Rejected
                $stmt = $conn->prepare("INSERT INTO r_review (r_id, Cause) VALUES (?, 'Admin Rejected')");
                $stmt->bind_param("i", $r_id);
                $stmt->execute();
                
                // DELETED: Logging Admin Action in reviwer table

                $new_status = 'Rejected';
            } else {
                $get_rr = $conn->query("SELECT RR_id FROM r_review WHERE r_id = $r_id");
                if($row = $get_rr->fetch_assoc()) {
                    $old_rr_id = $row['RR_id'];
                    $conn->query("DELETE FROM reviwer WHERE RR_id = $old_rr_id");
                }
                
                $text_q = $conn->query("SELECT Review FROM review WHERE r_id = $r_id");
                $text = $text_q->fetch_assoc()['Review'];
                
                $conn->query("DELETE FROM r_review WHERE r_id = $r_id");

                // Insert into Approved
                $stmt = $conn->prepare("INSERT INTO a_review (r_id, Review, Report) VALUES (?, ?, 0)");
                $stmt->bind_param("is", $r_id, $text);
                $stmt->execute();
                
                // DELETED: Logging Admin Action in reviwer table
                
                $new_status = 'Approved';
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => "Review changed to $new_status"]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Action failed: ' . $e->getMessage()]);
        }
        exit;
    }
    // --- MODIFIED SECTION ENDS HERE ---

    // --- 5. REQUESTS ---
    if ($action === 'get_requests') {
        $check = $conn->query("SHOW TABLES LIKE 'role_requests'");
        if ($check && $check->num_rows > 0) {
            $res = $conn->query("SELECT rq.id, u.Username, rq.requested_role 
                                 FROM role_requests rq 
                                 JOIN users u ON rq.s_id = u.s_id 
                                 WHERE rq.status = 'Pending'");
            $reqs = [];
            if($res) while ($row = $res->fetch_assoc()) $reqs[] = $row;
            echo json_encode(['status' => 'success', 'data' => $reqs]);
        } else {
            echo json_encode(['status' => 'success', 'data' => []]);
        }
        exit;
    }

    if ($action === 'handle_request') {
        $req_id = intval($_POST['req_id']);
        $decision = $_POST['decision'];
        
        $check = $conn->query("SHOW TABLES LIKE 'role_requests'");
        if ($check && $check->num_rows > 0) {
            if ($decision === 'approve') {
                $q = $conn->query("SELECT s_id, requested_role FROM role_requests WHERE id = $req_id");
                $req = $q->fetch_assoc();
                
                if ($req) {
                    $u_id = $req['s_id'];
                    $role = $req['requested_role'];
                    $conn->query("UPDATE users SET Role = '$role' WHERE s_id = $u_id");
                    $conn->query("UPDATE role_requests SET status = 'Approved' WHERE id = $req_id");
                    
                    if ($role === 'Reviewer') {
                        $conn->query("INSERT INTO reviwer (s_id) VALUES ($u_id)");
                    }
                    echo json_encode(['status' => 'success', 'message' => 'Request Approved']);
                }
            } else {
                $conn->query("UPDATE role_requests SET status = 'Rejected' WHERE id = $req_id");
                echo json_encode(['status' => 'success', 'message' => 'Request Rejected']);
            }
        }
        exit;
    }
    exit;
}

// --- PAGE LOAD COUNTS ---
// UPDATED SECURITY CHECK
if (!isset($_SESSION['s_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../../../Common/MVC/php/Login.php"); 
    exit(); 
}

$cnt_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE Role = 'student' OR Role = 'Reviewer' OR Role = 'UniRep'")->fetch_assoc()['c'];
$cnt_rev = $conn->query("SELECT COUNT(*) as c FROM users WHERE Role = 'Reviewer'")->fetch_assoc()['c'];
$cnt_unirep = $conn->query("SELECT COUNT(*) as c FROM users WHERE Role = 'UniRep' OR Role = 'uni_rep'")->fetch_assoc()['c'];

// UPDATED: Count UNIQUE Professors only
$cnt_prof = $conn->query("SELECT COUNT(DISTINCT Name, Department, University) as c FROM professors")->fetch_assoc()['c'];

$check_req = $conn->query("SHOW TABLES LIKE 'role_requests'");
$cnt_req = ($check_req && $check_req->num_rows > 0) ? $conn->query("SELECT COUNT(*) as c FROM role_requests WHERE status='Pending'")->fetch_assoc()['c'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/AdminDashboard.css">
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
                    <a href="#" aria-label="Facebook"><img src="../images/facebook.png" alt="Facebook" class="social-icon"></a>
                    <a href="#" aria-label="Instagram"><img src="../images/instagram.png" alt="Instagram" class="social-icon"></a>
                    <a href="#" aria-label="Twitter"><img src="../images/twitter.png" alt="Twitter" class="social-icon"></a>
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