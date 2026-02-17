<?php
include __DIR__ . '/../db.php';
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
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $button_text = trim($_POST['button_text'] ?? '');
        $button_link = trim($_POST['button_link'] ?? '');
        
        if(!empty($title) && isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if($file['error'] === 0) {
                if(in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'hero_' . time() . '_' . uniqid() . '.' . $ext;
                    $db_path = 'images/hero/' . $filename;
                    $upload_path = '../' . $db_path;
                    
                    if(move_uploaded_file($file['tmp_name'], $upload_path)) {
                        // Get max order
                        $max_order = $conn->query("SELECT MAX(display_order) as max_ord FROM hero_images")->fetch_assoc()['max_ord'] ?? 0;
                        $new_order = $max_order + 1;
                        
                        $stmt = $conn->prepare("INSERT INTO hero_images (image_path, title, subtitle, button_text, button_link, display_order) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssssi", $db_path, $title, $subtitle, $button_text, $button_link, $new_order);
                        
                        if($stmt->execute()) {
                            $message = "<div class='alert alert-success'>Hero slide added successfully!</div>";
                        } else {
                            $message = "<div class='alert alert-danger'>Error adding slide.</div>";
                        }
                        $stmt->close();
                    } else {
                        $message = "<div class='alert alert-danger'>Error uploading file.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-warning'>Invalid file type or size. Use JPG/PNG/WEBP, max 5MB.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>File upload error.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Title and image are required.</div>";
        }
    } elseif ($action == "update") {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $button_text = trim($_POST['button_text'] ?? '');
        $button_link = trim($_POST['button_link'] ?? '');
        
        if($id && !empty($title)) {
            // Check if a new image is uploaded
            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $file = $_FILES['image'];
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                $max_size = 5 * 1024 * 1024;
                
                if(in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                    // Get old image path to delete it
                    $old_stmt = $conn->prepare("SELECT image_path FROM hero_images WHERE id = ?");
                    $old_stmt->bind_param("i", $id);
                    $old_stmt->execute();
                    $old_res = $old_stmt->get_result();
                    if($old_row = $old_res->fetch_assoc()) {
                        if(file_exists('../' . $old_row['image_path']) && strpos($old_row['image_path'], 'images/hero/') === 0) {
                            unlink('../' . $old_row['image_path']);
                        }
                    }
                    $old_stmt->close();

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'hero_' . time() . '_' . uniqid() . '.' . $ext;
                    $db_path = 'images/hero/' . $filename;
                    $upload_path = '../' . $db_path;

                    if(move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $stmt = $conn->prepare("UPDATE hero_images SET image_path = ?, title = ?, subtitle = ?, button_text = ?, button_link = ? WHERE id = ?");
                        $stmt->bind_param("sssssi", $db_path, $title, $subtitle, $button_text, $button_link, $id);
                        if($stmt->execute()) {
                            $message = "<div class='alert alert-info'>Hero slide updated with new image.</div>";
                        }
                        $stmt->close();
                    }
                } else {
                    $message = "<div class='alert alert-warning'>Invalid image. Text only updated.</div>";
                }
            } else {
                // Just update text fields
                $stmt = $conn->prepare("UPDATE hero_images SET title = ?, subtitle = ?, button_text = ?, button_link = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $title, $subtitle, $button_text, $button_link, $id);
                if($stmt->execute()) {
                    $message = "<div class='alert alert-info'>Hero slide updated.</div>";
                }
                $stmt->close();
            }
        }
    } elseif ($action == "delete") {
        $id = intval($_POST['id'] ?? 0);
        if($id) {
            // Get image path first
            $result = $conn->query("SELECT image_path FROM hero_images WHERE id = $id");
            if($row = $result->fetch_assoc()) {
                $full_path = '../' . $row['image_path'];
                if(file_exists($full_path) && strpos($row['image_path'], 'images/') === 0) {
                    unlink($full_path);
                }
            }
            $conn->query("DELETE FROM hero_images WHERE id = $id");
            $message = "<div class='alert alert-warning'>Hero slide deleted.</div>";
        }
    } elseif ($action == "toggle_active") {
        $id = intval($_POST['id'] ?? 0);
        if($id) {
            $conn->query("UPDATE hero_images SET is_active = 1 - is_active WHERE id = $id");
            $message = "<div class='alert alert-info'>Status updated.</div>";
        }
    } elseif ($action == "move_up" || $action == "move_down") {
        $id = intval($_POST['id'] ?? 0);
        if($id) {
            $current = $conn->query("SELECT display_order FROM hero_images WHERE id = $id")->fetch_assoc();
            if($current) {
                $curr_order = $current['display_order'];
                if($action == "move_up") {
                    $swap = $conn->query("SELECT id, display_order FROM hero_images WHERE display_order < $curr_order ORDER BY display_order DESC LIMIT 1")->fetch_assoc();
                } else {
                    $swap = $conn->query("SELECT id, display_order FROM hero_images WHERE display_order > $curr_order ORDER BY display_order ASC LIMIT 1")->fetch_assoc();
                }
                
                if($swap) {
                    $conn->query("UPDATE hero_images SET display_order = {$swap['display_order']} WHERE id = $id");
                    $conn->query("UPDATE hero_images SET display_order = $curr_order WHERE id = {$swap['id']}");
                    $message = "<div class='alert alert-info'>Order updated.</div>";
                }
            }
        }
    }
    }
}

// Check for edit mode
$hero_to_edit = null;
if(isset($_GET['edit'])) {
    $e_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM hero_images WHERE id = ?");
    $stmt->bind_param("i", $e_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0) {
        $hero_to_edit = $res->fetch_assoc();
    }
}
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-image" style="color: #d4af37;"></i>
                        <?php echo $hero_to_edit ? 'Update Hero Slide' : 'Add New Hero Slide'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <?php csrf_field(); ?>
                        <?php if($hero_to_edit): ?>
                            <input type="hidden" name="id" value="<?php echo $hero_to_edit['id']; ?>">
                            <div class="mb-3">
                                <label class="font-weight-bold">Current Image</label>
                                <img src="../<?php echo htmlspecialchars($hero_to_edit['image_path']); ?>" 
                                     class="img-fluid rounded border" alt="Current Image">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Replace Image (Optional)</label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input" id="heroImage" accept="image/*">
                                    <label class="custom-file-label" for="heroImage" style="border-radius: 10px;">Choose file...</label>
                                </div>
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label class="font-weight-bold">Hero Image *</label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input" id="heroImageNew" accept="image/*" required>
                                    <label class="custom-file-label" for="heroImageNew" style="border-radius: 10px;">Choose image...</label>
                                </div>
                                <small class="text-muted">JPG, PNG, or WEBP. Max 5MB. Recommended: 1920x1080px</small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Title *</label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control form-control-lg" 
                                   placeholder="e.g. Welcome to Qiira Magazine"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($hero_to_edit['title'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Subtitle</label>
                            <textarea name="subtitle" 
                                      class="form-control" 
                                      rows="2"
                                      placeholder="Optional subtitle text"
                                      style="border-radius: 10px;"><?php echo htmlspecialchars($hero_to_edit['subtitle'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Button Text</label>
                            <input type="text" 
                                   name="button_text" 
                                   class="form-control" 
                                   placeholder="e.g. Start Reading"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($hero_to_edit['button_text'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Button Link</label>
                            <input type="text" 
                                   name="button_link" 
                                   class="form-control" 
                                   placeholder="e.g. #latest-articles or category.php"
                                   style="border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($hero_to_edit['button_link'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <?php if($hero_to_edit): ?>
                                <div class="col-6">
                                    <button type="submit" name="action" value="update" class="btn btn-block btn-lg" style="background: #d4af37; color: #000; border-radius: 10px;">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="hero_section.php" class="btn btn-dark btn-block btn-lg" style="border-radius: 10px;">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <button type="submit" name="action" value="add" class="btn btn-block btn-lg" style="background: #000; color: #fff; border-radius: 10px;">
                                        <i class="fas fa-plus" style="color: #d4af37;"></i> Add Hero Slide
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="manage_editors.php" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Admin</a>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-images" style="color: #d4af37;"></i> Hero Slides</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 100px;">Image</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT * FROM hero_images ORDER BY display_order ASC");
                                while($row = $res->fetch_assoc()) {
                                    echo "<tr>";
                                    $img_src = (strpos($row['image_path'], 'http') === 0) ? $row['image_path'] : '../' . $row['image_path'];
                                    echo "<td><img src='" . htmlspecialchars($img_src) . "' class='img-thumbnail' style='width: 80px; height: 50px; object-fit: cover;'></td>";
                                    echo "<td><strong>" . htmlspecialchars($row['title']) . "</strong><br><small class='text-muted'>" . htmlspecialchars(substr($row['subtitle'] ?? '', 0, 50)) . "...</small></td>";
                                    
                                    $status_class = $row['is_active'] ? 'success' : 'secondary';
                                    $status_text = $row['is_active'] ? 'Active' : 'Inactive';
                                    echo "<td><span class='badge badge-$status_class'>$status_text</span></td>";
                                    
                                    echo "<td>{$row['display_order']}</td>";
                                    
                                    echo "<td>";
                                    // Edit button
                                    echo "<a href='hero_section.php?edit=" . $row['id'] . "' class='btn btn-sm btn-light mr-1' title='Edit'><i class='fas fa-edit'></i></a>";
                                    
                                    // Move up/down
                                    echo "<form method='POST' style='display:inline;'>";
                                    csrf_field();
                                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                                    echo "<button type='submit' name='action' value='move_up' class='btn btn-sm btn-secondary mr-1' title='Move Up'><i class='fas fa-arrow-up'></i></button>";
                                    echo "</form>";
                                    
                                    echo "<form method='POST' style='display:inline;'>";
                                    csrf_field();
                                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                                    echo "<button type='submit' name='action' value='move_down' class='btn btn-sm btn-secondary mr-1' title='Move Down'><i class='fas fa-arrow-down'></i></button>";
                                    echo "</form>";
                                    
                                    // Toggle active
                                    echo "<form method='POST' style='display:inline;'>";
                                    csrf_field();
                                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                                    $toggle_icon = $row['is_active'] ? 'eye-slash' : 'eye';
                                    echo "<button type='submit' name='action' value='toggle_active' class='btn btn-sm btn-info mr-1' title='Toggle Status'><i class='fas fa-$toggle_icon'></i></button>";
                                    echo "</form>";
                                    
                                    // Delete form
                                    echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete this hero slide?');\">";
                                    csrf_field();
                                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
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
    // Display filename on selection
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>



