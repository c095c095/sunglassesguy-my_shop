# Comprehensive Website Workflow - My Shop Fishing Gear Store

## 1. Project Overview & Architecture
**My Shop** is a web-based e-commerce platform dedicated to selling fishing gear. The project is structured using native PHP with a custom routing engine, MySQL for the database, and Bootstrap for frontend styling, complemented by custom CSS and Swiper for interactive carousels.

### Tech Stack
* **Frontend:** HTML5, CSS3 (Bootstrap 5), JavaScript (Vanilla, Swiper.js, Bootstrap Plugins).
* **Backend:** PHP 8+ (Routing, Database queries, Business logic).
* **Database:** MySQL (MariaDB).
* **Security:** Role-based access control (Admin vs User vs Guest).

---

## 2. Database Structure (Domain Model)
The system is backed by a relational database consisting of the following key entities:
* **user**: Stores user accounts, including roles (`permission`: 2=Admin, 1=Customer, 0=Blocked).
* **product**: Contains product details (name, price, stock, images).
* **product_type**: Categorizes products.
* **cart**: Manages user shopping carts (links `user_id` and `product_id`).
* **order & order_detail**: Tracks customer purchases, linking users to specific products bought, total prices, and fulfillment status.
* **bank & payment**: Handles payment tracking and receipt uploads from customers.
* **banner**: Configurable homepage hero images.

---

## 3. Customer Workflow (Frontend)

### A. Browsing & Discovery (`pages/home.php`, `pages/products.php`, `pages/product.php`)
1. **Landing:** Visitors land on `index.php` (Home). They are presented with promotional banners and a Swiper carousel of product categories.
2. **Product Browsing:** Customers can click on specific categories to view filtered products or browse popular items.
3. **Product Details:** Clicking an item opens the product detail page, showing stock status, descriptions, and related products.

### B. Account Management (`pages/login.php`, `pages/register.php`, `pages/profile.php`)
1. **Registration:** New users must register (providing name, email, phone, and delivery address) to add items to their cart.
2. **Authentication:** Existing users log in. The system establishes a session and assigns a permission level.
3. **Profile Edit:** Once authenticated, users can update their profile information and view their `order-history`.

### C. Shopping & Cart (`pages/cart.php`, `pages/cart-*.php`)
1. **Add to Cart:** Authenticated users can add products. The system checks stock availability.
2. **Cart Management:** Users can increase (`cart-increase.php`) or decrease (`cart-decrease.php`) quantities or remove items entirely (`cart-remove.php`).
3. **Validation:** The system continuously validates quantities against the actual database `stock`.

### D. Checkout & Payment (`pages/confirm.php`, `pages/payment.php`)
1. **Confirmation:** Users review their cart. Delivery details are auto-filled from the user's profile.
2. **Delivery Methods:** Users select standard shipping (with dynamically calculated delivery fees based on order value) or store pickup.
3. **Payment:** Since it's a bank transfer mechanism, the customer proceeds to complete the order and uploads a transfer slip on the `payment.php` page.

---

## 4. Admin Workflow (Backend)

The administrative dashboard (accessible via `admin/index.php`) is restricted to accounts with permission level `2`. It offers full control over the storefront and business operations.

### A. Dashboard Overview (`admin/pages/home.php`)
* **Analytics/Metrics:** The admin's landing page provides a quick snapshot of daily orders, revenue, out-of-stock items, and pending payments.

### B. Order Management (`admin/pages/orders.php`, `admin/pages/order.php`)
* **Tracking:** Admins view all incoming orders.
* **Fulfillment:** They can update order statuses (e.g., Pending, Paid, Shipped, Cancelled).
* **Payment Verification:** Admins check the uploaded payment slips from the `payment` table to confirm transactions before shipping.

### C. Catalog Management (`admin/pages/products.php`, `admin/pages/product_types.php`, `admin/pages/product_stocks.php`)
* **Product Types:** Admins create and modify product categories.
* **Products:** Creating, editing, or deleting products, uploading product images, and setting prices.
* **Inventory Control:** Specifically adjusting stock metrics in `product_stocks.php` to prevent overselling on the frontend.

### D. User & Content Management (`admin/pages/users.php`, `admin/pages/banners.php`)
* **User Control:** Admins can view customer details, demote users, or block accounts by altering the `permission` level.
* **Homepage Banners:** Managing the hero section slides in `banners.php` to run active promotions.

---

## 5. Security & System Features
* **Routing (`core/services/route.php`):** Centralized control mapping URL queries (e.g., `?page=cart`) to specific PHP templates.
* **Session Validation:** Pages check session tokens strictly; e.g., the Admin Dashboard forcefully redirects non-admin users to the homepage.
* **File Uploads (`core/helpers/image_upload.php`):** Secure uploading of images separated tightly into categories (banners, payments, products).