<?php
include 'db.php';
include 'includes/header.php';

// Get category from URL
$category_slug = isset($_GET['cat']) ? trim($_GET['cat']) : '';

// Get category name
$category_name = "All Posts";
if(!empty($category_slug)) {
    $cat_stmt = $conn->prepare("SELECT category_name FROM categories WHERE category_slug = ?");
    $cat_stmt->bind_param("s", $category_slug);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if($cat_result->num_rows > 0) {
        $category_name = $cat_result->fetch_assoc()['category_name'];
    }
    $cat_stmt->close();
}
?>

<div class="container" style="margin-top: 20px;">
    <!-- Category Header -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-white mb-4">
                <div class="card-body text-center py-4">
                    <h1><i class="fas fa-folder-open"></i> <?php echo htmlspecialchars($category_name); ?></h1>
                    <p class="mb-0">Browse articles in this category</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group flex-wrap" role="group">
                <a href="category.php" class="btn <?php echo empty($category_slug) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    All
                </a>
                <?php
                $cats = $conn->query("SELECT * FROM categories ORDER BY category_name");
                while($cat = $cats->fetch_assoc()):
                ?>
                <a href="category.php?cat=<?php echo htmlspecialchars($cat['category_slug']); ?>" 
                   class="btn <?php echo ($category_slug == $cat['category_slug']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <!-- Posts Grid -->
    <div class="row">
        <?php
        // Build query based on category filter
        if(!empty($category_slug)) {
            $stmt = $conn->prepare("SELECT * FROM post_table WHERE category = ? ORDER BY post_date DESC");
            $stmt->bind_param("s", $category_slug);
            $stmt->execute();
            $posts = $stmt->get_result();
        } else {
            $posts = $conn->query("SELECT * FROM post_table ORDER BY post_date DESC");
        }
        
        if($posts->num_rows > 0):
            while($post = $posts->fetch_assoc()):
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php if(!empty($post['post_image'])): ?>
                    <img src="<?php echo htmlspecialchars($post['post_image']); ?>" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;"
                         alt="<?php echo htmlspecialchars($post['post_title']); ?>">
                <?php else: ?>
                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-white"></i>
                    </div>
                <?php endif; ?>
                
                <div class="card-body">
                    <span class="badge badge-info mb-2"><?php echo htmlspecialchars($post['category'] ?? 'general'); ?></span>
                    <h5 class="card-title"><?php echo htmlspecialchars($post['post_title']); ?></h5>
                    <p class="card-text text-muted">
                        <?php echo htmlspecialchars(substr($post['post_description'], 0, 100)) . '...'; ?>
                    </p>
                </div>
                
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> <?php echo date("M d, Y", strtotime($post['post_date'])); ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-heart text-danger"></i> <?php echo $post['post_likes']; ?>
                    </small>
                </div>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No posts found in this category.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
