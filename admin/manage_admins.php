<?php
include __DIR__ . '/../includes/csrf.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control - Admin only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include __DIR__ . '/../includes/header.php';

$message = "";

// Check if current user is the Super Admin (admin_id = 1 or username = 'admin')
$is_super_admin = ($_SESSION['username'] === 'admin');

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token()) {
        $message = "<div class='alert alert-danger'>Invalid session. Please refresh and try again.</div>";
    } else {
        $admin_username = trim($_POST['admin_username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $admin_password = trim($_POST['admin_password'] ?? '');
    $action = $_POST['action'] ?? '';

    if ($action == "add") {
        if(!empty($admin_username) && !empty($full_name) && !empty($admin_password)) {
            // Check if Username exists
            $check_stmt = $conn->prepare("SELECT * FROM admin_login WHERE admin_username = ?");
            if (!$check_stmt) {
                $message = "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
            } else {
                $check_stmt->bind_param("s", $admin_username);
                $check_stmt->execute();
                $check = $check_stmt->get_result();
                if($check->num_rows > 0) {
                    $message = "<div class='alert alert-danger'>Username already exists.</div>";
                } else {
                    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO admin_login (admin_username, full_name, admin_password, is_suspended) VALUES (?, ?, ?, 0)");
                    if (!$stmt) {
                        $message = "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
                    } else {
                        $stmt->bind_param("sss", $admin_username, $full_name, $hashed_password);
                        if($stmt->execute()) {
                            $message = "<div class='alert alert-success'>Admin Added Successfully.</div>";
                        } else {
                            $message = "<div class='alert alert-danger'>Error Adding Admin: " . htmlspecialchars($stmt->error) . "</div>";
                        }
                        $stmt->close();
                    }
                }
                $check_stmt->close();
            }
        } else {
            $message = "<div class='alert alert-warning'>Username, Full Name and Password are required.</div>";
        }
    } elseif ($action == "update") {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        if($admin_id > 0 && !empty($full_name)) {
            if(!empty($admin_password)) {
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admin_login SET full_name = ?, admin_password = ? WHERE admin_id = ?");
                if ($stmt) {
                    $stmt->bind_param("ssi", $full_name, $hashed_password, $admin_id);
                }
            } else {
                $stmt = $conn->prepare("UPDATE admin_login SET full_name = ? WHERE admin_id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $full_name, $admin_id);
                }
            }
            
            if (!$stmt) {
                $message = "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
            } else {
                if($stmt->execute()) {
                    $message = "<div class='alert alert-info'>Admin Updated.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error updating admin: " . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            }
        } else {
            $message = "<div class='alert alert-warning'>Full name is required for update.</div>";
        }
    } elseif ($action == "delete" && $is_super_admin) {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        if($admin_id > 0 && $admin_id != 1) { // Cannot delete super admin (id=1)
            $stmt = $conn->prepare("DELETE FROM admin_login WHERE admin_id = ?");
            $stmt->bind_param("i", $admin_id);
            if($stmt->execute()) {
                $message = "<div class='alert alert-warning'>Admin Deleted.</div>";
            }
            $stmt->close();
        }
    } elseif ($action == "suspend" && $is_super_admin) {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        if($admin_id > 0 && $admin_id != 1) { // Cannot suspend super admin
            $stmt = $conn->prepare("UPDATE admin_login SET is_suspended = 1 WHERE admin_id = ?");
            $stmt->bind_param("i", $admin_id);
            if($stmt->execute()) {
                $message = "<div class='alert alert-warning'>Admin Suspended.</div>";
            }
            $stmt->close();
        }
    } elseif ($action == "unsuspend" && $is_super_admin) {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        if($admin_id > 0) {
            $stmt = $conn->prepare("UPDATE admin_login SET is_suspended = 0 WHERE admin_id = ?");
            $stmt->bind_param("i", $admin_id);
            if($stmt->execute()) {
                $message = "<div class='alert alert-success'>Admin Unsuspended.</div>";
            }
            $stmt->close();
        }
    }
}
}

// Check for edit mode
$admin_to_edit = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM admin_login WHERE admin_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0) {
        $admin_to_edit = $res->fetch_assoc();
    }
}
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-user-shield" style="color: #d4af37;"></i>
                        <?php echo $admin_to_edit ? 'Update Admin' : 'Add New Admin'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>

                    <form method="POST">
                        <?php csrf_field(); ?>
                        <?php if($admin_to_edit): ?>
                            <input type="hidden" name="admin_id" value="<?php echo $admin_to_edit['admin_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Username</label>
                            <input type="text" 
                                   name="admin_username" 
                                   class="form-control form-control-lg" 
                                   placeholder="e.g. admin_john" 
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($admin_to_edit['admin_username'] ?? ''); ?>"
                                   <?php echo $admin_to_edit ? 'readonly' : ''; ?>
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Full Name</label>
                            <input type="text" 
                                   name="full_name" 
                                   class="form-control form-control-lg" 
                                   placeholder="Enter full name"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($admin_to_edit['full_name'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="admin_password" 
                                       id="admin_password"
                                       class="form-control form-control-lg border-right-0" 
                                       style="border-radius: 10px 0 0 10px;"
                                       placeholder="<?php echo $admin_to_edit ? 'Leave blank to keep current' : 'Enter password'; ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white" style="border-radius: 0 10px 10px 0; cursor: pointer;" onclick="togglePassword('admin_password', 'toggleIcon')">
                                        <i class="fas fa-eye text-muted" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <?php if($admin_to_edit): ?>
                                <div class="col-6">
                                    <button type="submit" name="action" value="update" class="btn btn-block btn-lg" style="background: #d4af37; color: #000; border-radius: 10px;">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="manage_admins.php" class="btn btn-dark btn-block btn-lg" style="border-radius: 10px;">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <button type="submit" name="action" value="add" class="btn btn-block btn-lg" style="background: #000; color: #fff; border-radius: 10px;">
                                        <i class="fas fa-plus" style="color: #d4af37;"></i> Add Admin
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-users-cog" style="color: #d4af37;"></i> Admin List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT * FROM admin_login");
                                while($row = $res->fetch_assoc()) {
                                    $is_suspended = isset($row['is_suspended']) && $row['is_suspended'] == 1;
                                    echo "<tr" . ($is_suspended ? " class='table-secondary'" : "") . ">";
                                    echo "<td>" . $row['admin_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['admin_username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                    echo "<td>";
                                    if ($is_suspended) {
                                        echo "<span class='badge badge-danger'>Suspended</span>";
                                    } else {
                                        echo "<span class='badge badge-success'>Active</span>";
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                    
                                    // Edit button: super admin can edit all, others can only edit themselves
                                    if ($is_super_admin || $row['admin_username'] === $_SESSION['username']) {
                                        echo "<a href='manage_admins.php?edit=" . $row['admin_id'] . "' class='btn btn-sm btn-light mr-1' title='Edit'><i class='fas fa-edit'></i></a>";
                                    }
                                    
                                    // Super admin actions (only for non-super-admin accounts)
                                    if ($is_super_admin && $row['admin_id'] != 1) {
                                        // Suspend/Unsuspend button
                                        if ($is_suspended) {
                                            echo "<form method='POST' style='display:inline;'>";
                                            csrf_field();
                                            echo "<input type='hidden' name='admin_id' value='" . $row['admin_id'] . "'>";
                                            echo "<button type='submit' name='action' value='unsuspend' class='btn btn-sm btn-success mr-1' title='Unsuspend'><i class='fas fa-check-circle'></i></button>";
                                            echo "</form>";
                                        } else {
                                            echo "<form method='POST' style='display:inline;'>";
                                            csrf_field();
                                            echo "<input type='hidden' name='admin_id' value='" . $row['admin_id'] . "'>";
                                            echo "<button type='submit' name='action' value='suspend' class='btn btn-sm btn-warning mr-1' title='Suspend'><i class='fas fa-ban'></i></button>";
                                            echo "</form>";
                                        }
                                        
                                        // Delete button
                                        echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this admin?');\">";
                                        csrf_field();
                                        echo "<input type='hidden' name='admin_id' value='" . $row['admin_id'] . "'>";
                                        echo "<button type='submit' name='action' value='delete' class='btn btn-sm btn-danger' title='Delete'><i class='fas fa-trash'></i></button>";
                                        echo "</form>";
                                    }
                                    
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    $('#datatable').DataTable();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
