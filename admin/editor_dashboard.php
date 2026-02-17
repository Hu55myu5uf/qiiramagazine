<?php
include __DIR__ . '/../includes/csrf.php';

// Start session if header is moved
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control - Editor only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'editor') {
    header("Location: editor_login.php");
    exit();
}

include __DIR__ . '/../includes/header.php';

$message = "";
$editor_username = $_SESSION['editor_username'];
$editor_name = $_SESSION['editor_name'];

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token()) {
        $message = "<div class='alert alert-danger'>Invalid session. Please refresh and try again.</div>";
    } else {
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
            $stmt->bind_param("sssss", $post_title, $post_description, $post_image, $editor_username, $category);
            
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
            $stmt->bind_param("is", $post_id, $editor_username);
            
            if($stmt->execute() && $stmt->affected_rows > 0) {
                $message = "<div class='alert alert-warning'><i class='fas fa-trash'></i> Post deleted.</div>";
            }
            $stmt->close();
        }
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
$posts_stmt->bind_param("s", $editor_username);
$posts_stmt->execute();
$my_posts = $posts_stmt->get_result();
?>

<div class="container-fluid" style="margin-top: 30px; margin-bottom: 50px;">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg border-0" style="background: #000; color: #fff; border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h2 class="font-weight-bold">
                                <i class="fas fa-user-edit" style="color: #d4af37;"></i> 
                                Welcome, <span style="color: #d4af37;"><?php echo htmlspecialchars($editor_name); ?></span>!
                            </h2>
                            <p class="mb-0 text-white-50">Manage your articles and create new content for Qiira Magazine.</p>
                        </div>
                        <div class="col-md-3 text-right">
                            <a href="../logout.php" class="btn btn-lg btn-block" style="background: #d4af37; color: #000; border-radius: 10px; font-weight: bold;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow border-0 h-100" style="border-radius: 15px; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body text-center p-4">
                    <div style="width: 60px; height: 60px; background: #000; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fas fa-newspaper fa-2x" style="color: #d4af37;"></i>
                    </div>
                    <h5 class="font-weight-bold">Manage Posts</h5>
                    <p class="text-muted small">Create, edit and delete magazine posts</p>
                    <a href="manage_posts.php" class="btn btn-dark btn-sm px-4" style="border-radius: 20px;">Go to Posts</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow border-0 h-100" style="border-radius: 15px; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body text-center p-4">
                    <div style="width: 60px; height: 60px; background: #000; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fas fa-image fa-2x" style="color: #d4af37;"></i>
                    </div>
                    <h5 class="font-weight-bold">Hero Section</h5>
                    <p class="text-muted small">Update the homepage slider images</p>
                    <a href="hero_section.php" class="btn btn-dark btn-sm px-4" style="border-radius: 20px;">Edit Hero</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow border-0 h-100" style="border-radius: 15px; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body text-center p-4">
                    <div style="width: 60px; height: 60px; background: #000; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fas fa-book-open fa-2x" style="color: #d4af37;"></i>
                    </div>
                    <h5 class="font-weight-bold">Magazines</h5>
                    <p class="text-muted small">Manage magazine issues and PDFs</p>
                    <a href="manage_magazines.php" class="btn btn-dark btn-sm px-4" style="border-radius: 20px;">View Magazines</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Create Post Form -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-plus" style="color: #d4af37;"></i> Create New Post
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <?php csrf_field(); ?>
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-heading" style="color: #d4af37;"></i> Post Title</label>
                            <input type="text" name="post_title" class="form-control form-control-lg" placeholder="Enter post title" style="border-radius: 10px;" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-folder" style="color: #d4af37;"></i> Category</label>
                            <select name="category" class="form-control form-control-lg" style="border-radius: 10px;">
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category_slug']); ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-image" style="color: #d4af37;"></i> Post Image</label>
                            <div class="custom-file">
                                <input type="file" name="post_image" class="custom-file-input" id="postImage">
                                <label class="custom-file-label" for="postImage" style="border-radius: 10px;">Choose file...</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-align-left" style="color: #d4af37;"></i> Content</label>
                            <textarea name="post_description" class="form-control" rows="6" placeholder="Write your article..." style="border-radius: 10px;" required></textarea>
                        </div>
                        
                        <button type="submit" name="action" value="add" class="btn btn-block btn-lg" style="background: #000; color: #fff; border-radius: 10px;">
                            <i class="fas fa-paper-plane" style="color: #d4af37;"></i> Publish Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- My Posts -->
        <div class="col-md-7">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-newspaper" style="color: #d4af37;"></i> My Posts
                    </h4>
                </div>
                <div class="card-body">
                    <?php if($my_posts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="editorTable">
                                <thead class="thead-light">
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
                                        <td class="font-weight-bold"><?php echo htmlspecialchars(substr($post['post_title'], 0, 30)); ?>...</td>
                                        <td><span class="badge" style="background: #eee; color: #000;"><?php echo htmlspecialchars($post['category'] ?? 'general'); ?></span></td>
                                        <td><?php echo date("M d", strtotime($post['post_date'])); ?></td>
                                        <td><span class="badge" style="background: #d4af37; color: #000;"><?php echo $post['post_likes']; ?></span></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete">
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
                        <div class="text-center py-5">
                            <i class="fas fa-pencil-alt fa-3x mb-3" style="color: #d4af37;"></i>
                            <h4 class="text-muted">No posts yet</h4>
                            <p class="text-muted">You haven't created any posts yet. Start writing your first article!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editorTable').DataTable({
             "order": [[ 2, "desc" ]]
        });
        
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>



