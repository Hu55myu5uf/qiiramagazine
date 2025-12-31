<?php
include 'db.php';
include 'includes/header.php';

// Access Control - Editor only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'editor') {
    header("Location: editor_login.php");
    exit();
}

$message = "";
$editor_id = $_SESSION['editor_id'];
$editor_name = $_SESSION['editor_name'];

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action == "add") {
        $post_title = trim($_POST['post_title'] ?? '');
        $post_description = trim($_POST['post_description'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        
        // Handle image upload
        $post_image = '';
        if(isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
            $target_dir = "images/posts/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if(in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($_FILES['post_image']['tmp_name'], $target_file)) {
                    $post_image = $target_file;
                }
            }
        }
        
        if(!empty($post_title) && !empty($post_description)) {
            $stmt = $conn->prepare("INSERT INTO post_table (post_title, post_description, post_image, author_id, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $post_title, $post_description, $post_image, $editor_id, $category);
            
            if($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Post created successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error creating post.</div>";
            }
            $stmt->close();
        }
        
    } elseif ($action == "delete") {
        $post_id = intval($_POST['post_id'] ?? 0);
        
        // Only delete own posts
        if($post_id > 0) {
            $stmt = $conn->prepare("DELETE FROM post_table WHERE post_id = ? AND author_id = ?");
            $stmt->bind_param("is", $post_id, $editor_id);
            
            if($stmt->execute() && $stmt->affected_rows > 0) {
                $message = "<div class='alert alert-warning'><i class='fas fa-trash'></i> Post deleted.</div>";
            }
            $stmt->close();
        }
    }
}

// Get categories
$categories = [];
$cat_result = $conn->query("SELECT * FROM categories ORDER BY category_name");
if($cat_result) {
    while($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// Get editor's posts
$posts_stmt = $conn->prepare("SELECT * FROM post_table WHERE author_id = ? ORDER BY post_date DESC");
$posts_stmt->bind_param("s", $editor_id);
$posts_stmt->execute();
$my_posts = $posts_stmt->get_result();
?>

<div class="container-fluid" style="margin-top: 20px;">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2><i class="fas fa-user-edit"></i> Welcome, <?php echo htmlspecialchars($editor_name); ?>!</h2>
                            <p class="mb-0">Manage your articles and create new content.</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="logout.php" class="btn btn-light btn-lg">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Create Post Form -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-plus"></i> Create New Post</h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Post Title</label>
                            <input type="text" name="post_title" class="form-control" placeholder="Enter post title" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-folder"></i> Category</label>
                            <select name="category" class="form-control">
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category_slug']); ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Post Image</label>
                            <input type="file" name="post_image" class="form-control-file">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Content</label>
                            <textarea name="post_description" class="form-control" rows="6" placeholder="Write your article..." required></textarea>
                        </div>
                        
                        <button type="submit" name="action" value="add" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-paper-plane"></i> Publish Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- My Posts -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-newspaper"></i> My Posts</h4>
                </div>
                <div class="card-body">
                    <?php if($my_posts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Likes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($post = $my_posts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(substr($post['post_title'], 0, 30)); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($post['category'] ?? 'general'); ?></span></td>
                                        <td><?php echo date("M d", strtotime($post['post_date'])); ?></td>
                                        <td><span class="badge badge-primary"><?php echo $post['post_likes']; ?></span></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You haven't created any posts yet. Start writing!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
