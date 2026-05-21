# FixIt Hub Pakistan 🔧

**Pakistan's Premier Home Services Platform**  
Book verified technicians for mobile repair, electrician, plumbing, AC services and more.

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.1+-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 🚀 Features

### Core Features
- ✅ **User Registration & Authentication** — Secure login with password hashing
- ✅ **Service Browsing** — Browse categories, search, filter by price/category
- ✅ **Technician Profiles** — Verified technicians with ratings & reviews
- ✅ **Booking System** — Full booking lifecycle with 8 status workflow
- ✅ **Real-time Notifications** — 6 notification types (booking, message, payment, system, review, admin)
- ✅ **Messaging System** — Direct communication between users and technicians
- ✅ **Admin Dashboard** — Full platform management with Chart.js analytics
- ✅ **Review System** — Star ratings with technician replies

### Architecture & Security
- ✅ **Environment Configuration** — `.env` file for all credentials
- ✅ **MVC Structure** — Organized `app/` with controllers, models, helpers, middleware
- ✅ **JWT API Authentication** — Stateless tokens for mobile app support
- ✅ **CSRF Protection** — Token-based form protection
- ✅ **Rate Limiting** — File-based request throttling
- ✅ **Login Attempt Tracking** — Brute-force protection with account lockout
- ✅ **Secure Headers** — CSP, X-Frame-Options, XSS protection
- ✅ **File Upload Security** — Randomized filenames, type validation, PHP injection detection

### Payment Integration (Pakistan)
- ✅ **JazzCash** — Mock integration ready for production credentials
- ✅ **EasyPaisa** — Mock integration ready for production credentials
- ✅ **Cash on Delivery** — Default payment method
- ✅ **Bank Transfer** — Manual payment option
- ✅ **Refund Processing** — Automated refund workflow

### Performance
- ✅ **File Caching** — Categories, services, and homepage stats
- ✅ **Image Optimization** — WebP conversion, compression, auto-resize
- ✅ **Lazy Loading** — Native lazy loading for all images
- ✅ **Gzip Compression** — Apache mod_deflate configuration
- ✅ **Static Asset Caching** — Browser caching via mod_expires

### SEO
- ✅ **Dynamic Meta Tags** — Per-page title, description, keywords
- ✅ **Open Graph Tags** — Facebook/social media sharing
- ✅ **Twitter Cards** — Twitter sharing optimization
- ✅ **JSON-LD Structured Data** — Schema.org LocalBusiness markup
- ✅ **Dynamic Sitemap** — Auto-generated from database content
- ✅ **robots.txt** — Search engine crawl directives
- ✅ **Clean URLs** — Apache rewrite rules (no .php extensions)

### Frontend
- ✅ **Bootstrap 5.3** — Responsive UI framework
- ✅ **AOS Animations** — Scroll-based animations
- ✅ **Font Awesome 6** — Extended icon library
- ✅ **Bootstrap Icons** — Primary icon set
- ✅ **SweetAlert2** — Beautiful alert dialogs and confirmations
- ✅ **Chart.js 4** — Interactive admin analytics charts
- ✅ **Google Fonts (Inter)** — Modern typography

---

## 📁 Project Structure

```
fixithub/
├── app/                          # MVC Application Layer
│   ├── controllers/
│   │   └── Controller.php        # Base controller
│   ├── models/
│   │   ├── Model.php             # Base model (CRUD)
│   │   ├── BookingModel.php      # Booking with status workflow
│   │   ├── UserModel.php         # User operations
│   │   └── ServiceModel.php      # Service operations
│   ├── views/                    # Future view templates
│   ├── helpers/
│   │   ├── Cache.php             # File-based caching
│   │   ├── ImageOptimizer.php    # WebP conversion & compression
│   │   ├── PaymentGateway.php    # JazzCash & EasyPaisa
│   │   └── SEO.php               # Meta tags & Open Graph
│   └── middleware/
│       ├── Security.php          # Headers, rate limiting, uploads
│       └── JWTAuth.php           # API token authentication
├── admin/                        # Admin panel pages
├── api/                          # REST API endpoints
│   ├── auth.php                  # JWT login/refresh/me
│   ├── payment.php               # Payment initiation/callbacks
│   ├── notifications.php         # Notification API
│   └── search.php                # Search API
├── assets/
│   ├── css/style.css             # Main stylesheet
│   ├── js/app.js                 # Main JavaScript
│   └── images/                   # Static images
├── auth/                         # Authentication pages
├── config/
│   ├── env.php                   # Environment loader
│   ├── database.php              # PDO connection
│   ├── constants.php             # App constants
│   └── session.php               # Session management
├── database/
│   ├── schema.sql                # Database schema v1
│   ├── migration_v2.sql          # Enhanced schema migration
│   └── seed.sql                  # Sample data
├── includes/
│   ├── auth.php                  # Auth middleware
│   ├── csrf.php                  # CSRF protection
│   ├── functions.php             # Helper functions
│   ├── header.php                # Global header template
│   ├── footer.php                # Global footer template
│   ├── navbar.php                # Navigation bar
│   └── sidebar.php               # Dashboard sidebar
├── technician/                   # Technician panel
├── user/                         # User panel
├── uploads/                      # User uploads
├── cache/                        # File cache (auto-created)
├── .env                          # Environment config (DO NOT COMMIT)
├── .env.example                  # Environment template
├── .gitignore                    # Git ignore rules
├── .htaccess                     # Apache config & security
├── router.php                    # PHP dev server router
├── robots.txt                    # SEO crawl rules
├── sitemap.php                   # Dynamic XML sitemap
└── README.md                     # This file
```

---

## ⚡ Quick Start

### Prerequisites
- PHP 8.1+ with GD extension
- MySQL 8.0+
- Apache with mod_rewrite (or PHP built-in server)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/fixithub-pakistan.git
cd fixithub-pakistan

# 2. Copy environment file
cp .env.example .env

# 3. Edit .env with your database credentials
# DB_HOST=localhost
# DB_NAME=fixithub_pakistan
# DB_USER=root
# DB_PASS=

# 4. Import database
mysql -u root -p < database/schema.sql
mysql -u root -p fixithub_pakistan < database/seed.sql
mysql -u root -p fixithub_pakistan < database/migration_v2.sql

# 5. Start development server
php -S localhost:8000 router.php

# 6. Open in browser
# http://localhost:8000
```

### Demo Accounts
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@fixithub.pk | password |
| User | ahmed@example.com | password |
| Technician | usman@example.com | password |

---

## 🔄 Booking Status Flow

```
pending → accepted → technician_on_way → in_progress → completed
    ↓         ↓              ↓                ↓            ↓
cancelled  cancelled     cancelled         disputed     refunded
                                               ↓
                                           refunded / completed
```

---

## 🔐 API Documentation

### Authentication
```bash
# Login (get JWT tokens)
POST /api/auth?action=login
Body: { "email": "user@example.com", "password": "password" }

# Refresh token
POST /api/auth?action=refresh
Body: { "refresh_token": "..." }

# Get current user
GET /api/auth?action=me
Header: Authorization: Bearer <access_token>
```

### Payment
```bash
# Initiate payment
POST /api/payment?action=initiate
Body: { "booking_id": 1, "method": "jazzcash" }

# Get payment methods
GET /api/payment?action=methods
```

---

## 🌿 Git Workflow

```
main                    # Production-ready code
├── development         # Integration branch
│   ├── feature/auth    # Authentication features
│   ├── feature/bookings # Booking system
│   ├── feature/payments # Payment integration
│   └── feature/admin   # Admin dashboard
```

### Branch Guidelines
1. Create feature branches from `development`
2. Use descriptive names: `feature/`, `fix/`, `hotfix/`
3. Submit pull requests to `development`
4. Merge `development` → `main` for releases

---

## 🔮 Future Expansion Roadmap

### Phase 1: Mobile App
- 📱 **Android App** — React Native / Flutter app using JWT API
- 📱 **iOS App** — Cross-platform mobile support
- 🔔 **Push Notifications** — Firebase Cloud Messaging

### Phase 2: Advanced Features
- 📍 **Technician Live Tracking** — Real-time GPS tracking on map
- 🤖 **AI Chatbot** — Automated customer support with NLP
- 🎤 **Voice Booking** — Book services via voice commands
- 📊 **Advanced Analytics** — ML-powered demand forecasting

### Phase 3: Business Growth
- 💳 **Subscription Plans** — Monthly/yearly service packages
- 🏙️ **Multi-City Support** — Expand to all major Pakistani cities
- 🌐 **Multi-Language** — Urdu, Sindhi, Punjabi localization
- 🏢 **Corporate Accounts** — B2B service contracts
- 📋 **Service Warranty** — Extended warranty tracking

### Phase 4: Ecosystem
- 👨‍🔧 **Technician Training** — In-app certification courses
- 🏪 **Parts Marketplace** — Buy repair parts through the app
- 📈 **Franchise Model** — Expand via franchised operators
- 🔗 **Third-party Integrations** — Daraz, Foodpanda style partnerships

---

## 🛡️ Security Features

| Feature | Implementation |
|---------|---------------|
| Password Hashing | bcrypt via `password_hash()` |
| CSRF Protection | Token-based with `hash_equals()` |
| SQL Injection | PDO prepared statements |
| XSS Prevention | `htmlspecialchars()` output escaping |
| Session Security | HttpOnly, SameSite, secure cookies |
| Rate Limiting | File-based request throttling |
| Login Lockout | 5 attempts → 15 min lockout |
| Secure Headers | CSP, X-Frame, XSS-Protection |
| File Upload | Type validation, PHP detection, random names |
| JWT Auth | HMAC-SHA256 signed tokens |

---

## 📄 License

This project is developed for educational purposes as a university project.

---

**Built with ❤️ in Pakistan** | FixIt Hub Pakistan © 2026
