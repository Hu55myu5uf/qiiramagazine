<?php
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

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
            <div class="hero-slide mb-4" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/qira/bg<?php echo rand(1,8); ?>.<?php echo (rand(0,1) ? 'png' : 'jpg'); ?>'); border-radius: 15px; min-height: 300px !important;">
                <div class="d-flex align-items-center justify-content-center h-100 hero-content" style="min-height: 300px !important;">
                    <div class="text-center text-white p-4">
                        <h1 class="display-3 font-weight-bold"><i class="fas fa-folder-open"></i> <?php echo htmlspecialchars($category_name); ?></h1>
                        <p class="lead hero-lead-text">Browse articles in this category</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group flex-wrap" role="group">
                <a href="category.php" class="btn <?php echo empty($category_slug) ? '' : 'btn-dark'; ?>" style="border-radius: 20px; margin: 3px; <?php echo empty($category_slug) ? 'background: #d4af37; color: #000;' : ''; ?>">
                    All
                </a>
                <?php
                $cats = $conn->query("SELECT * FROM categories ORDER BY category_name");
                while($cat = $cats->fetch_assoc()):
                ?>
                <a href="category.php?cat=<?php echo htmlspecialchars($cat['category_slug']); ?>" 
                   class="btn <?php echo ($category_slug == $cat['category_slug']) ? '' : 'btn-dark'; ?>" style="border-radius: 20px; margin: 3px; <?php echo ($category_slug == $cat['category_slug']) ? 'background: #d4af37; color: #000;' : ''; ?>">
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
        <div class="col-md-4 col-12 mb-4">
            <a href="post.php?id=<?php echo $post['post_id']; ?>" class="text-decoration-none">
            <div class="card h-100 shadow-sm article-card">
                <?php if(!empty($post['post_image'])): ?>
                    <div class="card-img-wrapper">
                        <img src="<?php echo htmlspecialchars($post['post_image']); ?>" 
                             class="card-img-top card-img-zoom"
                             alt="<?php echo htmlspecialchars($post['post_title']); ?>">
                    </div>
                <?php else: ?>
                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center default-article-img">
                        <i class="fas fa-image fa-3x text-white"></i>
                    </div>
                <?php endif; ?>
                
                <div class="card-body">
                    <span class="badge mb-2 badge-gold"><?php echo ucfirst(htmlspecialchars($post['category'] ?? 'general')); ?></span>
                    <h5 class="card-title font-weight-bold text-dark"><?php echo htmlspecialchars($post['post_title']); ?></h5>
                    <p class="card-text text-muted small">
                        <?php echo htmlspecialchars(substr($post['post_description'], 0, 100)) . '...'; ?>
                    </p>
                </div>
                
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> <?php echo date("M d, Y", strtotime($post['post_date'])); ?>
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-heart icon-gold"></i> <?php echo $post['post_likes']; ?>
                        </small>
                    </div>
                </div>
            </div>
            </a>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
