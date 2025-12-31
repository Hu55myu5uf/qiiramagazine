<?php
include 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_msg = "";

// Process login BEFORE including header (which outputs HTML)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_msg = "Fields cannot be empty";
    } else {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM admin_login WHERE admin_username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Use password_verify for secure password checking
            if (password_verify($password, $row['admin_password'])) {
                $_SESSION['username'] = $row['admin_username'];
                $_SESSION['fullname'] = $row['full_name'];
                $_SESSION['role'] = 'admin';
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                // Redirect to admin homepage
                header("Location: manage_editors.php");
                exit();
            } else {
                $error_msg = "Invalid Username or Password";
            }
        } else {
            $error_msg = "Invalid Username or Password";
        }
        $stmt->close();
    }
}

// Now include header (after any potential redirects)
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card" style="margin-top: 50px;">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <center>
                                <i class="fas fa-user-shield fa-5x text-primary mb-3"></i>
                            </center>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <center>
                                <h3>Admin Login</h3>
                            </center>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <hr>
                        </div>
                    </div>
                    <?php if($error_msg): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col">
                                <label>Admin Member ID</label>
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control" placeholder="Member ID" required>
                                </div>
                                <label>Password</label>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success btn-block btn-lg">Login</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <a href="index.php"><< Back to Home</a><br><br>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
