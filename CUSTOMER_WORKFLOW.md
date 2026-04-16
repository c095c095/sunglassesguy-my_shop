# Customer Normal Workflow - My Shop Fishing Gear Store

## Overview
This document outlines the standard customer workflow for the My Shop fishing gear e-commerce platform. The workflow covers the complete customer journey from initial visit to order completion.

## Customer Workflow Steps

### 1. Initial Visit & Browsing
**Entry Point:** `index.php` → `pages/home.php`
- Customer lands on the homepage
- Views promotional banners (carousel)
- Browses product categories (fishing gear types) displayed in swiper
- Sees popular products organized by category
- Can view store benefits (free shipping, quality products, secure payment, 24/7 support)

### 2. Product Discovery
**Pages:** `pages/products.php`, `pages/product.php`

#### Category Browsing:
- Click on product type from homepage swiper
- View filtered products by category
- See product thumbnails, names, and prices

#### Product Detail View:
- Click on specific product
- View detailed product information:
  - Product image
  - Stock availability status
  - Product name and type
  - Price (฿)
  - Quantity selector
  - Product description/details
  - Related products from same category

### 3. Authentication (Required for Purchase)
**Pages:** `pages/login.php`, `pages/register.php`

#### For New Customers:
1. Click "หยิบใส่ตะกร้า" (Add to Cart) button redirects to login
2. Click "สร้างบัญชี" (Create Account) link
3. Fill registration form:
   - First name, Last name
   - Phone number
   - Email address
   - Delivery address
   - Username
   - Password
   - Accept terms and conditions
4. Submit → Account created → Redirect to login

#### For Existing Customers:
1. Enter username and password
2. Submit → Validated → Session created
3. Redirect to homepage (permission level 1) or admin panel (permission level 2)
4. Blocked accounts (permission level 0) are denied access

### 4. Shopping Cart Management
**Pages:** `pages/cart.php`, `pages/cart-increase.php`, `pages/cart-decrease.php`, `pages/cart-remove.php`

#### Cart Operations:
- **Add Item:** Click "หยิบใส่ตะกร้า" on product/homepage
- **View Cart:** Access cart page showing:
  - All cart items with thumbnails
  - Product name, price, quantity
  - Total price per item
  - Grand total
- **Increase Quantity:** Click "+" button (respects stock limits)
- **Decrease Quantity:** Click "-" button
- **Remove Item:** Click "X" button
- **Continue Shopping:** Browse more products
- **Proceed to Checkout:** Click "ดำเนินการต่อ" button

#### Cart Validation:
- Automatically removes deleted products
- Adjusts quantities if stock is reduced
- Validates stock availability
- Requires authentication

### 5. Order Confirmation & Checkout
**Page:** `pages/confirm.php`

#### Shipping Information:
- Pre-filled from user profile
- Editable fields:
  - First name, Last name
  - Phone number
  - Delivery address

#### Delivery Method Selection:
1. **Standard Delivery (จัดส่งปกติ):**
   - ฿50 fee for orders under ฿500
   - FREE for orders ฿500 and above
2. **Self Pickup (รับเองที่ร้าน):**
   - No delivery fee

#### Order Summary:
- List of all items with quantities
- Subtotal calculation
- Delivery fee
- Price before tax
- VAT 7%
- Net total
- Accept privacy policy and terms checkbox

#### Submit Order:
- Click "ดำเนินการชำระเงิน" (Proceed to Payment)
- Creates order record in database
- Creates order detail records
- Updates product stock
- Clears cart
- Redirects to payment page

### 6. Payment Processing
**Page:** `pages/payment.php`

#### Payment Information:
- Order details displayed:
  - Order number (#ORDER-{id})
  - Order date
  - Order status
  - Total amount to pay (total + delivery fee)

#### Payment Form:
1. **Upload Payment Slip:**
   - File upload for transfer evidence
   - Supported formats: JPG, JPEG, PNG, GIF, WEBP
   - Max file size limit
   - Image preview before submission

2. **Payment Details:**
   - Payment date (date picker)
   - Payment time (time picker)
   - Select bank account (radio buttons):
     - Multiple Thai banks supported (K-Bank, SCB, KTB, BBL, etc.)
     - Shows bank logo, account number, account name
     - PromptPay option available

3. **Submit Payment:**
   - Click "แจ้งโอนเงิน" (Notify Transfer)
   - Uploads payment slip image
   - Creates payment record
   - Updates order status to "รอตรวจสอบ" (Awaiting Verification) - Status 2
   - Redirects to order detail page

### 7. Order Tracking & History
**Pages:** `pages/order-history.php`, `pages/order-detail.php`

#### Order History:
- View all orders with filtering tabs:
  - **ทั้งหมด** (All) - All orders
  - **ทีต้องชำระ** (Unpaid) - Status 1
  - **ที่รอตรวจสอบ** (Pending Verification) - Status 2
  - **ที่ต้องจัดส่ง** (Awaiting Shipment) - Status 3
  - **สำเร็จแล้ว** (Delivered) - Status 4
  - **ยกเลิก** (Canceled) - Status 0

- Each order displays:
  - Order number
  - Order date
  - Status badge (color-coded)
  - Tracking number (if available)
  - Total amount
  - Link to order details

#### Order Status Flow:
```
Status 0: ยกเลิก (Canceled)
Status 1: รอชำระเงิน (Awaiting Payment) ← Initial after checkout
Status 2: รอตรวจสอบ (Awaiting Verification) ← After payment slip submitted
Status 3: รอจัดส่ง (Awaiting Shipment) ← After admin verifies payment
Status 4: จัดส่งสำเร็จ (Delivered Successfully) ← After shipping completed
```

### 8. Account Management
**Pages:** `pages/profile.php`, `pages/profile-edit.php`, `pages/change-password.php`

#### Profile Dashboard:
- User information display:
  - Name and username
  - Order statistics (all, delivered, pending, unpaid)
  - Email, phone, address

#### Profile Actions:
- **Edit Profile:** Update personal information
- **Change Password:** Update account password
- **View Order History:** Access complete order history

### 9. Additional Pages
- **FAQ:** `pages/faq.php` - Frequently asked questions
- **Contact:** `pages/contact.php` - Contact information
- **404 Error:** `pages/404.php` - Page not found

## Complete Customer Journey Flow

```
1. Visit Homepage
   ↓
2. Browse Products (by category or search)
   ↓
3. View Product Details
   ↓
4. Login/Register (if not authenticated)
   ↓
5. Add Products to Cart
   ↓
6. Manage Cart (adjust quantities, remove items)
   ↓
7. Proceed to Checkout
   ↓
8. Enter/Confirm Shipping Information
   ↓
9. Select Delivery Method
   ↓
10. Review Order Summary
    ↓
11. Submit Order (creates order with status 1)
    ↓
12. Make Payment (bank transfer)
    ↓
13. Upload Payment Slip (changes status to 2)
    ↓
14. Admin Verifies Payment (changes status to 3)
    ↓
15. Admin Ships Order (adds tracking, changes status to 4)
    ↓
16. Customer Receives Order
    ↓
17. View Order History & Track Orders
```

## Key Features

### Security & Validation
- Password hashing with bcrypt
- Session-based authentication
- Stock validation before order
- File upload validation for payment slips
- CSRF protection through session validation

### User Experience
- Responsive design (mobile-friendly)
- Real-time cart quantity updates
- Image preview for payment slips
- Color-coded order status badges
- Automatic cart cleanup for unavailable items
- Free shipping incentive (orders over ฿500)

### Business Rules
- Minimum order quantity: 1
- Maximum order quantity: Available stock
- Free shipping threshold: ฿500
- VAT: 7% (included in price)
- Delivery fee: ฿50 (standard, waived for orders ≥฿500)
- Self-pickup: Free

## Database Tables Used
- `user` - Customer accounts
- `product` - Product catalog
- `product_type` - Product categories
- `cart` - Shopping cart items
- `order` - Order headers
- `order_detail` - Order line items
- `payment` - Payment records
- `bank` - Bank account information
- `banner` - Homepage banners

## Session Management
- `$_SESSION['uid']` - User ID
- `$_SESSION['permission']` - User permission level (0=blocked, 1=customer, 2=admin)
- `$_SESSION['name']` - User full name

## Error Handling
- Stock validation messages
- File upload error messages
- Database operation error messages
- Authentication error messages
- Form validation alerts

---

**Note:** This workflow is designed for a Thai fishing gear e-commerce platform with manual bank transfer payment processing and admin verification.