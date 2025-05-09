# Accounts Receivable System (ARS) - Installation Guide

This guide will walk you through the process of setting up the Accounts Receivable System using XAMPP and managing the GitHub repository.

## Repository Information

- **Repository URL**: https://github.com/Shorton04/ARS.git
- **System**: Cooperative Accounts Receivable System
- **Technology Stack**: PHP, MySQL, Bootstrap, JavaScript

## Prerequisites

Before you begin, ensure you have the following installed:

- [XAMPP](https://www.apachefriends.org/download.html) (includes Apache, MySQL, PHP)
- [Git](https://git-scm.com/downloads) (for repository management)
- Web browser (Chrome, Firefox, Edge, etc.)

## Setup Instructions

### Step 1: Install XAMPP

1. Download and install XAMPP from the [official website](https://www.apachefriends.org/download.html)
2. Start the XAMPP Control Panel
3. Start the Apache and MySQL services by clicking the "Start" buttons

### Step 2: Create Project Directory in XAMPP

1. Navigate to your XAMPP installation directory:
   - Windows: `C:\xampp\htdocs\`
   - macOS: `/Applications/XAMPP/htdocs/`
   - Linux: `/opt/lampp/htdocs/`

2. Create a new directory named `accounts_receivable_system`:
   ```bash
   # Windows
   mkdir C:\xampp\htdocs\accounts_receivable_system

   # macOS/Linux
   mkdir /path/to/xampp/htdocs/accounts_receivable_system
   ```

### Step 3: Clone the Repository

1. Open your terminal or command prompt
2. Navigate to the created directory:
   ```bash
   # Windows
   cd C:\xampp\htdocs\accounts_receivable_system

   # macOS/Linux
   cd /path/to/xampp/htdocs/accounts_receivable_system
   ```

3. Clone the repository:
   ```bash
   git clone https://github.com/Shorton04/ARS.git .
   ```
   *Note: The dot (.) at the end clones the repository into the current directory without creating a new subdirectory*

### Step 4: Set Up the Database

1. Open your web browser and navigate to:
   ```
   http://localhost/phpmyadmin
   ```

2. Create a new database:
   - Click on "New" in the left sidebar
   - Enter `cooperative_db` as the database name
   - Click "Create"

3. Import the database structure:
   - Select the newly created `cooperative_db` from the left sidebar
   - Click on "Import" tab at the top
   - Click "Choose File" and select the `database.sql` file from your project directory
   - Scroll down and click "Import"

4. Configure database connection:
   - Open `config/database.php` in a text editor
   - Update the database connection settings:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');  // Default XAMPP username
     define('DB_PASSWORD', '');      // Default XAMPP password (empty)
     define('DB_NAME', 'cooperative_db');
     ```

### Step 5: Configure Application Settings

1. If needed, update the base URL in the application:
   - Open `includes/security.php` (if it exists)
   - Ensure the `$base_url` variable is set correctly:
     ```php
     $base_url = "/accounts_receivable_system";
     ```

2. Create necessary directories and set permissions:
   ```bash
   mkdir -p logs
   chmod 755 logs
   ```

### Step 6: Access the Application

1. Open your web browser and navigate to:
   ```
   http://localhost/accounts_receivable_system
   ```

2. Log in with the default administrator credentials:
   - Username: `admin`
   - Password: `admin123`
   - **Important**: Change the default password immediately after your first login!

## Git Repository Management

### Pulling Latest Changes

To update your local copy with the latest changes from the repository:

```bash
cd C:\xampp\htdocs\accounts_receivable_system
git pull origin main
```

### Pushing Your Changes

If you've made changes and want to push them to the repository:

1. Stage your changes:
   ```bash
   git add .
   ```

2. Commit the changes:
   ```bash
   git commit -m "Description of your changes"
   ```

3. Push to the repository:
   ```bash
   git push origin main
   ```

### Managing Branches

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature-name
   ```

2. Switch between branches:
   ```bash
   git checkout branch-name
   ```

3. Merge changes from another branch:
   ```bash
   git merge branch-name
   ```

### Resolving Conflicts

If you encounter merge conflicts:

1. Check which files have conflicts:
   ```bash
   git status
   ```

2. Open the conflicted files in a text editor and resolve the conflicts manually
   (Look for sections marked with `<<<<<<<`, `=======`, and `>>>>>>>`)

3. After resolving conflicts, mark them as resolved:
   ```bash
   git add <resolved-file>
   ```

4. Complete the merge:
   ```bash
   git commit
   ```

## Troubleshooting

### XAMPP Issues

- **Services won't start**: Check if other applications are using ports 80 (Apache) or 3306 (MySQL)
- **Access forbidden**: Check folder permissions and ensure Apache has read access
- **PHP errors**: Check the Apache error log in XAMPP Control Panel

### Database Issues

- **Connection error**: Verify credentials in `config/database.php`
- **Tables not found**: Confirm that the database import was successful
- **Invalid credentials**: Ensure your MySQL username and password are correct

### Application Issues

- **404 errors**: Check that your folder is named exactly `accounts_receivable_system`
- **Blank pages**: Enable error reporting in PHP (`ini_set('display_errors', 1);`)
- **Login issues**: Verify the database has the correct admin user credentials

## Regular Maintenance

1. **Database Backup**:
   - In phpMyAdmin, select your database
   - Click the "Export" tab
   - Choose "Quick" export method and SQL format
   - Click "Go" to download the SQL backup file

2. **Code Backup**:
   - Your code is already versioned with Git
   - For additional safety, occasionally make a full copy of your directory

3. **Security Updates**:
   - Regularly update XAMPP to get the latest security patches
   - Follow secure coding practices when modifying the codebase

## Default Login Credentials

- **Admin User**:
  - Username: `admin`
  - Password: `admin123`

---

© 2025 Accounts Receivable System | Developed by Shorton04
