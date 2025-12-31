<?php
include 'db.php';
include 'includes/header.php';

$success_msg = "";
$error_msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $error_msg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        
        if ($stmt->execute()) {
            $success_msg = "Thank you for your message! We will get back to you soon.";
            // Clear form values on success
            $name = $email = $message = "";
        } else {
            $error_msg = "Sorry, there was an error sending your message. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h3 class="mb-0"><i class="fas fa-envelope"></i> Contact Us</h3>
                </div>
                <div class="card-body">
                    <?php if($success_msg): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error_msg): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i> Your Name</label>
                            <input type="text" 
                                   id="name"
                                   name="name" 
                                   class="form-control" 
                                   placeholder="Enter your full name"
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" 
                                   id="email"
                                   name="email" 
                                   class="form-control" 
                                   placeholder="Enter your email address"
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message"><i class="fas fa-comment"></i> Your Message</label>
                            <textarea id="message"
                                      name="message" 
                                      class="form-control" 
                                      rows="5" 
                                      placeholder="Write your message here..."
                                      required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h5><i class="fas fa-info-circle"></i> Other Ways to Reach Us</h5>
                    <hr>
                    <p><i class="fas fa-map-marker-alt text-danger"></i> <strong>Address:</strong> Qiira Company Limited</p>
                    <p><i class="fas fa-phone text-success"></i> <strong>Phone:</strong> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope text-primary"></i> <strong>Email:</strong> info@qiiramagazine.com</p>
                </div>
            </div>
            
            <br>
            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
