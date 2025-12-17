# Alpacabon's Hardware - Point of Sale and Inventory Management System
## User Guide

---

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Installation Guide (XAMPP)](#installation-guide-xampp)
3. [Running the System](#running-the-system)
4. [Default Login Credentials](#default-login-credentials)
5. [System Navigation](#system-navigation)
6. [User Roles & Permissions](#user-roles--permissions)

---

## System Requirements

- **XAMPP** (with PHP 8.2+ and MySQL)
- **Node.js** (v18 or higher)
- **Composer** (PHP package manager)
- **Web Browser** (Chrome, Firefox, Edge recommended)

---

## Installation Guide (XAMPP)

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install with PHP 8.2+ selected
3. Start **Apache** and **MySQL** from XAMPP Control Panel

### Step 2: Setup the Project
1. Extract the project folder to `C:\xampp\htdocs\alpacabon-hardware`

2. Open **Command Prompt** or **Terminal** and navigate to the project:
   ```bash
   cd C:\xampp\htdocs\alpacabon-hardware
   ```

3. Install PHP dependencies:
   ```bash
   composer install
   ```

4. Install Node.js dependencies:
   ```bash
   npm install
   ```


   ```5
   . Generate application key:
   ```bash
   php artisan key:generate
   ```

### Step 3: Database Setup
1. Open **phpMyAdmin** at http://localhost/phpmyadmin
2. Create a new database named `alpacabon_hardware`
3. Update `.env` file with database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=alpacabon_hardware
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Run database migrations:
   ```bash
   php artisan migrate
   ```

5. Seed default data (roles, settings):
   ```bash
   php artisan db:seed
   ```

6. Create storage link for product images:
   ```bash
   php artisan storage:link
   ```

---

## Running the System

### Option 1: Using Built-in PHP Server (Recommended for Development)

Open **two terminal windows**:

**Terminal 1 - Start Laravel Server:**
```bash
cd C:\xampp\htdocs\alpacabon-hardware
php artisan serve
```
This will start the server at http://127.0.0.1:8000

**Terminal 2 - Start Vite (for CSS/JS):**
```bash
cd C:\xampp\htdocs\alpacabon-hardware
npm run dev
```

### Option 2: Using XAMPP Apache

1. Build assets for production:
   ```bash
   npm run build
   ```

2. Access via: http://localhost/alpacabon-hardware/public

### Stopping the System
- Press `Ctrl + C` in each terminal window to stop the servers

---

## Default Login Credentials

After running `php artisan db:seed`, use these credentials:

| Role | Email | Password |
|------|-------|----------|
| Administrator | test@example.com | password |

**Note:** Create additional users through the Users page after logging in.

---

## System Navigation

### Sidebar Menu

| Menu Item | Description | Access |
|-----------|-------------|--------|
| **Dashboard** | Overview of sales, transactions, and alerts | All users |
| **POS Terminal** | Process sales transactions | Cashiers, Admin |
| **Products** | Manage product catalog | Admin, Stock Manager |
| **Inventory** | Track and adjust stock levels | Admin, Stock Manager |
| **Transactions** | View transaction history | Admin, Cashiers |
| **Reports** | Sales reports and analytics | Admin, Managers |
| **Users** | Manage system users | Admin only |
| **Permissions** | Configure user access | Admin only |
| **Settings** | Store configuration | Admin only |

### Key Features

#### Dashboard
- Today's sales summary
- Transaction count
- Low stock alerts (notification bell)
- Weekly sales chart

#### POS Terminal
1. Search products by name or filter by category
2. Click product to open add-to-cart modal
3. Select quantity and add to cart
4. Review cart items (adjust quantities or remove)
5. Click **Checkout** to complete sale
6. Select payment method (Cash/GCash)
7. Print or view receipt

#### Products Management
- Add new products with images
- Edit product details
- Manage categories
- Set prices and stock quantities

#### Inventory Management
- View all products with stock levels
- Quick stock adjustments (+1/-1)
- Detailed adjustments with reasons
- Filter by stock status (All/Low/Out of Stock)

#### Reports
- Daily, Weekly, Monthly reports
- Custom date range
- Top selling products
- Revenue analytics

---

## User Roles & Permissions

### Default Roles

| Role | Description | Access |
|------|-------------|--------|
| **Administrator** | Full system access (protected, cannot be deleted) | All modules |
| **Cashier** | POS and transaction access | Dashboard, POS, Transactions |
| **Stock Manager** | Inventory management | Dashboard, Products, Inventory, Reports |

### Managing Roles
1. Go to **Users** page
2. Click **Add Role** to create custom roles
3. Select permissions for the new role
4. Assign roles to users when creating/editing

### Managing User Permissions
1. Go to **Permissions** page
2. Toggle checkboxes to enable/disable sidebar access per user
3. Administrators always have full access

---

## Troubleshooting

### Common Issues

**"Class not found" errors:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

**CSS/JS not loading:**
```bash
npm run build
```

**Database connection error:**
- Ensure MySQL is running in XAMPP
- Check `.env` database credentials

**Images not showing:**
```bash
php artisan storage:link
```

**Permission denied errors:**
- Run terminal as Administrator (Windows)

---

## Support

For technical support or questions, contact the development team.

---

*Alpacabon's Hardware - Point of Sale and Inventory Management System*
*Version 1.0*
