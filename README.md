# Multi-Vendor E-Commerce API

A complete, production-ready RESTful API for a multi-vendor e-commerce platform built with Laravel 10. This API supports multiple vendors, product management, shopping cart, order processing, and admin controls.

## 🚀 Features

### Core Features
- ✅ **User Authentication** - Token-based authentication using Laravel Sanctum
- ✅ **Multi-Role System** - Customer, Vendor, and Admin roles with permissions
- ✅ **Multi-Vendor Architecture** - Multiple vendors can sell on the platform
- ✅ **Product Management** - Complete CRUD operations for products
- ✅ **Advanced Search & Filtering** - Search by name, filter by category, brand, price, etc.
- ✅ **Shopping Cart** - Add/remove items with stock validation
- ✅ **Checkout Process** - Complete order placement with multi-vendor splitting
- ✅ **Order Management** - Track orders, update status, cancel orders
- ✅ **Review System** - Customers can review products
- ✅ **Wishlist** - Save favorite products
- ✅ **File Upload** - Product images with automatic thumbnail generation
- ✅ **Admin Dashboard** - Statistics and analytics
- ✅ **Vendor Approval** - Admin can approve/reject vendor registrations

### Technical Features
- ✅ RESTful API design
- ✅ Database transactions for data integrity
- ✅ Stock management with automatic updates
- ✅ Image processing and optimization
- ✅ Pagination on all list endpoints
- ✅ Eager loading to prevent N+1 queries
- ✅ Comprehensive validation
- ✅ Soft deletes for important records

---

## 📋 Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7 or MariaDB
- Laravel 10.x
- GD Library or Imagick (for image processing)

---

## 🛠️ Installation

### 1. Clone the repository
```bash
git clone https://github.com/yourusername/multivendor-api.git
cd multivendor-api
```

### 2. Install dependencies
```bash
composer install
```

### 3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multivendor_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Run migrations and seeders
```bash
php artisan migrate --seed
```

This will create:
- 1 admin user (email: `admin@example.com`, password: `password`)
- 5 approved vendors
- 20 customers
- 20 categories (nested)
- 10 brands
- 50-100 products with images and reviews

### 6. Create storage symlink
```bash
php artisan storage:link
```

### 7. Start the development server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

---

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
Most endpoints require authentication using Bearer token:
```
Authorization: Bearer {your_token}
```

---

## 🔐 Authentication Endpoints

### Register Customer
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890"
}
```

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "access_token": "1|abcd1234...",
    "token_type": "Bearer"
  }
}
```

### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Get Profile
```http
GET /api/auth/profile
Authorization: Bearer {token}
```

### Update Profile
```http
PUT /api/auth/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Updated",
  "phone": "081234567890"
}
```

### Change Password
```http
POST /api/auth/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "oldpassword",
  "new_password": "newpassword",
  "new_password_confirmation": "newpassword"
}
```

---

## 🏷️ Category & Brand Endpoints

### List Categories
```http
GET /api/categories
```

### Get Single Category
```http
GET /api/categories/{slug}
```

### List Brands
```http
GET /api/brands
```

### Get Single Brand
```http
GET /api/brands/{slug}
```

---

## 🛍️ Product Endpoints

### List Products
```http
GET /api/products
GET /api/products?search=laptop
GET /api/products?category_id=1
GET /api/products?brand_id=2
GET /api/products?min_price=100&max_price=1000
GET /api/products?sort_by=price&sort_order=asc
GET /api/products?is_featured=1
GET /api/products?on_sale=1
GET /api/products?per_page=20
```

### Get Product Details
```http
GET /api/products/{slug}
```

### Featured Products
```http
GET /api/products/featured
```

### Products On Sale
```http
GET /api/products/on-sale
```

### Get Product Reviews
```http
GET /api/products/{productId}/reviews
```

### Submit Product Review
```http
POST /api/products/{productId}/reviews
Authorization: Bearer {token}
Content-Type: application/json

{
  "rating": 5,
  "comment": "Great product!",
  "images": ["review1.jpg", "review2.jpg"]
}
```

---

## ❤️ Wishlist Endpoints

### View Wishlist
```http
GET /api/wishlist
Authorization: Bearer {token}
```

### Add to Wishlist
```http
POST /api/wishlist/{productId}
Authorization: Bearer {token}
```

### Remove from Wishlist
```http
DELETE /api/wishlist/{productId}
Authorization: Bearer {token}
```

### Check if in Wishlist
```http
GET /api/wishlist/check/{productId}
Authorization: Bearer {token}
```

---

## 🛒 Cart Endpoints

### View Cart
```http
GET /api/cart
Authorization: Bearer {token}
```

### Add to Cart
```http
POST /api/cart/items
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2,
  "product_variant_id": null
}
```

### Update Cart Item
```http
PUT /api/cart/items/{itemId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 3
}
```

### Remove from Cart
```http
DELETE /api/cart/items/{itemId}
Authorization: Bearer {token}
```

### Clear Cart
```http
DELETE /api/cart/clear
Authorization: Bearer {token}
```

---

## 📍 Address Endpoints

### List Addresses
```http
GET /api/addresses
Authorization: Bearer {token}
```

### Create Address
```http
POST /api/addresses
Authorization: Bearer {token}
Content-Type: application/json

{
  "full_name": "John Doe",
  "phone": "081234567890",
  "address_line_1": "123 Main Street",
  "address_line_2": "Apt 4B",
  "city": "Surabaya",
  "state": "East Java",
  "postal_code": "60111",
  "country": "Indonesia",
  "type": "both",
  "is_default": true
}
```

### Get Single Address
```http
GET /api/addresses/{id}
Authorization: Bearer {token}
```

### Update Address
```http
PUT /api/addresses/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

### Delete Address
```http
DELETE /api/addresses/{id}
Authorization: Bearer {token}
```

### Set Default Address
```http
POST /api/addresses/{id}/set-default
Authorization: Bearer {token}
```

---

## 📦 Order Endpoints

### Checkout
```http
POST /api/checkout
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "payment_method": "cod",
  "notes": "Please deliver in the morning"
}
```

**Payment Methods:** `cod`, `bank_transfer`, `credit_card`, `e-wallet`

### List Orders
```http
GET /api/orders
GET /api/orders?status=pending
GET /api/orders?payment_status=paid
Authorization: Bearer {token}
```

### Get Order Details
```http
GET /api/orders/{orderNumber}
Authorization: Bearer {token}
```

### Cancel Order
```http
POST /api/orders/{orderNumber}/cancel
Authorization: Bearer {token}
```

---

## 🏪 Vendor Endpoints

### List My Products
```http
GET /api/vendor/products
GET /api/vendor/products?is_active=1
GET /api/vendor/products?search=laptop
Authorization: Bearer {vendor_token}
```

### Get My Product
```http
GET /api/vendor/products/{id}
Authorization: Bearer {vendor_token}
```

### Create Product
```http
POST /api/vendor/products
Authorization: Bearer {vendor_token}
Content-Type: application/json

{
  "category_id": 1,
  "brand_id": 2,
  "name": "New Laptop Model X",
  "description": "High performance laptop with amazing features",
  "short_description": "Best laptop for developers",
  "price": 1200.00,
  "sale_price": 1000.00,
  "cost_price": 800.00,
  "stock_quantity": 50,
  "weight": 2.5,
  "dimensions": {
    "length": 35,
    "width": 25,
    "height": 2
  },
  "is_featured": false
}
```

### Update Product
```http
PUT /api/vendor/products/{id}
Authorization: Bearer {vendor_token}
Content-Type: application/json
```

### Delete Product
```http
DELETE /api/vendor/products/{id}
Authorization: Bearer {vendor_token}
```

### List My Orders
```http
GET /api/vendor/orders
GET /api/vendor/orders?status=pending
GET /api/vendor/orders?search=ORD-20251221
Authorization: Bearer {vendor_token}
```

### Get Order Details
```http
GET /api/vendor/orders/{orderNumber}
Authorization: Bearer {vendor_token}
```

### Update Order Status
```http
PUT /api/vendor/orders/{orderNumber}/status
Authorization: Bearer {vendor_token}
Content-Type: application/json

{
  "status": "processing",
  "tracking_number": "TRACK123456"
}
```

**Status Flow:** `pending` → `processing` → `shipped` → `delivered`

### Get Statistics
```http
GET /api/vendor/orders/statistics
Authorization: Bearer {vendor_token}
```

---

## 👨‍💼 Admin Endpoints

### Dashboard Statistics
```http
GET /api/admin/dashboard
Authorization: Bearer {admin_token}
```

**Response includes:**
- Total users, customers, vendors
- Product statistics
- Order statistics
- Revenue data (total, today, this month)

### Recent Orders
```http
GET /api/admin/dashboard/recent-orders
Authorization: Bearer {admin_token}
```

### Top Products
```http
GET /api/admin/dashboard/top-products
Authorization: Bearer {admin_token}
```

### Sales Chart Data
```http
GET /api/admin/dashboard/sales-chart
Authorization: Bearer {admin_token}
```

### List All Vendors
```http
GET /api/admin/vendors
GET /api/admin/vendors?status=pending
GET /api/admin/vendors?search=shop
Authorization: Bearer {admin_token}
```

### Approve Vendor
```http
POST /api/admin/vendors/{id}/approve
Authorization: Bearer {admin_token}
```

### Reject Vendor
```http
POST /api/admin/vendors/{id}/reject
Authorization: Bearer {admin_token}
```

### Suspend Vendor
```http
POST /api/admin/vendors/{id}/suspend
Authorization: Bearer {admin_token}
```

### Update Vendor Commission
```http
PUT /api/admin/vendors/{id}/commission
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "commission_rate": 15.00
}
```

---

## 📤 File Upload Endpoints

### Upload Product Image
```http
POST /api/upload/product-image
Authorization: Bearer {token}
Content-Type: multipart/form-data

image: [file]
```

**Response:**
```json
{
  "success": true,
  "message": "Image uploaded successfully",
  "data": {
    "image_path": "products/1703123456_abc123.jpg",
    "thumbnail_path": "products/thumbnails/1703123456_abc123.jpg",
    "full_url": "http://localhost:8000/storage/products/1703123456_abc123.jpg",
    "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/1703123456_abc123.jpg"
  }
}
```

### Upload Avatar
```http
POST /api/upload/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

avatar: [file]
```

### Delete Image
```http
DELETE /api/upload/image
Authorization: Bearer {token}
Content-Type: application/json

{
  "path": "products/image.jpg"
}
```

---

## 🗂️ Database Structure

### Main Tables
- **users** - All users (customers, vendors, admins)
- **vendors** - Vendor shop information
- **vendor_settings** - Vendor-specific settings
- **categories** - Product categories (nested)
- **brands** - Product brands
- **products** - Product catalog
- **product_images** - Product images
- **product_variants** - Product variations (size, color, etc.)
- **attributes** - Variant attributes
- **attribute_values** - Attribute values
- **carts** - Shopping carts
- **cart_items** - Cart contents
- **addresses** - User addresses
- **orders** - Customer orders
- **order_items** - Order line items
- **payments** - Payment records
- **transactions** - Payment transactions
- **reviews** - Product reviews
- **wishlists** - User wishlists
- **settings** - App settings

---

## 🔒 User Roles & Permissions

### Customer
- Browse products
- Add to cart & wishlist
- Place orders
- Write reviews
- Manage profile & addresses

### Vendor
- All customer permissions
- Manage own products
- View own orders
- Update order status
- View sales statistics

### Admin
- All vendor permissions
- Approve/reject vendors
- View all orders & products
- Access dashboard analytics
- Manage vendor commissions
- System-wide management

---

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

### Pagination Response
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

---

## 🧪 Testing

### Default Test Accounts

**Admin:**
- Email: `admin@example.com`
- Password: `password`

**Sample Vendor:**
- Check database for vendor user emails
- Password: `password` (for seeded accounts)

**Sample Customer:**
- Check database for customer user emails
- Password: `password` (for seeded accounts)

### Testing with Postman
1. Import API endpoints into Postman
2. Set environment variable `base_url` = `http://localhost:8000/api`
3. Set environment variable `token` after login
4. Use `{{base_url}}` and `{{token}}` in requests

---

## 🚀 Deployment

### Environment Variables
Update `.env` for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Add your email/queue/cache configs
```

### Optimization Commands
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

---

## 🛡️ Security

- All sensitive routes protected with authentication
- Role-based access control (RBAC)
- Password hashing with bcrypt
- Token-based authentication
- CSRF protection
- SQL injection prevention via Eloquent
- XSS protection
- Input validation on all endpoints

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author

**Your Name**
- GitHub: [@Josey34](https://github.com/Josey34)
- Email: joseytakesan1@gmail.com

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

---

## ⭐ Show your support

Give a ⭐️ if this project helped you!

---

## 📞 Support

For support, email joseytakesan1@gmail.com or open an issue on GitHub.
