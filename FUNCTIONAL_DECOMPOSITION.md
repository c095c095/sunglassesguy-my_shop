# Functional Decomposition Diagram

## System: Online Fishing Gear Store

```
Online Fishing Gear Store
├── Admin Dashboard
│   ├── Banner Management
│   │   ├── Create Banners
│   │   ├── Edit Banners
│   │   ├── Delete Banners
│   │   └── View Banners
│   ├── Product Management
│   │   ├── Add Products
│   │   ├── Edit Products
│   │   ├── Delete Products
│   │   ├── View Products
│   │   └── Manage Product Images
│   ├── Product Stock Control
│   │   ├── Update Stock Levels
│   │   ├── View Stock Status
│   │   └── Low Stock Alerts
│   ├── Product Type Management
│   │   ├── Add Product Types
│   │   ├── Edit Product Types
│   │   ├── Delete Product Types
│   │   └── View Product Types
│   ├── Order Management
│   │   ├── View Orders
│   │   ├── Update Order Status
│   │   ├── View Order Details
│   │   └── Process Payments
│   ├── User Management
│   │   ├── View Users
│   │   ├── Edit User Info
│   │   ├── Manage User Roles
│   │   └── Deactivate Users
│   ├── Profile Management
│   │   ├── View Admin Profile
│   │   ├── Edit Profile Info
│   │   └── Change Password
│   └── Authentication
│       ├── Admin Login
│       └── Admin Logout
├── Customer Portal
│   ├── Authentication
│   │   ├── Customer Login
│   │   ├── Customer Registration
│   │   ├── Change Password
│   │   └── Customer Logout
│   ├── Product Browsing
│   │   ├── Home Page
│   │   ├── Products List
│   │   ├── Product Detail View
│   │   └── Search Products
│   ├── Shopping Cart
│   │   ├── Add to Cart
│   │   ├── View Cart
│   │   ├── Increase Quantity
│   │   ├── Decrease Quantity
│   │   └── Remove Items
│   ├── Order Processing
│   │   ├── Order Confirmation
│   │   ├── Payment Processing
│   │   ├── Order History
│   │   └── Order Detail View
│   └── Account Management
│       ├── View Profile
│       ├── Edit Profile
│       ├── View Order History
│       └── Logout
└── System Core
    ├── Database Services
    │   ├── Connection Management
    │   ├── Query Execution
    │   └── Data Retrieval
    ├── Configuration
    │   ├── Database Config
    │   ├── Routes Config
    │   └── Website Config
    ├── Helpers & Utilities
    │   ├── Data Processing
    │   ├── Image Upload
    │   └── Utility Functions
    └── Routing
        ├── Page Routing
        ├── Route Handling
        └── 404 Handling
```

## Function Categories

### Administrative Functions
1. **Content Management**
   - Banner CRUD operations
   - Product type management
   - Product catalog management

2. **Inventory Control**
   - Stock level monitoring
   - Stock updates
   - Inventory alerts

3. **Order Administration**
   - Order tracking
   - Status updates
   - Payment verification

4. **User Administration**
   - Customer account management
   - Role assignment
   - Access control

### Customer Functions
1. **Account Services**
   - Registration/Login
   - Profile management
   - Password management

2. **Shopping Experience**
   - Product browsing
   - Cart management
   - Checkout process

3. **Order Tracking**
   - Order placement
   - Order history
   - Payment processing

### System Functions
1. **Data Management**
   - Database operations
   - Data validation
   - Data persistence

2. **File Handling**
   - Image uploads
   - File management
   - Media processing

3. **Navigation**
   - URL routing
   - Page rendering
   - Error handling