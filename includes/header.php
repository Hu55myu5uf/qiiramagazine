<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamic asset path detection using a more robust method
$is_admin = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$assets_path = $is_admin ? '../' : '';
$admin_path = $is_admin ? '' : 'admin/';

// Fetch all categories for the navbar
$pinned_slugs = ['history', 'culture', 'politics'];
$nav_pinned_cats = [];
$nav_more_cats = [];

// Ensure db.php is included if not already
$db_file = __DIR__ . '/../db.php';
if (file_exists($db_file)) {
    include_once $db_file;
    if (isset($conn)) {
        $cat_query = "SELECT * FROM categories ORDER BY category_name ASC";
        $cat_res = $conn->query($cat_query);
        if ($cat_res) {
            while ($c = $cat_res->fetch_assoc()) {
                if (in_array($c['category_slug'], $pinned_slugs)) {
                    // Store pinned in order of the pinned_slugs array
                    $nav_pinned_cats[$c['category_slug']] = $c;
                } else {
                    $nav_more_cats[] = $c;
                }
            }
        }
    }
}

// --- Dynamic OG Tags for Post Pages ---
$protocol = 'http';
if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) || 
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    $protocol = 'https';
}
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$base_path = rtrim(dirname($script_path, $is_admin ? 2 : 1), '/\\');
$site_base_url = $protocol . "://" . $host . $base_path;

$og_title = "Qiira Magazine";
$og_description = "Your source for History, Culture, Education, Business, and Politics insights";
$og_image = $site_base_url . "/images/qira/qiiralogo.png"; // Default image
$og_url = $protocol . "://" . $host . $_SERVER['REQUEST_URI'];
$og_type = "website";

// Detect if on a post page
if (isset($_GET['id']) && strpos($_SERVER['SCRIPT_NAME'], 'post.php') !== false && isset($conn)) {
    $og_post_id = intval($_GET['id']);
    $og_stmt = $conn->prepare("SELECT post_title, post_description, post_image FROM post_table WHERE post_id = ?");
    if ($og_stmt) {
        $og_stmt->bind_param("i", $og_post_id);
        $og_stmt->execute();
        $og_result = $og_stmt->get_result();
        if ($og_row = $og_result->fetch_assoc()) {
            $og_type = "article";
            $og_title = htmlspecialchars($og_row['post_title']) . " | Qiira Magazine";
            $og_description = htmlspecialchars(substr(strip_tags($og_row['post_description']), 0, 160));
            if (!empty($og_row['post_image'])) {
                // Ensure it's an absolute URL
                $img_path = ltrim($og_row['post_image'], '/');
                $og_image = $site_base_url . "/" . $img_path;
            }
        }
        $og_stmt->close();
    }
}

// Map extensions to MIME types
$og_image_mime = "image/png";
if (!empty($og_image)) {
    $ext = pathinfo($og_image, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), ['jpg', 'jpeg'])) $og_image_mime = "image/jpeg";
    elseif (strtolower($ext) === 'gif') $og_image_mime = "image/gif";
    elseif (strtolower($ext) === 'webp') $og_image_mime = "image/webp";
}
?><!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Social Media Meta Tags (WhatsApp & FB First) -->
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <meta property="og:image:secure_url" content="<?php echo $og_image; ?>">
    <meta property="og:image:type" content="<?php echo $og_image_mime; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:title" content="<?php echo $og_title; ?>">
    <meta property="og:description" content="<?php echo $og_description; ?>">
    <meta property="og:url" content="<?php echo $og_url; ?>">
    <meta property="og:type" content="<?php echo $og_type; ?>">
    <meta property="og:site_name" content="Qiira Magazine">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $og_title; ?>">
    <meta name="twitter:description" content="<?php echo $og_description; ?>">
    <?php if (!empty($og_image)): ?>
    <meta name="twitter:image" content="<?php echo $og_image; ?>">
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo $og_url; ?>">

    <!-- Metadata for Google -->
    <title><?php echo $og_title; ?></title>
    <meta name="title" content="<?php echo $og_title; ?>">
    <meta name="description" content="<?php echo $og_description; ?>">
    <meta name="keywords" content="magazine, news, history, culture, education, business, politics">
    <meta name="author" content="Qiira Company Limited">
    <meta itemprop="name" content="<?php echo $og_title; ?>">
    <meta itemprop="description" content="<?php echo $og_description; ?>">
    <?php if (!empty($og_image)): ?>
    <meta itemprop="image" content="<?php echo $og_image; ?>">
    <?php endif; ?>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo $assets_path; ?>bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- DataTables CSS -->
    <link href="<?php echo $assets_path; ?>datatables/css/jquery.dataTables.min.css" rel="stylesheet" />
    
    <!-- FontAwesome CSS -->
    <link href="<?php echo $assets_path; ?>fontawesome/css/all.css" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link href="<?php echo $assets_path; ?>css/customstylesheet.css" rel="stylesheet" />

    <!-- jQuery & Bootstrap JS -->
    <script src="<?php echo $assets_path; ?>bootstrap/js/jquery-3.5.1.slim.min.js"></script>
    <script src="<?php echo $assets_path; ?>bootstrap/js/popper.min.js"></script>
    <script src="<?php echo $assets_path; ?>bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo $assets_path; ?>datatables/js/jquery.dataTables.min.js"></script>
    
</head>
<body>
    <!-- Modern Navbar -->
    <nav class="navbar navbar-expand-lg modern-navbar fixed-top">
        <div class="container-fluid px-lg-4 px-2">
            <a class="navbar-brand" href="<?php echo $assets_path; ?>index.php">
                <img src="<?php echo $assets_path; ?>images/qira/qiiralogo.png" width="40" height="40" alt="Logo">
                Qiira Magazine
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" style="border: none; outline: none;">
                <span class="fas fa-bars text-white" style="font-size: 1.5rem;"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <?php 
                    // Render pinned categories in specific order
                    foreach ($pinned_slugs as $slug) {
                        if (isset($nav_pinned_cats[$slug])) {
                            $c = $nav_pinned_cats[$slug];
                            echo '<li class="nav-item">
                                    <a class="nav-link" href="' . $assets_path . 'category.php?cat=' . htmlspecialchars($c['category_slug']) . '">' . htmlspecialchars($c['category_name']) . '</a>
                                  </li>';
                        }
                    }
                    ?>
                    
                    <?php if (!empty($nav_more_cats)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="moreCatsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                More <i class="fas fa-chevron-down small"></i>
                            </a>
                            <div class="dropdown-menu two-column-dropdown shadow-lg border-0" aria-labelledby="moreCatsDropdown" style="border-radius: 10px; background: #111;">
                                <?php foreach ($nav_more_cats as $mc): ?>
                                    <a class="dropdown-item" href="<?php echo $assets_path; ?>category.php?cat=<?php echo htmlspecialchars($mc['category_slug']); ?>">
                                        <i class="fas fa-tag small mr-2" style="color: #d4af37;"></i> <?php echo htmlspecialchars($mc['category_name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $assets_path; ?>index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $assets_path; ?>about.php"><i class="fas fa-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $assets_path; ?>magazines.php"><i class="fas fa-book"></i> Magazines</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $assets_path; ?>contact.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle admin-badge" href="#" id="adminDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-user-shield"></i> Admin
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>manage_posts.php"><i class="fas fa-newspaper"></i> Manage Posts</a>
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>manage_magazines.php"><i class="fas fa-book"></i> Manage Magazines</a>
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>manage_editors.php"><i class="fas fa-users"></i> Manage Editors</a>
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>manage_admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a>
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>hero_section.php"><i class="fas fa-images"></i> Hero Section</a>
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>view_contacts.php"><i class="fas fa-inbox"></i> View Messages</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="<?php echo $assets_path; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </li>
                    <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] == 'editor'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle editor-nav-link" href="#" id="editorDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-user-edit"></i> <?php echo htmlspecialchars($_SESSION['editor_name']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?php echo $admin_path; ?>editor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="<?php echo $assets_path; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </li>
                    <?php elseif(isset($_SESSION['user_id'])): ?>
                        <!-- Logged in User -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" style="color: #d4af37;">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="background: #111; border: 1px solid rgba(212,175,55,0.2);">
                                <a class="dropdown-item" href="<?php echo $assets_path; ?>my_purchases.php" style="color: #fff;"><i class="fas fa-book mr-2" style="color: #d4af37;"></i> My Purchases</a>
                                <div class="dropdown-divider" style="border-color: rgba(255,255,255,0.1);"></div>
                                <a class="dropdown-item text-danger" href="<?php echo $assets_path; ?>user_logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php 
                        // Hide these links on admin/editor login pages
                        $current_page = basename($_SERVER['SCRIPT_NAME']);
                        $is_admin_login = ($is_admin && ($current_page == 'login.php' || $current_page == 'editor_login.php'));
                        ?>
                        <?php if (!$is_admin_login): ?>
                            <!-- Guest - Show Login/Register -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $assets_path; ?>login.php" style="color: #d4af37;"><i class="fas fa-sign-in-alt mr-1"></i> Login</a>
                            </li>
                            <li class="nav-item d-flex align-items-center">
                                <a class="nav-link py-1 px-3" href="<?php echo $assets_path; ?>register.php" 
                                   style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #000; border-radius: 20px; font-size: 0.9rem;">
                                    <i class="fas fa-user-plus mr-1"></i> Register
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Spacer for fixed navbar -->
    <div class="navbar-spacer"></div>

    <!-- Main Content -->
    <div class="main-content-wrapper">
