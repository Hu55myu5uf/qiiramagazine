<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Qiira Magazine - Your source for History, Culture, Education, Business, and Politics insights">
    <meta name="keywords" content="magazine, news, history, culture, education, business, politics">
    <meta name="author" content="Qiira Company Limited">
    <title>Qiira Magazine</title>
    
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- DataTables CSS -->
    <link href="datatables/css/jquery.dataTables.min.css" rel="stylesheet" />
    
    <!-- FontAwesome CSS -->
    <link href="fontawesome/css/all.css" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link href="css/customstylesheet.css" rel="stylesheet" />

    <!-- jQuery & Bootstrap JS -->
    <script src="bootstrap/js/jquery-3.5.1.slim.min.js"></script>
    <script src="bootstrap/js/popper.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="datatables/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <div>
        <nav class="navbar navbar-expand-lg navbar-dark bgDark">
            <a class="navbar-brand text-warning" href="index.php">
                <img src="images/qira/qiiralogo.png" width="35" height="35" class="d-inline-block align-top mr-2" alt="Logo">
                Qiira Magazine
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <!-- Category Links - Expanded -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="category.php?cat=history"><i class="fas fa-landmark"></i> History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="category.php?cat=culture"><i class="fas fa-theater-masks"></i> Culture</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="category.php?cat=education"><i class="fas fa-graduation-cap"></i> Education</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="category.php?cat=business"><i class="fas fa-briefcase"></i> Business</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="category.php?cat=politics"><i class="fas fa-balance-scale"></i> Politics</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <!-- Home, About, Contact - Now on Right -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <!-- Admin Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" id="adminDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-user-shield"></i> Admin
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="manage_posts.php"><i class="fas fa-newspaper"></i> Manage Posts</a>
                                <a class="dropdown-item" href="manage_magazines.php"><i class="fas fa-book"></i> Manage Magazines</a>
                                <a class="dropdown-item" href="manage_editors.php"><i class="fas fa-users"></i> Manage Editors</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </li>
                    <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] == 'editor'): ?>
                        <!-- Editor Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-info" href="#" id="editorDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-user-edit"></i> <?php echo htmlspecialchars($_SESSION['editor_name']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="editor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div style="min-height: 500px;">
