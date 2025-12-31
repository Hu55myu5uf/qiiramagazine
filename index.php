<?php
include 'db.php';
include 'includes/header.php';
?>

<!-- Hero Section with Background Image -->
<div class="hero-section" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/qira/bg8.png'); background-size: cover; background-position: center center; background-repeat: no-repeat; min-height: 100vh; display: flex; align-items: center;">
    <div class="container text-white text-center">
        <h1 class="display-3 font-weight-bold">Welcome to Qiira Magazine</h1>
        <p class="lead" style="font-size: 1.5rem;">Your trusted source for History, Culture, Education, Business, and Politics</p>
    </div>
</div>

<div class="container">
    <!-- Latest Articles Section -->
    <div class="row my-4">
        <div class="col-12">
            <h2><i class="fas fa-fire text-danger"></i> Latest Articles</h2>
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
            <div class="card h-100 shadow-sm">
                <?php if(!empty($row['post_image'])): ?>
                    <img src="<?php echo htmlspecialchars($row['post_image']); ?>" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;"
                         alt="<?php echo htmlspecialchars($row['post_title']); ?>">
                <?php else: ?>
                    <img src="images/qira/bg<?php echo rand(1,8); ?>.<?php echo (rand(0,1) ? 'png' : 'jpg'); ?>" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;"
                         alt="Article Image"
                         onerror="this.src='images/qira/bg1.png'">
                <?php endif; ?>
                
                <div class="card-body">
                    <span class="badge badge-<?php 
                        $cat = $row['category'] ?? 'general';
                        echo ($cat == 'history') ? 'primary' : 
                             (($cat == 'culture') ? 'success' : 
                             (($cat == 'education') ? 'info' : 
                             (($cat == 'business') ? 'warning' : 
                             (($cat == 'politics') ? 'danger' : 'secondary'))));
                    ?> mb-2"><?php echo ucfirst(htmlspecialchars($cat)); ?></span>
                    
                    <h5 class="card-title"><?php echo htmlspecialchars($row['post_title']); ?></h5>
                    <p class="card-text text-muted">
                        <?php echo htmlspecialchars(substr($row['post_description'], 0, 120)) . '...'; ?>
                    </p>
                </div>
                
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt"></i> <?php echo date("M d, Y", strtotime($row['post_date'])); ?>
                        <span class="float-right">
                            <i class="fas fa-heart text-danger"></i> <?php echo $row['post_likes']; ?>
                        </span>
                    </small>
                </div>
            </div>
        </div>
        <?php
            }
        } else {
        ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <h4>No posts yet</h4>
                <p>Check back soon for exciting new content!</p>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="manage_posts.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Post
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
            <a href="category.php" class="btn btn-outline-dark btn-lg">
                <i class="fas fa-th-list"></i> View All Articles
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Our Magazines Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-newspaper text-success"></i> Our Magazines</h2>
            <hr>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-6 col-md-2 mb-3">
            <img src="images/books/b1.jpg" class="img-fluid rounded shadow" alt="Magazine 1">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="images/books/b2.jpg" class="img-fluid rounded shadow" alt="Magazine 2">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="images/books/b3.jpg" class="img-fluid rounded shadow" alt="Magazine 3">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="images/books/b4.jpg" class="img-fluid rounded shadow" alt="Magazine 4">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="images/books/b5.jpg" class="img-fluid rounded shadow" alt="Magazine 5">
        </div>
        <div class="col-6 col-md-2 mb-3 d-flex align-items-center justify-content-center">
            <a href="magazines.php" class="btn btn-success">
                <i class="fas fa-arrow-right"></i> View More
            </a>
        </div>
    </div>
    
    <!-- Categories Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-folder text-primary"></i> Browse by Category</h2>
            <hr>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-4 col-6 mb-3">
            <a href="category.php?cat=history" class="text-decoration-none">
                <div class="card text-white text-center h-100" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg1.png'); background-size: cover;">
                    <div class="card-body py-5">
                        <i class="fas fa-landmark fa-3x mb-2"></i>
                        <h5>History</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <a href="category.php?cat=culture" class="text-decoration-none">
                <div class="card text-white text-center h-100" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg2.jpg'); background-size: cover;">
                    <div class="card-body py-5">
                        <i class="fas fa-theater-masks fa-3x mb-2"></i>
                        <h5>Culture</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <a href="category.php?cat=education" class="text-decoration-none">
                <div class="card text-white text-center h-100" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg3.png'); background-size: cover;">
                    <div class="card-body py-5">
                        <i class="fas fa-graduation-cap fa-3x mb-2"></i>
                        <h5>Education</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-6 mb-3">
            <a href="category.php?cat=business" class="text-decoration-none">
                <div class="card text-white text-center h-100" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg4.png'); background-size: cover;">
                    <div class="card-body py-5">
                        <i class="fas fa-briefcase fa-3x mb-2"></i>
                        <h5>Business</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-12 mb-3">
            <a href="category.php?cat=politics" class="text-decoration-none">
                <div class="card text-white text-center h-100" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg5.JPG'); background-size: cover;">
                    <div class="card-body py-5">
                        <i class="fas fa-balance-scale fa-3x mb-2"></i>
                        <h5>Politics</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
