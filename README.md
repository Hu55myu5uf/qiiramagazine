# Qiira Magazine

A modern PHP-based magazine/blog web application with admin panel for managing posts, editors, and magazines.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

## Features

- 📰 **Post Management** - Create, edit, and delete articles with image uploads
- 📚 **Magazine Store** - Showcase and sell digital magazines
- 👥 **Editor System** - Dedicated editor login and dashboard
- 📂 **Category System** - Organize content by History, Culture, Education, Business, Politics
- 📧 **Contact Form** - Functional contact form with database storage
- 🔒 **Secure Authentication** - Bcrypt password hashing, session security
- 📱 **Responsive Design** - Mobile-friendly Bootstrap layout

## Screenshots

The application features:
- Full-screen hero sections with background images
- Card-based article and magazine displays
- Admin dashboard for content management
- Category-filtered post views

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache (XAMPP recommended)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Hu55myu5uf/qiiramagazine.git
```

### 2. Move to Web Server Directory
```bash
# For XAMPP
cp -r qiiramagazine /xampp/htdocs/
```

### 3. Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `qiiramagazine`
3. Import `database.sql` file

### 4. Configure Database Connection
Edit `db.php` if needed:
```php
$server = "localhost";
$dbuser = "root";
$dbpassword = "";
$db = "qiiramagazine";
```

### 5. Access the Application
```
http://localhost/qiiramagazine/
```

## Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |

> ⚠️ **Important:** Change the default password after first login!

## Project Structure

```
qiiramagazine/
├── css/                    # Custom stylesheets
├── images/                 # Image assets
│   ├── books/             # Magazine covers
│   ├── qira/              # Background images
│   └── posts/             # Uploaded post images
├── includes/              # PHP includes
│   ├── header.php         # Navigation header
│   ├── footer.php         # Page footer
│   └── csrf.php           # CSRF protection
├── index.php              # Homepage
├── about.php              # About page
├── contact.php            # Contact form
├── category.php           # Category browser
├── magazines.php          # Magazine store
├── admin_login.php        # Admin authentication
├── editor_login.php       # Editor authentication
├── editor_dashboard.php   # Editor workspace
├── manage_posts.php       # Post management (Admin)
├── manage_editors.php     # Editor management (Admin)
├── manage_magazines.php   # Magazine management (Admin)
├── db.php                 # Database connection
└── database.sql           # Database schema
```

## Security Features

- ✅ SQL Injection Prevention (Prepared Statements)
- ✅ Password Hashing (bcrypt)
- ✅ Session Fixation Protection
- ✅ XSS Prevention (htmlspecialchars)
- ✅ CSRF Protection Utilities

## Technologies Used

- **Backend:** PHP, MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 4
- **Icons:** Font Awesome 5
- **Tables:** DataTables

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

**Qiira Company Limited**

---

⭐ If you found this project helpful, please give it a star!
