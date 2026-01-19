<?php
// AdminDashboard.php (Controller)
session_start();
include '../db/Config.php';

//HANDLE AJAX REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $current_admin_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    //USER MANAGEMENT
    
    //Get All Users
    if ($action === 'get_users') {
        $sql = "SELECT s_id, Username, Email, Role FROM users WHERE Role = 'student' OR Role = 'User' OR ROLE = 'Reviewer'OR Role = 'UniRep'";
        $res = $conn->query($sql);
        $users = [];
        if($res) while ($row = $res->fetch_assoc()) $users[] = $row;
        echo json_encode(['status' => 'success', 'data' => $users]);
        exit;
    }

    //Search Users
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

    //Assign Role
    if ($action === 'assign_role') {
        $s_id = intval($_POST['user_id']);
        $role = $_POST['role']; 
        
        // Check if user already has this role
        $check_stmt = $conn->prepare("SELECT Role FROM users WHERE s_id = ?");
        $check_stmt->bind_param("i", $s_id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        $current_data = $check_res->fetch_assoc();

        if ($current_data && strtolower($current_data['Role']) === strtolower($role)) {
            echo json_encode(['status' => 'error', 'message' => "Action Failed: User is already a $role"]);
            exit;
        }

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

    //Remove User
    if ($action === 'delete_user') {
        $s_id = intval($_POST['user_id']);
        $conn->query("DELETE FROM reviwer WHERE s_id = $s_id"); 
        $conn->query("DELETE FROM users WHERE s_id = $s_id");
        echo json_encode(['status' => 'success', 'message' => 'User removed successfully']);
        exit;
    }

    //REVIEWER & UNIREP MANAGEMENT

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

    //PROFESSOR MANAGEMENT
    
    //Get All Professors (Grouped)
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

    //REVIEW MANAGEMENT

    if ($action === 'get_reviews') {
        $acc_sql = "SELECT ar.AR_id, ar.Review, r.r_id, r.`Overall Rating` as Overall_Rating, 'Approved' as status, 
                    CASE 
                        WHEN u_act.Role = 'Admin' THEN 'Admin'
                        WHEN u_act.Username IS NOT NULL THEN u_act.Username
                        WHEN u_assign.Role = 'Admin' THEN 'Admin'
                        ELSE COALESCE(u_assign.Username, 'Unknown')
                    END as ReviewerName 
                    FROM a_review ar 
                    JOIN review r ON ar.r_id = r.r_id 
                    LEFT JOIN reviwer rv_act ON ar.AR_id = rv_act.AR_id 
                    LEFT JOIN users u_act ON rv_act.s_id = u_act.s_id
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
                    LEFT JOIN reviwer rv_act ON rr.RR_id = rv_act.RR_id
                    LEFT JOIN users u_act ON rv_act.s_id = u_act.s_id
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

    //REQUESTS MANAGEMENT (UPDATED TO MATCH 'applications' TABLE)
    if ($action === 'get_requests') {
        // Changed table check from 'role_requests' to 'applications'
        $check = $conn->query("SHOW TABLES LIKE 'applications'");
        if ($check && $check->num_rows > 0) {
            // Note: We alias 'App_id' as 'id' and 'Applied Role' as 'requested_role' 
            // so the frontend JS doesn't need to change.
            $sql = "SELECT a.App_id as id, u.Username, a.`Applied Role` as requested_role 
                    FROM applications a 
                    JOIN users u ON a.S_id = u.s_id";
            
            $res = $conn->query($sql);
            $reqs = [];
            if($res) while ($row = $res->fetch_assoc()) $reqs[] = $row;
            echo json_encode(['status' => 'success', 'data' => $reqs]);
        } else {
            echo json_encode(['status' => 'success', 'data' => []]);
        }
        exit;
    }

    if ($action === 'handle_request') {
        $req_id = intval($_POST['req_id']); // This is actually App_id
        $decision = $_POST['decision'];
        
        $check = $conn->query("SHOW TABLES LIKE 'applications'");
        if ($check && $check->num_rows > 0) {
            if ($decision === 'approve') {
                $q = $conn->query("SELECT S_id, `Applied Role` FROM applications WHERE App_id = $req_id");
                $req = $q->fetch_assoc();
                
                if ($req) {
                    $u_id = $req['S_id'];
                    $role = $req['Applied Role'];
                    
                    // Update User Role
                    $conn->query("UPDATE users SET Role = '$role' WHERE s_id = $u_id");
                    
                    // Add to reviewer table if applicable
                    if ($role === 'Reviewer') {
                         // Prevent duplicates
                        $chk = $conn->query("SELECT RV_id FROM reviwer WHERE s_id = $u_id");
                        if ($chk->num_rows == 0) {
                            $conn->query("INSERT INTO reviwer (s_id) VALUES ($u_id)");
                        }
                    }
                    
                    // DELETE from applications to remove it from the list
                    $conn->query("DELETE FROM applications WHERE App_id = $req_id");

                    echo json_encode(['status' => 'success', 'message' => 'Request Approved']);
                }
            } else {
                // If rejected, just delete from applications table so it disappears
                $conn->query("DELETE FROM applications WHERE App_id = $req_id");
                echo json_encode(['status' => 'success', 'message' => 'Request Rejected']);
            }
        }
        exit;
    }
    exit;
}

//PAGE LOAD COUNTS & SECURITY

if (!isset($_SESSION['s_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../../../Common/MVC/php/Login.php"); 
    exit(); 
}

$cnt_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE Role = 'student' OR Role = 'Reviewer' OR Role = 'UniRep'")->fetch_assoc()['c'];
$cnt_rev = $conn->query("SELECT COUNT(*) as c FROM users WHERE Role = 'Reviewer'")->fetch_assoc()['c'];
$cnt_unirep = $conn->query("SELECT COUNT(*) as c FROM users WHERE Role = 'UniRep' OR Role = 'uni_rep'")->fetch_assoc()['c'];

// Count UNIQUE Professors only
$cnt_prof = $conn->query("SELECT COUNT(DISTINCT Name, Department, University) as c FROM professors")->fetch_assoc()['c'];

// Check applications table for pending count
$check_req = $conn->query("SHOW TABLES LIKE 'applications'");
$cnt_req = ($check_req && $check_req->num_rows > 0) ? $conn->query("SELECT COUNT(*) as c FROM applications")->fetch_assoc()['c'] : 0;

include '../html/AdminDashboardView.php';
?>