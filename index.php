<?php
include 'db.php';
include 'includes/header.php';

// Fetch active hero images
$hero_result = $conn->query("SELECT * FROM hero_images WHERE is_active = 1 ORDER BY display_order ASC");
$hero_slides = [];
while ($row = $hero_result->fetch_assoc()) {
    $hero_slides[] = $row;
}
?>

<!-- Hero Slider Section -->
<?php if(count($hero_slides) > 0): ?>
<div id="heroCarousel" class="carousel slide carousel-fade" data-ride="carousel">
    <ol class="carousel-indicators">
        <?php foreach($hero_slides as $index => $slide): ?>
            <li data-target="#heroCarousel" data-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"></li>
        <?php endforeach; ?>
    </ol>
    <div class="carousel-inner">
        <?php foreach($hero_slides as $index => $slide): ?>
        <!-- Slide <?php echo $index + 1; ?> -->
        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo htmlspecialchars($slide['image_path']); ?>'); background-size: cover; background-position: center; min-height: 100vh;">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold"><?php echo htmlspecialchars($slide['title']); ?></h1>
                    <?php if(!empty($slide['subtitle'])): ?>
                        <p class="lead hero-lead-text"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                    <?php endif; ?>
                    <?php if(!empty($slide['button_text']) && !empty($slide['button_link'])): ?>
                        <a href="<?php echo htmlspecialchars($slide['button_link']); ?>" class="btn btn-primary btn-lg mt-3 btn-hero-primary"><?php echo htmlspecialchars($slide['button_text']); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if(count($hero_slides) > 1): ?>
    <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
    <?php endif; ?>
</div>
<?php else: ?>
<!-- Fallback if no hero slides -->
<div class="jumbotron text-center" style="background: #f8f9fa; margin:0; padding: 100px 0;">
    <div class="container">
        <h1 class="display-4">Welcome to Qiira Magazine</h1>
        <p class="lead">Your trusted source for insightful articles</p>
    </div>
</div>
<?php endif; ?>

<div class="container" id="latest-articles">
    <!-- Latest News/Posts Section -->
    <div class="row my-4">
        <div class="col-12">
            <h2><i class="fas fa-fire icon-gold"></i> Latest News/Posts</h2>
            <hr>
        </div>
    </div>
    
    <div class="row">
        <?php
        $sql = "SELECT * FROM post_table ORDER BY post_date DESC LIMIT 9";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
        ?>
        <div class="col-md-4 mb-4">
            <a href="post.php?id=<?php echo $row['post_id']; ?>" class="text-decoration-none">
            <div class="card h-100 shadow-sm article-card">
                <?php if(!empty($row['post_image'])): ?>
                    <div class="card-img-wrapper">
                        <img src="<?php echo htmlspecialchars($row['post_image']); ?>" 
                             class="card-img-top card-img-zoom"
                             alt="<?php echo htmlspecialchars($row['post_title']); ?>">
                    </div>
                <?php else: ?>
                    <img src="images/qira/bg<?php echo rand(1,8); ?>.<?php echo (rand(0,1) ? 'png' : 'jpg'); ?>" 
                         class="card-img-top default-article-img"
                         alt="Article Image"
                         onerror="this.src='images/qira/bg1.png'">
                <?php endif; ?>
                
                <div class="card-body">
                    <span class="badge mb-2 badge-gold">
                        <?php echo ucfirst(htmlspecialchars($row['category'] ?? 'general')); ?>
                    </span>
                    
                    <h5 class="card-title font-weight-bold text-dark"><?php echo htmlspecialchars($row['post_title']); ?></h5>
                    <p class="card-text text-muted small">
                        <?php echo htmlspecialchars(substr($row['post_description'], 0, 100)) . '...'; ?>
                    </p>
                </div>
                
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt"></i> <?php echo date("M d, Y", strtotime($row['post_date'])); ?>
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-heart icon-gold"></i> <?php echo $row['post_likes']; ?>
                        </small>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <?php
            }
        } else {
        ?>
        <div class="col-12">
            <div class="alert alert-light text-center">
                <i class="fas fa-info-circle fa-2x mb-2 icon-gold"></i>
                <h4>No posts yet</h4>
                <p>Check back soon for exciting new content!</p>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="manage_posts.php" class="btn btn-black-gold">
                        <i class="fas fa-plus icon-gold"></i> Create First Post
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php } ?>
    </div>
    
    <!-- View All Link -->
    <?php if($result->num_rows > 0): ?>
    <div class="row mt-3 mb-5">
        <div class="col-12 text-center">
            <a href="category.php" class="btn btn-outline-dark btn-lg btn-rounded">
                <i class="fas fa-th-list"></i> View All Articles
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Our Magazines Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-newspaper icon-gold"></i> Our Magazines</h2>
            <hr>
        </div>
    </div>
    
    <div class="row mb-5">
        <?php
        $mag_result = $conn->query("SELECT * FROM magazines ORDER BY created_at DESC LIMIT 5");
        if ($mag_result && $mag_result->num_rows > 0) {
            while($mag = $mag_result->fetch_assoc()) {
        ?>
        <div class="col-6 col-md-2 mb-3">
            <?php if(!empty($mag['image'])): ?>
                <img src="<?php echo htmlspecialchars($mag['image']); ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($mag['title']); ?>" style="height: 200px; object-fit: cover; width: 100%;">
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center rounded shadow" style="height: 200px;">
                    <i class="fas fa-book fa-3x text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
        <?php 
            }
        } 
        ?>
        <div class="col-6 col-md-2 mb-3 d-flex align-items-center justify-content-center">
            <a href="magazines.php" class="btn shadow btn-gold-rounded">
                View More <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Categories Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-folder icon-gold"></i> Browse by Category</h2>
            <hr>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-4 col-6 mb-3">
            <a href="category.php?cat=history" class="text-decoration-none">
                <div class="card text-white text-center h-100 border-0 shadow category-card cat-bg-history">
                    <div class="card-body py-5 d-flex flex-column justify-content-center">
                        <i class="fas fa-landmark fa-3x mb-2 icon-gold"></i>
                        <h5 class="font-weight-bold">History</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <a href="category.php?cat=culture" class="text-decoration-none">
                <div class="card text-white text-center h-100 border-0 shadow category-card cat-bg-culture">
                    <div class="card-body py-5 d-flex flex-column justify-content-center">
                        <i class="fas fa-theater-masks fa-3x mb-2 icon-gold"></i>
                        <h5 class="font-weight-bold">Culture</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <a href="category.php?cat=education" class="text-decoration-none">
                <div class="card text-white text-center h-100 border-0 shadow category-card cat-bg-education">
                    <div class="card-body py-5 d-flex flex-column justify-content-center">
                        <i class="fas fa-graduation-cap fa-3x mb-2 icon-gold"></i>
                        <h5 class="font-weight-bold">Education</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-6 mb-3">
            <a href="category.php?cat=business" class="text-decoration-none">
                <div class="card text-white text-center h-100 border-0 shadow category-card cat-bg-business">
                    <div class="card-body py-5 d-flex flex-column justify-content-center">
                        <i class="fas fa-briefcase fa-3x mb-2 icon-gold"></i>
                        <h5 class="font-weight-bold">Business</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-12 mb-3">
            <a href="category.php?cat=politics" class="text-decoration-none">
                <div class="card text-white text-center h-100 border-0 shadow category-card cat-bg-politics">
                    <div class="card-body py-5 d-flex flex-column justify-content-center">
                        <i class="fas fa-balance-scale fa-3x mb-2 icon-gold"></i>
                        <h5 class="font-weight-bold">Politics</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
