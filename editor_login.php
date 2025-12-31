<?php
include 'db.php';
include 'includes/header.php';

$error_msg = "";

// If already logged in as editor, redirect
if(isset($_SESSION['role']) && $_SESSION['role'] == 'editor') {
    header("Location: editor_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editor_id = trim($_POST['editor_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($editor_id) || empty($password)) {
        $error_msg = "All fields are required.";
    } else {
        // Use prepared statements
        $stmt = $conn->prepare("SELECT * FROM editors_table WHERE editor_id = ?");
        $stmt->bind_param("s", $editor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Verify hashed password
            if (password_verify($password, $row['editor_password'])) {
                $_SESSION['editor_id'] = $row['editor_id'];
                $_SESSION['editor_name'] = $row['editor_name'];
                $_SESSION['role'] = 'editor';
                
                // Regenerate session ID
                session_regenerate_id(true);
                
                header("Location: editor_dashboard.php");
                exit();
            } else {
                $error_msg = "Invalid Editor ID or Password";
            }
        } else {
            $error_msg = "Invalid Editor ID or Password";
        }
        $stmt->close();
    }
}
?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h3><i class="fas fa-user-edit"></i> Editor Login</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-pen-fancy fa-4x text-primary"></i>
                    </div>
                    
                    <?php if($error_msg): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label><i class="fas fa-id-badge"></i> Editor ID</label>
                            <input type="text" 
                                   name="editor_id" 
                                   class="form-control form-control-lg" 
                                   placeholder="Enter your Editor ID"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Password</label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control form-control-lg" 
                                   placeholder="Enter your password"
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <a href="admin_login.php" class="text-muted">
                        <i class="fas fa-user-shield"></i> Admin Login
                    </a>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
