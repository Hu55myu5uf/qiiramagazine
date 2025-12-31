<?php
include 'db.php';
include 'includes/header.php';

// Access Control - Admin only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action == "add") {
        $title = trim($_POST['title'] ?? '');
        $issue = trim($_POST['issue'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 9.99);
        $buy_link = trim($_POST['buy_link'] ?? '');
        
        // Handle image upload
        $image = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "images/magazines/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if(in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = $target_file;
                }
            }
        }
        
        if(!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO magazines (title, issue, description, price, image, buy_link) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdss", $title, $issue, $description, $price, $image, $buy_link);
            
            if($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Magazine added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error adding magazine.</div>";
            }
            $stmt->close();
        }
        
    } elseif ($action == "delete") {
        $id = intval($_POST['id'] ?? 0);
        
        if($id > 0) {
            $stmt = $conn->prepare("DELETE FROM magazines WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute()) {
                $message = "<div class='alert alert-warning'><i class='fas fa-trash'></i> Magazine deleted.</div>";
            }
            $stmt->close();
        }
    }
}

// Get all magazines
$magazines = $conn->query("SELECT * FROM magazines ORDER BY created_at DESC");
?>

<div class="container-fluid" style="margin-top: 20px;">
    <div class="row">
        <!-- Add Magazine Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-plus"></i> Add New Magazine</h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Magazine Title" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-hashtag"></i> Issue</label>
                            <input type="text" name="issue" class="form-control" placeholder="e.g. Issue #1">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Price</label>
                            <input type="number" name="price" class="form-control" step="0.01" value="9.99">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Cover Image</label>
                            <input type="file" name="image" class="form-control-file">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-link"></i> Buy Link (optional)</label>
                            <input type="url" name="buy_link" class="form-control" placeholder="https://...">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea>
                        </div>
                        
                        <button type="submit" name="action" value="add" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-plus"></i> Add Magazine
                        </button>
                    </form>
                </div>
            </div>
            <br>
            <a href="manage_posts.php"><i class="fas fa-arrow-left"></i> Back to Posts</a>
        </div>
        
        <!-- Magazines List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-newspaper"></i> All Magazines</h4>
                </div>
                <div class="card-body">
                    <?php if($magazines && $magazines->num_rows > 0): ?>
                    <div class="row">
                        <?php while($mag = $magazines->fetch_assoc()): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <?php if(!empty($mag['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($mag['image']); ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="fas fa-newspaper fa-3x text-white"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body text-center p-2">
                                    <h6><?php echo htmlspecialchars($mag['title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($mag['issue']); ?></small>
                                    <p class="text-success mb-1">$<?php echo number_format($mag['price'], 2); ?></p>
                                </div>
                                <div class="card-footer p-2">
                                    <form method="POST" onsubmit="return confirm('Delete this magazine?');">
                                        <input type="hidden" name="id" value="<?php echo $mag['id']; ?>">
                                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger btn-block">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No magazines yet. Add your first magazine!
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
