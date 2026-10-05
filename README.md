# HR Demo Portal

> **Local URL:** [http://localhost/hrdemoportal/](http://localhost/hrdemoportal/)
---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Project Setup](#project-setup)
3. [Database Setup](#database-setup)
4. [Environment Configuration](#environment-configuration)
5. [Running the Project](#running-the-project)
6. [Project Structure Overview](#project-structure-overview)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Make sure the following are installed on your machine before proceeding:

| Requirement | Version |
|---|---|
| XAMPP (Apache + MySQL) | Latest stable |
| PHP | 7.4 or higher (8.x recommended) |
| MySQL | 5.7 or higher |
| Composer | Latest stable |

**Required PHP Extensions** (enable in `php.ini` if not already active):

- `intl`
- `mbstring`
- `json` *(enabled by default)*
- `mysqlnd`
- `curl`
- `gd`

---

## Project Setup

### 1. Clone / Place the Project

Place the project folder inside your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\hrdemoportal\
```

The project root should contain `index.php`, `composer.json`, and the `app/` folder.

### 2. Install PHP Dependencies

Open a terminal in the project root and run:

```bash
composer install
```

This will install all required packages including:
- `dompdf/dompdf` — PDF generation
- `firebase/php-jwt` — JWT authentication
- `phpmailer/phpmailer` — Email sending
- `laminas/laminas-escaper` — Output escaping

---

## Database Setup

### 1. Start MySQL

Open XAMPP Control Panel and start **Apache** and **MySQL**.

### 2. Create the Database

Open [phpMyAdmin](http://localhost/phpmyadmin) in your browser and create a new database:

```
Database name: hrdemoportal
```

### 3. Import the Database Dump

The database dump file is located in the `db/` folder (use the latest date folder):

```
db/<latest-date-folder>/database.sql
```

**To import via phpMyAdmin:**

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Select the `hrdemoportal` database from the left panel
3. Click the **Import** tab at the top
4. Click **Choose File** and select the `.sql` file from the `db/` folder
5. Click **Go** to import

**To import via command line:**

```bash
mysql -u root -p hrdemoportal < db/<latest-date-folder>/database.sql
```

---

## Environment Configuration

### 1. Configure the `.env` File

Copy or rename `.env.example` to `.env` (if not already present) and update the following values:

```env
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = hrdemoportal
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port     = 3306
```

> If your MySQL root user has a password, enter it in `database.default.password`.

### 2. Verify Base URL

Open `app/Config/App.php` and confirm the base URL is set correctly:

```php
public string $baseURL = 'http://localhost/hrdemoportal/';
```

### 3. Verify Database Config

Open `app/Config/Database.php` and confirm:

```php
'hostname' => 'localhost',
'username' => 'root',
'password' => '',
'database' => 'hrdemoportal',
```

---

## Running the Project

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open your browser and navigate to:

```
http://localhost/hrdemoportal/
```

3. You should see the HR Demo Portal login page.

---

## Troubleshooting

**Blank page or 500 error**
- Enable error display by setting `CI_ENVIRONMENT = development` in `.env`
- Check `writable/logs/` for error log files

**Database connection error**
- Confirm MySQL is running in XAMPP
- Verify credentials in `app/Config/Database.php` match your local MySQL setup
- Ensure the `hrdemoportal` database exists and the SQL dump has been imported

**Composer dependencies missing**
- Run `composer install` from the project root
- Ensure PHP is added to your system PATH so Composer can use it

**`writable/` permission errors**
- Ensure the `writable/` directory and its subdirectories are writable by the web server

**Page not found (404)**
- Confirm Apache `mod_rewrite` is enabled in XAMPP
- Check that the `.htaccess` file exists in the project root

---

## License 
This project is proprietary software developed by **Fablead Developers Technolab**. All rights reserved.
# HR-Korvia
# FB-HR-PORTAL
