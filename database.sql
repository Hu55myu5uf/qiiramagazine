-- Database: qiiramagazine

CREATE TABLE IF NOT EXISTS `admin_login` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_username` varchar(50) DEFAULT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `admin_password` varchar(255) DEFAULT NULL,  -- Extended for bcrypt hash
  `is_suspended` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default password is 'admin123' (hashed with bcrypt)
-- IMPORTANT: Change this password after first login!
INSERT INTO `admin_login` (`admin_username`, `full_name`, `admin_password`) VALUES
('admin', 'Admin User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE TABLE IF NOT EXISTS `editors_table` (
  `username` varchar(50) NOT NULL,
  `editor_name` varchar(100) DEFAULT NULL,
  `editor_email` varchar(100) DEFAULT NULL,
  `editor_password` varchar(255) DEFAULT NULL,  -- Extended for bcrypt hash
  `is_first_login` tinyint(1) DEFAULT 1,
  `is_suspended` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `post_table` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_title` varchar(255) DEFAULT NULL,
  `post_description` text,
  `post_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `post_image` varchar(255) DEFAULT NULL,
  `post_likes` int(11) DEFAULT 0,
  `author_id` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,  -- Added category field
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample post
INSERT INTO `post_table` (`post_title`, `post_description`, `post_image`, `post_likes`, `category`) VALUES
('Welcome to Qiira Magazine', 'This is the first post in the new PHP version of Qiira Magazine.', 'images/qira/bg5.JPG', 10, 'general');

-- Categories table for organizing content
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  `category_slug` varchar(50) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`category_name`, `category_slug`) VALUES
('History', 'history'),
('Culture', 'culture'),
('Education', 'education'),
('Business', 'business'),
('Politics', 'politics'),
('People', 'people'),
('Opinion', 'opinion'),
('Exclusive', 'exclusive'),
('Lifestyle', 'lifestyle'),
('Health', 'health'),
('Sports', 'sports'),
('General', 'general');

-- Contact submissions table
CREATE TABLE IF NOT EXISTS `contact_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Magazines table
CREATE TABLE IF NOT EXISTS `magazines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `issue` varchar(50) DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) DEFAULT 9.99,
  `image` varchar(255) DEFAULT NULL,
  `buy_link` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments table for post comments
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `commenter_name` varchar(100) NOT NULL,
  `commenter_email` varchar(100) DEFAULT NULL,
  `comment_text` text NOT NULL,
  `comment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_approved` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`comment_id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hero images table for homepage carousel
CREATE TABLE IF NOT EXISTS `hero_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default hero slides
('Welcome to Qiira Magazine', 'Your trusted source for History, Culture, Education, Business, and Politics', 'Start Reading', '#latest-articles', 'images/qira/bg5.JPG', 1, 1),
('Discover Our Stories', 'Explore insightful articles across all categories', 'Browse Articles', 'category.php', 'images/qira/bg6.JPG', 2, 1),
('Dive into History', 'Uncover the stories that shaped our world', 'Explore History', 'category.php?cat=history', 'images/qira/bg5.JPG', 3, 1),
('Experience Culture', 'Celebrating the rich tapestry of human expression', 'Discover Culture', 'category.php?cat=culture', 'images/qira/bg6.JPG', 4, 1),
('Business Insights', 'Stay ahead with the latest economic trends', 'Read Business', 'category.php?cat=business', 'images/qira/bg5.JPG', 5, 1);

-- Users table for customer registration
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `is_suspended` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Purchases table for tracking magazine purchases
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `magazine_id` int(11) NOT NULL,
  `paystack_reference` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `downloads_remaining` int(11) DEFAULT 5,
  `purchased_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `magazine_id` (`magazine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email log table for tracking admin emails
CREATE TABLE IF NOT EXISTS `email_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient_type` enum('single','all') NOT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `total_sent` int(11) DEFAULT 0,
  `sent_by` varchar(50) NOT NULL,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
