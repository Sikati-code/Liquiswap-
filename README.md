# LiquiSwap - P2P Financial Exchange Platform

A premium P2P financial exchange platform for Central Africa, allowing users to swap value between MTN MoMo, Orange Money, Express Union, bank accounts, and cash. Features bundle purchases, airtime-to-bundle conversion, USSD code library, and secure escrow protection.

## 🚀 Features

- **Multi-Operator Support**: MTN, Orange, Camtel, Nexttel, Express Union
- **Instant Swaps**: OM ↔ MOMO transfers with real-time fee calculation
- **Bundle Marketplace**: Purchase data bundles from all major operators
- **Airtime Conversion**: Convert airtime to data bundles instantly
- **USSD Library**: Quick access to all operator USSD codes
- **Secure Transactions**: Escrow protection and biometric authentication
- **Transaction History**: Complete transaction tracking and analytics
- **Responsive Design**: Works seamlessly on all devices

## 🛠 Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript, Bootstrap 5
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0
- **Authentication**: PHP Sessions with password hashing
- **Security**: CSRF protection, input sanitization, SQL injection prevention

## 📋 Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- phpMyAdmin (for database management)

## 🗄 Database Setup

1. **Create Database**:
   ```sql
   CREATE DATABASE liquiswap_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Schema**:
   - Open phpMyAdmin
   - Select `liquiswap_db` database
   - Import `sql/liquiswap_db.sql` file
   - This will create all tables and insert sample data

3. **Update Database Credentials**:
   Edit `includes/config.php` and update your database settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'liquiswap_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

## 📁 Project Structure

```
liquiswap/
├── index.php                 # Main entry point
├── splash.php                # Splash screen with preloader
├── login.php                 # Login page
├── dashboard.php             # Main dashboard
├── swap.php                  # OM ↔ MOMO swap page
├── bundles.php               # Bundles marketplace
├── airtime-bundle.php        # Airtime → Bundle conversion
├── ussd.php                  # USSD codes library
├── history.php               # Transaction history
├── profile.php               # User profile
├── settings.php              # Settings page
├── logout.php                # Logout handler
│
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/                  # JavaScript files
│   ├── images/               # Images and icons
│   └── fonts/               # Custom fonts
│
├── includes/
│   ├── config.php            # Database configuration
│   ├── Database.php          # MySQL PDO connection
│   ├── Auth.php              # Authentication functions
│   └── functions.php        # Helper functions
│
├── api/                     # AJAX endpoints
└── sql/
    └── liquiswap_db.sql      # Database schema and sample data
```

## 🔧 Installation Steps

1. **Download/Clone** project to your web server directory

2. **Configure Database**:
   - Create `liquiswap_db` database
   - Import SQL file from `sql/liquiswap_db.sql`
   - Update database credentials in `includes/config.php`

3. **Set Permissions**:
   ```bash
   chmod 755 -R /path/to/liquiswap
   chmod 777 -R /path/to/liquiswap/assets
   ```

4. **Configure Web Server**:
   
   **Apache (.htaccess)**:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```
   
   **Nginx**:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

5. **Access Application**:
   - Open your browser and navigate to `http://localhost/liquiswap/`
   - You'll see the splash screen, then redirected to login

## 👤 Default Login

Use these credentials to test the application:

- **Phone**: +237699999999
- **Password**: password123

## 🎯 Key Features Usage

### 1. Dashboard
- View unified balance across all wallets
- Quick access to all major functions
- Recent transactions feed
- Curated deals and offers

### 2. Swapping Money
- Select source and destination wallets
- Real-time fee calculation
- Secure escrow protection
- Transaction tracking

### 3. Bundle Purchases
- Browse bundles by operator
- Filter by price and data amount
- Instant activation
- Transaction history

### 4. Airtime Conversion
- Select airtime source
- Choose data bundle
- Instant conversion
- Bonus data offers

### 5. USSD Codes
- Quick access to operator codes
- Search functionality
- Copy to clipboard
- Direct dial support

## 🔒 Security Features

- **Password Hashing**: Bcrypt with salt
- **Session Management**: Secure PHP sessions
- **CSRF Protection**: Token-based validation
- **SQL Injection Prevention**: Prepared statements
- **Input Sanitization**: XSS protection
- **Biometric Authentication**: Face ID/fingerprint support

## 📱 Mobile Responsiveness

The application is fully responsive and works on:
- Desktop (1024px+)
- Tablet (768px-1023px)
- Mobile (320px-767px)

## 🎨 UI/UX Features

- **Dark Theme**: Easy on the eyes
- **Liquid Animations**: Smooth transitions
- **Glass Morphism**: Modern card designs
- **Micro-interactions**: Hover states and feedback
- **Loading States**: Progress indicators
- **Error Handling**: User-friendly messages

## 🔄 API Endpoints

The application includes AJAX endpoints for:
- Bundle fetching (`api/get_bundles.php`)
- USSD codes (`api/get_ussd.php`)
- Swap calculation (`api/calculate_swap.php`)
- Transaction creation (`api/create_swap.php`)

## 🐛 Troubleshooting

### Common Issues:

1. **Database Connection Error**:
   - Check MySQL credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **404 Errors**:
   - Check .htaccess configuration
   - Ensure mod_rewrite is enabled (Apache)
   - Verify file permissions

3. **Session Issues**:
   - Check PHP session save path permissions
   - Ensure session.cookie_secure is properly set
   - Clear browser cookies

4. **Blank Pages**:
   - Enable PHP error reporting
   - Check syntax errors in PHP files
   - Verify file paths in includes

## 📞 Support

For support and issues:
- Check error logs in your web server
- Verify database connectivity
- Ensure all file permissions are correct
- Test with different browsers

## 🚀 Deployment

For production deployment:

1. **Environment Setup**:
   - Set `error_reporting(0)` in production
   - Configure HTTPS/SSL certificate
   - Set proper file permissions

2. **Database Security**:
   - Create dedicated database user
   - Limit database privileges
   - Enable MySQL query cache

3. **Performance Optimization**:
   - Enable PHP OPcache
   - Configure browser caching
   - Optimize images and assets

## 📄 License

This project is for demonstration purposes. Please ensure compliance with local financial regulations before deployment.

## 🤝 Contributing

Feel free to contribute to this project by:
- Reporting bugs
- Suggesting features
- Improving documentation
- Submitting pull requests

---

**Built with ❤️ for Central Africa's digital economy**
