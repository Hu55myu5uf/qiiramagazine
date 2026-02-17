<?php
include __DIR__ . '/../includes/csrf.php';

// Start session if header is moved
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control - Admin & Editor
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
        $issue = trim($_POST['issue'] ?? '');
        $category = $_POST['category'] ?? 'paid';
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 9.99);
        $buy_link = trim($_POST['buy_link'] ?? '');
        
        // Handle image upload
        $image = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../images/magazines/"; // Fixed path to root
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if(in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                $db_image_path = "images/magazines/" . $new_filename;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = $db_image_path;
                }
            }
        }

        // Handle PDF upload
        $pdf_file = '';
        if(isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
            $target_dir = "../magazines_pdf/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
            if($file_extension == 'pdf') {
                $new_filename = uniqid() . '.pdf';
                $target_file = $target_dir . $new_filename;
                $db_pdf_path = "magazines_pdf/" . $new_filename;
                
                if(move_uploaded_file($_FILES['pdf_file']['tmp_name'], $target_file)) {
                    $pdf_file = $db_pdf_path;
                }
            }
        }
        
        if(!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO magazines (title, issue, category, description, price, image, buy_link, pdf_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssdsss", $title, $issue, $category, $description, $price, $image, $buy_link, $pdf_file);
            
            if($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Magazine added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error adding magazine.</div>";
            }
            $stmt->close();
        }
    } elseif ($action == "update") {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $issue = trim($_POST['issue'] ?? '');
        $category = $_POST['category'] ?? 'paid';
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 9.99);
        $buy_link = trim($_POST['buy_link'] ?? '');
        
        if($id > 0 && !empty($title)) {
            // Get current files
            $stmt = $conn->prepare("SELECT image, pdf_file FROM magazines WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $current = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $image = $current['image'];
            $pdf_file = $current['pdf_file'];

            // Handle image update
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = "../images/magazines/";
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                if(move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $new_filename)) {
                    $image = "images/magazines/" . $new_filename;
                }
            }

            // Handle PDF update
            if(isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
                $target_dir = "../magazines_pdf/";
                $file_extension = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
                if($file_extension == 'pdf') {
                    $new_filename = uniqid() . '.pdf';
                    if(move_uploaded_file($_FILES['pdf_file']['tmp_name'], $target_dir . $new_filename)) {
                        $pdf_file = "magazines_pdf/" . $new_filename;
                    }
                }
            }

            $stmt = $conn->prepare("UPDATE magazines SET title=?, issue=?, category=?, description=?, price=?, image=?, buy_link=?, pdf_file=? WHERE id=?");
            $stmt->bind_param("ssssdsssi", $title, $issue, $category, $description, $price, $image, $buy_link, $pdf_file, $id);
            
            if($stmt->execute()) {
                $message = "<div class='alert alert-info'><i class='fas fa-sync'></i> Magazine updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating magazine.</div>";
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
            $stmt->close();
        }
    }
}
}

// Get all magazines
$magazines = $conn->query("SELECT * FROM magazines ORDER BY created_at DESC");

// Fetch magazine for editing
$edit_mag = null;
if(isset($_GET['edit'])) {
    $e_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM magazines WHERE id = ?");
    $stmt->bind_param("i", $e_id);
    $stmt->execute();
    $edit_mag = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="row">
        <!-- Add Magazine Form -->
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas <?php echo $edit_mag ? 'fa-edit' : 'fa-plus'; ?>" style="color: #d4af37;"></i> 
                        <?php echo $edit_mag ? 'Edit Magazine' : 'Add New Magazine'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <?php csrf_field(); ?>
                        <?php if($edit_mag): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_mag['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-heading" style="color: #d4af37;"></i> Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" placeholder="Magazine Title" style="border-radius: 10px;" value="<?php echo htmlspecialchars($edit_mag['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-hashtag" style="color: #d4af37;"></i> Issue</label>
                            <input type="text" name="issue" class="form-control form-control-lg" placeholder="e.g. Issue #1" style="border-radius: 10px;" value="<?php echo htmlspecialchars($edit_mag['issue'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-tag" style="color: #d4af37;"></i> Category</label>
                            <select name="category" id="categorySelect" class="form-control form-control-lg" style="border-radius: 10px;" onchange="togglePriceInput()">
                                <option value="paid" <?php echo (($edit_mag['category'] ?? '') == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="free" <?php echo (($edit_mag['category'] ?? '') == 'free') ? 'selected' : ''; ?>>Free</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="priceInputGroup">
                            <label class="font-weight-bold"><i class="fas fa-money-bill-wave" style="color: #d4af37;"></i> Price (₦)</label>
                            <input type="number" name="price" class="form-control form-control-lg" step="0.01" value="<?php echo $edit_mag['price'] ?? '9.99'; ?>" style="border-radius: 10px;">
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-image" style="color: #d4af37;"></i> Cover Image</label>
                            <div class="custom-file mb-2">
                                <input type="file" name="image" class="custom-file-input" id="customFile" onchange="previewImage(this)">
                                <label class="custom-file-label" for="customFile" style="border-radius: 10px;">Choose cover...</label>
                            </div>
                            <!-- Current Image / Preview -->
                            <div id="imagePreviewContainer" style="<?php echo $edit_mag ? 'display: block;' : 'display: none;'; ?> text-align: center;">
                                <img id="imagePreview" src="<?php echo $edit_mag ? '../' . htmlspecialchars($edit_mag['image']) : ''; ?>" alt="Cover Preview" style="max-height: 200px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <?php if($edit_mag): ?><p class="small text-muted">Current cover</p><?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-file-pdf" style="color: #d4af37;"></i> Magazine PDF</label>
                            <div class="custom-file">
                                <input type="file" name="pdf_file" class="custom-file-input" id="customFilePdf" accept=".pdf">
                                <label class="custom-file-label" for="customFilePdf" style="border-radius: 10px;">Choose PDF...</label>
                            </div>
                            <?php if($edit_mag && !empty($edit_mag['pdf_file'])): ?>
                                <small class="text-success"><i class="fas fa-check"></i> Current PDF: <?php echo basename($edit_mag['pdf_file']); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-link" style="color: #d4af37;"></i> Buy Link (optional)</label>
                            <input type="url" name="buy_link" class="form-control form-control-lg" placeholder="https://..." style="border-radius: 10px;" value="<?php echo htmlspecialchars($edit_mag['buy_link'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-align-left" style="color: #d4af37;"></i> Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description..." style="border-radius: 10px;"><?php echo htmlspecialchars($edit_mag['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <?php if($edit_mag): ?>
                                <div class="col-6">
                                    <button type="submit" name="action" value="update" class="btn btn-block btn-lg" style="background: #d4af37; color: #000; border-radius: 10px;">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="manage_magazines.php" class="btn btn-dark btn-block btn-lg" style="border-radius: 10px;">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <button type="submit" name="action" value="add" class="btn btn-block btn-lg" style="background: #000; color: #fff; border-radius: 10px;">
                                        <i class="fas fa-plus" style="color: #d4af37;"></i> Add Magazine
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="manage_posts.php" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Posts</a>
            </div>
        </div>
        
        <!-- Magazines List -->
        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-newspaper" style="color: #d4af37;"></i> All Magazines
                    </h4>
                </div>
                <div class="card-body">
                    <?php if($magazines && $magazines->num_rows > 0): ?>
                    <div class="row">
                        <?php while($mag = $magazines->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                                <?php if(!empty($mag['image'])): ?>
                                    <div style="height: 250px; overflow: hidden;">
                                        <img src="<?php echo htmlspecialchars($mag['image']); ?>" class="card-img-top" style="width: 100%; height: 100%; object-fit: contain; background: #f8f9fa;">
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center" style="height: 250px; background: #e9ecef;">
                                        <i class="fas fa-newspaper fa-4x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body text-center p-3">
                                    <h5 class="font-weight-bold"><?php echo htmlspecialchars($mag['title']); ?></h5>
                                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($mag['issue']); ?></p>
                                    <span class="badge badge-<?php echo ($mag['category'] == 'free') ? 'success' : 'warning'; ?> mb-2"><?php echo ucfirst($mag['category']); ?></span>
                                    <?php if($mag['category'] != 'free'): ?>
                                        <h6 style="color: #d4af37; font-weight: bold;">₦<?php echo number_format($mag['price'], 2); ?></h6>
                                    <?php endif; ?>
                                    <?php if(!empty($mag['pdf_file'])): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> PDF Available</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white border-0 text-center pb-3">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="manage_magazines.php?edit=<?php echo $mag['id']; ?>" class="btn btn-sm btn-outline-dark mr-1" style="border-radius: 20px; padding: 5px 15px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php if(!empty($mag['pdf_file'])): ?>
                                            <a href="../<?php echo htmlspecialchars($mag['pdf_file']); ?>" target="_blank" class="btn btn-sm btn-outline-success mr-1" style="border-radius: 20px; padding: 5px 15px;">
                                                <i class="fas fa-file-pdf"></i> Download
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary mr-1" style="border-radius: 20px; padding: 5px 15px;" disabled title="No PDF uploaded">
                                                <i class="fas fa-file-pdf"></i> No PDF
                                            </button>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Delete this magazine?');" style="display:inline;">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $mag['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" style="border-radius: 20px; padding: 5px 15px;">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-light text-center" role="alert">
                        <i class="fas fa-info-circle fa-2x mb-3" style="color: #d4af37;"></i>
                        <h5>No magazines found.</h5>
                        <p class="text-muted">Start by adding a new magazine issue from the form.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Price Input based on Category
function togglePriceInput() {
    var category = document.getElementById('categorySelect').value;
    var priceGroup = document.getElementById('priceInputGroup');
    if (category === 'free') {
        priceGroup.style.display = 'none';
    } else {
        priceGroup.style.display = 'block';
    }
}

// Run on load
document.addEventListener('DOMContentLoaded', function() {
    togglePriceInput();
});

// Image Preview Function
function previewImage(input) {
    var preview = document.getElementById('imagePreview');
    var container = document.getElementById('imagePreviewContainer');
    
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = "";
        container.style.display = 'none';
    }
}

// Add the following code if you want the name of the file appear on select
$(".custom-file-input").on("change", function() {
  var fileName = $(this).val().split("\\").pop();
  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>



