<?php
include 'includes/header.php';
?>

<!-- Hero Section with Background -->
<div style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/qira/bg7.png'); background-size: cover; background-position: center center; background-repeat: no-repeat; min-height: 100vh; display: flex; align-items: center;">
    <div class="container text-white text-center">
        <h1 class="display-3 font-weight-bold">About Qiira Magazine</h1>
        <p class="lead" style="font-size: 1.5rem;">Your trusted source for insightful articles on History, Culture, Education, Business, and Politics</p>
    </div>
</div>

<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    
    <!-- Mission Statement -->
    <div class="row mb-5">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="text-center mb-4"><i class="fas fa-bullseye text-primary"></i> Our Mission</h2>
                    <p class="lead text-center">
                        At Qiira Magazine, we are dedicated to delivering high-quality content that informs, educates, and inspires our readers. 
                        We believe in the power of knowledge to transform lives and communities.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- What We Cover -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-center mb-4"><i class="fas fa-newspaper"></i> What We Cover</h2>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg1.png'); background-size: cover;">
                <div class="card-body text-center text-white py-5">
                    <i class="fas fa-landmark fa-3x mb-3"></i>
                    <h4>History</h4>
                    <p>Exploring the past to understand our present and shape our future.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg2.jpg'); background-size: cover;">
                <div class="card-body text-center text-white py-5">
                    <i class="fas fa-theater-masks fa-3x mb-3"></i>
                    <h4>Culture</h4>
                    <p>Celebrating diversity and the rich tapestry of human expression.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg3.png'); background-size: cover;">
                <div class="card-body text-center text-white py-5">
                    <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                    <h4>Education</h4>
                    <p>Empowering minds through knowledge and lifelong learning.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card h-100 shadow-sm" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg4.png'); background-size: cover;">
                <div class="card-body text-center text-white py-5">
                    <i class="fas fa-briefcase fa-3x mb-3"></i>
                    <h4>Business</h4>
                    <p>Insights into entrepreneurship, commerce, and economic trends.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card h-100 shadow-sm" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg6.JPG'); background-size: cover;">
                <div class="card-body text-center text-white py-5">
                    <i class="fas fa-balance-scale fa-3x mb-3"></i>
                    <h4>Politics</h4>
                    <p>Analyzing governance, policy, and civic engagement.</p>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- Call to Action -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-white text-center py-5" style="background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('images/qira/bg8.png'); background-size: cover;">
                <h3>Ready to explore?</h3>
                <p>Browse our latest articles and discover something new today.</p>
                <div>
                    <a href="index.php" class="btn btn-warning btn-lg mr-2">
                        <i class="fas fa-home"></i> Visit Home
                    </a>
                    <a href="category.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-folder"></i> Browse Categories
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
