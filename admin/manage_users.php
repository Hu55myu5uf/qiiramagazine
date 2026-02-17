<?php
include __DIR__ . '/../db.php';
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

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token()) {
        $message = "<div class='alert alert-danger'>Invalid session. Please refresh and try again.</div>";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action == "update") {
        $user_id = intval($_POST['user_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if($user_id > 0 && !empty($full_name) && !empty($email)) {
            // Check email uniqueness (excluding current user)
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message = "<div class='alert alert-danger'>Email already in use by another user.</div>";
            } else {
                if(!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("sssi", $full_name, $email, $hashed_password, $user_id);
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("ssi", $full_name, $email, $user_id);
                    }
                }

                if (!$stmt) {
                    $message = "<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
                } else {
                    if($stmt->execute()) {
                        $message = "<div class='alert alert-info'>User Updated Successfully.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Error updating user: " . htmlspecialchars($stmt->error) . "</div>";
                    }
                    $stmt->close();
                }
            }
            $check_stmt->close();
        } else {
            $message = "<div class='alert alert-warning'>Full name and email are required.</div>";
        }
        } elseif ($action == "delete") {
            $user_id = intval($_POST['user_id'] ?? 0);
            if($user_id > 0) {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if($stmt->execute()) {
                    $message = "<div class='alert alert-warning'>User Deleted.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error deleting user: " . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            }
        } elseif ($action == "suspend") {
            $user_id = intval($_POST['user_id'] ?? 0);
            if($user_id > 0) {
                $stmt = $conn->prepare("UPDATE users SET is_suspended = 1 WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if($stmt->execute()) {
                    $message = "<div class='alert alert-warning'>User Suspended.</div>";
                }
                $stmt->close();
            }
        } elseif ($action == "unsuspend") {
            $user_id = intval($_POST['user_id'] ?? 0);
            if($user_id > 0) {
                $stmt = $conn->prepare("UPDATE users SET is_suspended = 0 WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if($stmt->execute()) {
                    $message = "<div class='alert alert-success'>User Unsuspended.</div>";
                }
                $stmt->close();
            }
        }
    }
}

// Check for edit mode
$user_to_edit = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0) {
        $user_to_edit = $res->fetch_assoc();
    }
    $stmt->close();
}

// Get counts
$total_users = 0;
$active_users = 0;
$suspended_users = 0;
$count_res = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_suspended = 0 THEN 1 ELSE 0 END) as active, SUM(CASE WHEN is_suspended = 1 THEN 1 ELSE 0 END) as suspended FROM users");
if ($count_res) {
    $counts = $count_res->fetch_assoc();
    $total_users = $counts['total'] ?? 0;
    $active_users = $counts['active'] ?? 0;
    $suspended_users = $counts['suspended'] ?? 0;
}
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow border-0 text-center" style="border-radius: 15px; background: #111;">
                <div class="card-body py-3">
                    <h5 class="mb-1" style="color: #d4af37;"><i class="fas fa-users"></i> Total Users</h5>
                    <h2 class="mb-0 text-white"><?php echo $total_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow border-0 text-center" style="border-radius: 15px; background: #111;">
                <div class="card-body py-3">
                    <h5 class="mb-1 text-success"><i class="fas fa-check-circle"></i> Active</h5>
                    <h2 class="mb-0 text-white"><?php echo $active_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow border-0 text-center" style="border-radius: 15px; background: #111;">
                <div class="card-body py-3">
                    <h5 class="mb-1 text-danger"><i class="fas fa-ban"></i> Suspended</h5>
                    <h2 class="mb-0 text-white"><?php echo $suspended_users; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Edit Form (only shown when editing) -->
        <?php if($user_to_edit): ?>
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit" style="color: #d4af37;"></i> Edit User
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; $message = ""; ?>

                    <form method="POST">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="user_id" value="<?php echo $user_to_edit['id']; ?>">

                        <div class="form-group">
                            <label class="font-weight-bold">Full Name</label>
                            <input type="text"
                                   name="full_name"
                                   class="form-control form-control-lg"
                                   placeholder="Enter full name"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($user_to_edit['full_name']); ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control form-control-lg"
                                   placeholder="Enter email"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($user_to_edit['email']); ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">New Password</label>
                            <div class="input-group">
                                <input type="password"
                                       name="new_password"
                                       id="new_password"
                                       class="form-control form-control-lg border-right-0"
                                       style="border-radius: 10px 0 0 10px;"
                                       placeholder="Leave blank to keep current">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white" style="border-radius: 0 10px 10px 0; cursor: pointer;" onclick="togglePassword('new_password', 'toggleIcon')">
                                        <i class="fas fa-eye text-muted" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-muted">Registered</label>
                            <input type="text"
                                   class="form-control"
                                   style="border-radius: 10px;"
                                   value="<?php echo date('M d, Y h:i A', strtotime($user_to_edit['created_at'])); ?>"
                                   readonly>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="submit" name="action" value="update" class="btn btn-block btn-lg" style="background: #d4af37; color: #000; border-radius: 10px;">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                            <div class="col-6">
                                <a href="manage_users.php" class="btn btn-dark btn-block btn-lg" style="border-radius: 10px;">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- User List -->
        <div class="<?php echo $user_to_edit ? 'col-xl-8 col-lg-7' : 'col-12'; ?> col-md-12">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-users" style="color: #d4af37;"></i> Registered Users</h4>
                    <a href="send_email.php?all=1" class="btn btn-sm" style="background: #d4af37; color: #000; border-radius: 10px;">
                        <i class="fas fa-envelope mr-1"></i> Email All Users
                    </a>
                </div>
                <div class="card-body">
                    <?php if(!empty($message)) echo $message; ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
                                if ($res) {
                                    while($row = $res->fetch_assoc()) {
                                        $is_suspended = isset($row['is_suspended']) && $row['is_suspended'] == 1;
                                        echo "<tr" . ($is_suspended ? " class='table-secondary'" : "") . ">";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>";
                                        if ($is_suspended) {
                                            echo "<span class='badge badge-danger'>Suspended</span>";
                                        } else {
                                            echo "<span class='badge badge-success'>Active</span>";
                                        }
                                        echo "</td>";
                                        echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                        echo "<td>";

                                        // Edit button
                                        echo "<a href='manage_users.php?edit=" . $row['id'] . "' class='btn btn-sm btn-light mr-1' title='Edit'><i class='fas fa-edit'></i></a>";

                                        // View Purchases button
                                        echo "<a href='user_purchases.php?user_id=" . $row['id'] . "' class='btn btn-sm btn-info mr-1' title='View Purchases'><i class='fas fa-shopping-bag'></i></a>";

                                        // Send Email button
                                        echo "<a href='send_email.php?user_id=" . $row['id'] . "' class='btn btn-sm btn-primary mr-1' title='Send Email'><i class='fas fa-envelope'></i></a>";

                                        // Suspend/Unsuspend button
                                        if ($is_suspended) {
                                            echo "<form method='POST' style='display:inline;'>";
                                            csrf_field();
                                            echo "<input type='hidden' name='user_id' value='" . $row['id'] . "'>";
                                            echo "<button type='submit' name='action' value='unsuspend' class='btn btn-sm btn-success mr-1' title='Unsuspend'><i class='fas fa-check-circle'></i></button>";
                                            echo "</form>";
                                        } else {
                                            echo "<form method='POST' style='display:inline;'>";
                                            csrf_field();
                                            echo "<input type='hidden' name='user_id' value='" . $row['id'] . "'>";
                                            echo "<button type='submit' name='action' value='suspend' class='btn btn-sm btn-warning mr-1' title='Suspend'><i class='fas fa-ban'></i></button>";
                                            echo "</form>";
                                        }

                                        // Delete button
                                        echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this user? This will also remove their purchase records.');\">";
                                        csrf_field();
                                        echo "<input type='hidden' name='user_id' value='" . $row['id'] . "'>";
                                        echo "<button type='submit' name='action' value='delete' class='btn btn-sm btn-danger' title='Delete'><i class='fas fa-trash'></i></button>";
                                        echo "</form>";

                                        echo "</td>";
                                        echo "</tr>";
                                    }
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
    $('#datatable').DataTable({
        "order": [[0, "desc"]]
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
