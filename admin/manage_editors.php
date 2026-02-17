<?php
include __DIR__ . '/../includes/csrf.php';

// Start session if header is moved
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include __DIR__ . '/../includes/header.php';

$message = "";

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token()) {
        $message = "<div class='alert alert-danger'>Invalid session. Please refresh and try again.</div>";
    } else {
        $username = trim($_POST['username'] ?? '');
    $editor_name = trim($_POST['editor_name'] ?? '');
    $editor_email = trim($_POST['editor_email'] ?? '');
    $editor_password = trim($_POST['editor_password'] ?? '');
    $action = $_POST['action'] ?? '';

    if ($action == "add") {
        if(!empty($username) && !empty($editor_name) && !empty($editor_password)) {
             // Check if Username exists
             $check_stmt = $conn->prepare("SELECT * FROM editors_table WHERE username = ?");
             if (!$check_stmt) {
                 $message = "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
             } else {
                 $check_stmt->bind_param("s", $username);
                 $check_stmt->execute();
                 $check = $check_stmt->get_result();
                 if($check->num_rows > 0) {
                     $message = "<div class='alert alert-danger'>Username already exists.</div>";
                 } else {
                     $hashed_password = password_hash($editor_password, PASSWORD_DEFAULT);
                     $stmt = $conn->prepare("INSERT INTO editors_table (username, editor_name, editor_email, editor_password, is_first_login) VALUES (?, ?, ?, ?, 1)");
                     if (!$stmt) {
                         $message = "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
                     } else {
                         $stmt->bind_param("ssss", $username, $editor_name, $editor_email, $hashed_password);
                         if($stmt->execute()) {
                             $message = "<div class='alert alert-success'>Editor Added Successfully. Password update will be required on first login.</div>";
                         } else {
                             $message = "<div class='alert alert-danger'>Error Adding Editor: " . htmlspecialchars($stmt->error) . "</div>";
                         }
                         $stmt->close();
                     }
                 }
                 $check_stmt->close();
             }
        } else {
            $message = "<div class='alert alert-warning'>Username, Name and Password are required.</div>";
        }
    } elseif ($action == "update") {
        if(!empty($username)) {
             if(!empty($editor_name)) {
                if(!empty($editor_password)) {
                    $hashed_password = password_hash($editor_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE editors_table SET editor_name = ?, editor_email = ?, editor_password = ? WHERE username = ?");
                    if ($stmt) {
                        $stmt->bind_param("ssss", $editor_name, $editor_email, $hashed_password, $username);
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE editors_table SET editor_name = ?, editor_email = ? WHERE username = ?");
                    if ($stmt) {
                        $stmt->bind_param("sss", $editor_name, $editor_email, $username);
                    }
                }
                
                if (!$stmt) {
                    $message = "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
                } else {
                    if($stmt->execute()) {
                        $message = "<div class='alert alert-info'>Editor Updated.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Error updating editor: " . htmlspecialchars($stmt->error) . "</div>";
                    }
                    $stmt->close();
                }
             } else {
                 $message = "<div class='alert alert-warning'>Editor name is required for update.</div>";
             }
        }
    } elseif ($action == "delete") {
        if(!empty($username)) {
            $stmt = $conn->prepare("DELETE FROM editors_table WHERE username = ?");
            $stmt->bind_param("s", $username);
            if($stmt->execute()) {
                $message = "<div class='alert alert-warning'>Editor Deleted.</div>";
            }
            $stmt->close();
        }
    } elseif ($action == "suspend") {
        if(!empty($username)) {
            $stmt = $conn->prepare("UPDATE editors_table SET is_suspended = 1 WHERE username = ?");
            $stmt->bind_param("s", $username);
            if($stmt->execute()) {
                $message = "<div class='alert alert-warning'>Editor Suspended.</div>";
            }
            $stmt->close();
        }
    } elseif ($action == "unsuspend") {
        if(!empty($username)) {
            $stmt = $conn->prepare("UPDATE editors_table SET is_suspended = 0 WHERE username = ?");
            $stmt->bind_param("s", $username);
            if($stmt->execute()) {
                $message = "<div class='alert alert-success'>Editor Unsuspended.</div>";
            }
            $stmt->close();
        }
    }
}
}

// Check for edit mode
$editor_to_edit = null;
if(isset($_GET['edit'])) {
    $e_username = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM editors_table WHERE username = ?");
    $stmt->bind_param("s", $e_username);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0) {
        $editor_to_edit = $res->fetch_assoc();
    }
}
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit" style="color: #d4af37;"></i>
                        <?php echo $editor_to_edit ? 'Update Editor' : 'Add New Editor'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>

                    <form method="POST">
                        <?php csrf_field(); ?>
                        <div class="form-group">
                            <label class="font-weight-bold">Username</label>
                            <input type="text" 
                                   name="username" 
                                   class="form-control form-control-lg" 
                                   placeholder="e.g. editor123" 
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($editor_to_edit['username'] ?? ''); ?>"
                                   <?php echo $editor_to_edit ? 'readonly' : ''; ?>
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Full Name</label>
                            <input type="text" 
                                   name="editor_name" 
                                   class="form-control form-control-lg" 
                                   placeholder="Enter full name"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($editor_to_edit['editor_name'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Email Address</label>
                            <input type="email" 
                                   name="editor_email" 
                                   class="form-control form-control-lg" 
                                   placeholder="e.g. editor@example.com"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($editor_to_edit['editor_email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="editor_password" 
                                       id="editor_password"
                                       class="form-control form-control-lg border-right-0" 
                                       style="border-radius: 10px 0 0 10px;"
                                       placeholder="<?php echo $editor_to_edit ? 'Leave blank to keep current' : 'Enter password'; ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white" style="border-radius: 0 10px 10px 0; cursor: pointer;" onclick="togglePassword('editor_password', 'toggleIcon')">
                                        <i class="fas fa-eye text-muted" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <?php if($editor_to_edit): ?>
                                <div class="col-6">
                                    <button type="submit" name="action" value="update" class="btn btn-block btn-lg" style="background: #d4af37; color: #000; border-radius: 10px;">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="manage_editors.php" class="btn btn-dark btn-block btn-lg" style="border-radius: 10px;">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <button type="submit" name="action" value="add" class="btn btn-block btn-lg" style="background: #000; color: #fff; border-radius: 10px;">
                                        <i class="fas fa-plus" style="color: #d4af37;"></i> Add Editor
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
                    <h4 class="mb-0"><i class="fas fa-users" style="color: #d4af37;"></i> Editor List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT * FROM editors_table");
                                while($row = $res->fetch_assoc()) {
                                    $is_suspended = isset($row['is_suspended']) && $row['is_suspended'] == 1;
                                    echo "<tr" . ($is_suspended ? " class='table-secondary'" : "") . ">";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['editor_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['editor_email'] ?? 'N/A') . "</td>";
                                    echo "<td>";
                                    if ($is_suspended) {
                                        echo "<span class='badge badge-danger'>Suspended</span>";
                                    } elseif ($row['is_first_login']) {
                                        echo "<span class='badge' style='background: #f39c12; color: #fff;'>Pending Pwd Update</span>";
                                    } else {
                                        echo "<span class='badge' style='background: #d4af37; color: #000;'>Active</span>";
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                    // Edit button
                                    echo "<a href='manage_editors.php?edit=" . urlencode($row['username']) . "' class='btn btn-sm btn-light mr-1' title='Edit'><i class='fas fa-edit'></i></a>";
                                    // Suspend/Unsuspend button
                                    if ($is_suspended) {
                                        echo "<form method='POST' style='display:inline;'>";
                                        csrf_field();
                                        echo "<input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>";
                                        echo "<button type='submit' name='action' value='unsuspend' class='btn btn-sm btn-success mr-1' title='Unsuspend'><i class='fas fa-check-circle'></i></button>";
                                        echo "</form>";
                                    } else {
                                        echo "<form method='POST' style='display:inline;'>";
                                        csrf_field();
                                        echo "<input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>";
                                        echo "<button type='submit' name='action' value='suspend' class='btn btn-sm btn-warning mr-1' title='Suspend'><i class='fas fa-ban'></i></button>";
                                        echo "</form>";
                                    }
                                    // Delete form
                                    echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this editor?');\">";
                                    csrf_field();
                                    echo "<input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>";
                                    echo "<button type='submit' name='action' value='delete' class='btn btn-sm btn-danger' title='Delete'><i class='fas fa-trash'></i></button>";
                                    echo "</form>";
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



