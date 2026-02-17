<?php
include __DIR__ . '/../includes/csrf.php';

// Start session if header is moved
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control
if(!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'editor')) {
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
    
        if ($action == "add") {
        $post_title = trim($_POST['post_title'] ?? '');
        $post_description = trim($_POST['post_description'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $author_id = ($_SESSION['role'] == 'admin') ? $_SESSION['username'] : $_SESSION['editor_username'];
        
        // Handle image upload
        $post_image = '';
        if(isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
            $target_dir = "../images/posts/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if(in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                $db_path = "images/posts/" . $new_filename; // Path to store in DB
                
                if(move_uploaded_file($_FILES['post_image']['tmp_name'], $target_file)) {
                    $post_image = $db_path;
                }
            } else {
                $message = "<div class='alert alert-danger'>Invalid image format. Allowed: jpg, jpeg, png, gif, webp</div>";
            }
        }
        
        if(!empty($post_title) && !empty($post_description) && empty($message)) {
            $stmt = $conn->prepare("INSERT INTO post_table (post_title, post_description, post_image, author_id, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $post_title, $post_description, $post_image, $author_id, $category);
            
            if($stmt->execute()) {
                $message = "<div class='alert alert-success'>Post created successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error creating post.</div>";
            }
            $stmt->close();
        } elseif(empty($message)) {
            $message = "<div class='alert alert-warning'>Title and description are required.</div>";
        }
        
    } elseif ($action == "update") {
        $post_id = intval($_POST['post_id'] ?? 0);
        $post_title = trim($_POST['post_title'] ?? '');
        $post_description = trim($_POST['post_description'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        
        if($post_id > 0 && !empty($post_title) && !empty($post_description)) {
            // Check for new image upload
            if(isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
                $target_dir = "../images/posts/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if(in_array($file_extension, $allowed_extensions)) {
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    $db_path = "images/posts/" . $new_filename;
                    
                    if(move_uploaded_file($_FILES['post_image']['tmp_name'], $target_file)) {
                        $stmt = $conn->prepare("UPDATE post_table SET post_title = ?, post_description = ?, post_image = ?, category = ? WHERE post_id = ?");
                        $stmt->bind_param("ssssi", $post_title, $post_description, $db_path, $category, $post_id);
                    }
                }
            } else {
                $stmt = $conn->prepare("UPDATE post_table SET post_title = ?, post_description = ?, category = ? WHERE post_id = ?");
                $stmt->bind_param("sssi", $post_title, $post_description, $category, $post_id);
            }
            
            if(isset($stmt) && $stmt->execute()) {
                $message = "<div class='alert alert-info'>Post updated successfully!</div>";
                $stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>Error updating post.</div>";
            }
        }
        
    } elseif ($action == "delete") {
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if($post_id > 0) {
            $stmt = $conn->prepare("DELETE FROM post_table WHERE post_id = ?");
            $stmt->bind_param("i", $post_id);
            
            if($stmt->execute()) {
                $message = "<div class='alert alert-warning'>Post deleted.</div>";
            }
            $stmt->close();
            }
            $stmt->close();
        }
    }
}

// Get categories for dropdown
$categories = [];
$cat_result = $conn->query("SELECT * FROM categories ORDER BY category_name");
if($cat_result) {
    while($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// Get post for editing if edit_id is provided
$edit_post = null;
if(isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $edit_id = intval($_GET['edit']);
    $edit_stmt = $conn->prepare("SELECT * FROM post_table WHERE post_id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    if($edit_result->num_rows > 0) {
        $edit_post = $edit_result->fetch_assoc();
    }
    $edit_stmt->close();
}
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="row">
        <!-- Post Form -->
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-edit" style="color: #d4af37;"></i> 
                        <?php echo $edit_post ? 'Edit Post' : 'Create New Post'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <?php csrf_field(); ?>
                        <?php if($edit_post): ?>
                            <input type="hidden" name="post_id" value="<?php echo $edit_post['post_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Post Title</label>
                            <input type="text" 
                                   name="post_title" 
                                   class="form-control form-control-lg" 
                                   placeholder="Enter post title"
                                   value="<?php echo htmlspecialchars($edit_post['post_title'] ?? ''); ?>"
                                   style="border-radius: 10px;"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Category</label>
                            <select name="category" class="form-control form-control-lg" style="border-radius: 10px;">
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category_slug']); ?>"
                                            <?php echo (($edit_post['category'] ?? '') == $cat['category_slug']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Post Image</label>
                            <div class="custom-file">
                                <input type="file" name="post_image" class="custom-file-input" id="customFile">
                                <label class="custom-file-label" for="customFile" style="border-radius: 10px;">Choose file...</label>
                            </div>
                            <?php if($edit_post && !empty($edit_post['post_image'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Current:</small><br>
                                    <img src="<?php echo htmlspecialchars($edit_post['post_image']); ?>" height="60" style="border-radius: 5px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Post Content</label>
                            <textarea name="post_description" 
                                      class="form-control" 
                                      rows="8" 
                                      placeholder="Write your post content here..."
                                      style="border-radius: 10px;"
                                      required><?php echo htmlspecialchars($edit_post['post_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <?php if($edit_post): ?>
                                <div class="col-6">
                                    <button type="submit" name="action" value="update" class="btn btn-block btn-lg" style="background: #d4af37; color: #000; border-radius: 10px;">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="manage_posts.php" class="btn btn-dark btn-block btn-lg" style="border-radius: 10px;">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <button type="submit" name="action" value="add" class="btn btn-block btn-lg" style="background: #000; color: #fff; border-radius: 10px;">
                                        <i class="fas fa-plus" style="color: #d4af37;"></i> Create Post
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
        
        <!-- Posts List -->
        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-list" style="color: #d4af37;"></i> All Posts</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Likes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $posts = $conn->query("SELECT * FROM post_table ORDER BY post_date DESC");
                                while($post = $posts->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $post['post_id']; ?></td>
                                    <td><?php echo htmlspecialchars(substr($post['post_title'], 0, 40)) . (strlen($post['post_title']) > 40 ? '...' : ''); ?></td>
                                    <td><span class="badge badge-dark"><?php echo htmlspecialchars($post['category'] ?? 'general'); ?></span></td>
                                    <td><?php echo date("M d, Y", strtotime($post['post_date'])); ?></td>
                                    <td><span class="badge badge-warning" style="background: #d4af37; color: #000;"><?php echo $post['post_likes']; ?></span></td>
                                    <td>
                                        <a href="manage_posts.php?edit=<?php echo $post['post_id']; ?>" class="btn btn-sm" style="background: #d4af37; color: #000;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            <?php csrf_field(); ?>
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            "order": [[ 0, "desc" ]]
        });

        // Display filename on selection
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>



