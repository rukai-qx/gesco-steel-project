# Gesco Steel Project

A PHP + MySQL storefront and admin panel for a steel and hardware supplies business.

## Features

- Customer accounts: registration, login, password reset, profile and password updates
- Product catalog with image uploads (`uploads/`)
- Shopping cart and checkout (`cart.php`, `add_to_cart.php`, `checkout.php`)
- Order history, returns and reviews
- Admin dashboard with item, stock, staff, customer and manager management
- Sales and activity reports

## Requirements

- PHP 8.x with the `mysqli` extension
- MySQL / MariaDB
- Composer

## Setup

1. Install dependencies:

   ```sh
   composer install
   ```

2. Create a MySQL database and import your schema.

3. Configure the database connection. Each entry script declares its own
   connection settings near the top, for example:

   ```php
   $servername = "localhost";
   $username   = "root";
   $password   = "";
   $dbname     = "gesco";
   ```

   Fill in the values for your environment. Do not commit real credentials.

4. Serve the project with Apache/Nginx, or for local development:

   ```sh
   php -S localhost:8000
   ```

5. Open `index.php` in your browser.

## Project layout

| Path | Purpose |
| --- | --- |
| `index.php` | Storefront entry point |
| `login111.php`, `Register1.html` | Authentication pages |
| `cart.php`, `checkout.php` | Cart and checkout flow |
| `admindash.php`, `adminitem.php` | Admin dashboard and product admin |
| `viewstock.php`, `view_orders.php` | Inventory and order views |
| `returns.php`, `Reviews.php` | Returns and reviews |
| `report.php`, `activity.php` | Reporting |
| `uploads/` | Product images |
| `vendor/` | Composer dependencies (not committed) |
