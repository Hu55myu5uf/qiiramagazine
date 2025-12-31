-- Database: qiiramagazine

CREATE TABLE IF NOT EXISTS `admin_login` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_username` varchar(50) DEFAULT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `admin_password` varchar(255) DEFAULT NULL,  -- Extended for bcrypt hash
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default password is 'admin123' (hashed with bcrypt)
-- IMPORTANT: Change this password after first login!
INSERT INTO `admin_login` (`admin_username`, `full_name`, `admin_password`) VALUES
('admin', 'Admin User', '$2y$10$YourHashedPasswordHere');

CREATE TABLE IF NOT EXISTS `editors_table` (
  `editor_id` varchar(50) NOT NULL,
  `editor_name` varchar(100) DEFAULT NULL,
  `editor_password` varchar(255) DEFAULT NULL,  -- Extended for bcrypt hash
  PRIMARY KEY (`editor_id`)
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
