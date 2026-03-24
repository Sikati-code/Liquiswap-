# LiquiSwap Installation Guide

## 🚀 Quick Start

This guide will help you set up LiquiSwap on your server. The application is designed for production deployment with enterprise-grade security.

## 📋 System Requirements

### Minimum Requirements
- **PHP**: 8.0 or higher
- **PostgreSQL**: 14.0 or higher  
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 2GB RAM minimum
- **Storage**: 20GB available space
- **SSL**: Valid SSL certificate (HTTPS required)

### Recommended Requirements
- **PHP**: 8.2+
- **PostgreSQL**: 15+
- **Memory**: 4GB+ RAM
- **Storage**: 50GB+ SSD
- **Redis**: For session caching (optional)

## 🛠 Installation Steps

### Step 1: Download Files

```bash
# Clone the repository
git clone https://github.com/your-org/liquiswap.git
cd liquiswap

# Or download and extract the ZIP file
unzip liquiswap.zip
cd liquiswap
```

### Step 2: Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit with your preferred editor
nano .env
```

**Required Environment Variables:**
```env
# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=liquiswap
DB_USER=your_db_user
DB_PASSWORD=your_secure_password

# Application
APP_URL=https://your-domain.com
APP_NAME=LiquiSwap
APP_ENV=production

# Security (Generate unique keys)
JWT_SECRET=your-256-bit-jwt-secret-key-here
ENCRYPTION_KEY=your-256-bit-encryption-key-here

# Regional Settings
CURRENCY_CODE=XAF
PHONE_PREFIX=+237
```

### Step 3: Database Setup

```bash
# Connect to PostgreSQL
sudo -u postgres psql

# Create database and user
CREATE DATABASE liqui_swap;
CREATE USER liqui_user WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE liqui_swap TO liqui_user;
\q

# Import the schema
psql -h localhost -U liqui_user -d liqui_swap -f database.sql
```

### Step 4: Web Server Configuration

#### Apache Configuration

Create `/etc/apache2/sites-available/liquiswap.conf`:
```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/liquiswap
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/your-domain.crt
    SSLCertificateKeyFile /etc/ssl/private/your-domain.key
    
    # Security Headers
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options nosniff
    
    # Enable .htaccess
    <Directory /var/www/liquiswap>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Error pages
    ErrorDocument 404 /404.php
    ErrorDocument 500 /500.php
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite liquiswap
sudo a2enmod rewrite headers ssl
sudo systemctl reload apache2
```

#### Nginx Configuration

Create `/etc/nginx/sites-available/liquiswap`:
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/liquiswap;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    
    # Security Headers
    add_header X-Frame-Options DENY always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options nosniff always;
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    # Pretty URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Protect sensitive files
    location ~ /\.(env|git) {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/liquiswap /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 5: File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/liquiswap

# Set permissions
sudo find /var/www/liquiswap -type f -exec chmod 644 {} \;
sudo find /var/www/liquiswap -type d -exec chmod 755 {} \;

# Secure sensitive files
sudo chmod 600 /var/www/liquiswap/.env
sudo chmod 644 /var/www/liquiswap/.htaccess
```

### Step 6: PHP Configuration

Edit `/etc/php/8.2/fpm/php.ini`:
```ini
# Security settings
expose_php = Off
display_errors = Off
log_errors = On

# Performance settings
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M

# Session settings
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

## 🔧 Post-Installation Configuration

### Generate Security Keys

```bash
# Generate JWT secret (256 bits)
openssl rand -base64 32

# Generate encryption key (256 bits)  
openssl rand -hex 32
```

Update your `.env` file with these values.

### Test the Installation

1. **Check PHP Requirements**: Create `test.php` with `<?php phpinfo();`
2. **Test Database**: Run `psql -h localhost -U liqui_user -d liqui_swap -c "SELECT version();"`
3. **Test SSL**: Visit `https://your-domain.com` and check the certificate
4. **Test Application**: Visit the main page and try creating an account

### Configure Cron Jobs

```bash
# Edit crontab
sudo crontab -e

# Add cleanup job
0 2 * * * /usr/bin/php /var/www/liquiswap/scripts/cleanup.php

# Add backup job
0 3 * * * /usr/bin/pg_dump -h localhost -U liqui_user liqui_swap > /backups/liquiswap_$(date +\%Y\%m\%d).sql
```

## 🔒 Security Hardening

### Firewall Configuration

```bash
# Allow only necessary ports
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP (redirect to HTTPS)
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### Database Security

```sql
-- Create read-only user for reporting
CREATE USER liqui_readonly WITH PASSWORD 'readonly_password';
GRANT SELECT ON ALL TABLES IN SCHEMA public TO liqui_readonly;

-- Revoke unnecessary permissions
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO liqui_user;
```

### Application Security

1. **Disable PHP Info**: Remove `test.php`
2. **Hide Server Headers**: Configure web server
3. **Rate Limiting**: Configure fail2ban
4. **Monitoring**: Set up log monitoring
5. **Backups**: Automated daily backups

## 📊 Monitoring & Maintenance

### Log Files to Monitor

```bash
# Application logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# PHP logs
tail -f /var/log/php8.2-fpm.log

# Database logs
tail -f /var/log/postgresql/postgresql-15-main.log
```

### Performance Monitoring

Install monitoring tools:
```bash
# Install monitoring
sudo apt install htop iotop

# Monitor database connections
sudo -u postgres psql -c "SELECT * FROM pg_stat_activity;"

# Check disk space
df -h

# Monitor memory usage
free -h
```

## 🚨 Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error
```bash
# Check error logs
tail -f /var/log/apache2/error.log

# Common causes:
# - File permissions
# - PHP syntax errors
# - Database connection issues
```

#### 2. Database Connection Failed
```bash
# Test connection
psql -h localhost -U liqui_user -d liqui_swap

# Check PostgreSQL status
sudo systemctl status postgresql

# Verify credentials in .env
```

#### 3. SSL Certificate Issues
```bash
# Test SSL certificate
openssl s_client -connect your-domain.com:443

# Check certificate expiry
openssl x509 -in /etc/ssl/certs/your-domain.crt -noout -dates
```

#### 4. Permission Denied Errors
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/liquiswap
sudo find /var/www/liquiswap -type f -exec chmod 644 {} \;
sudo find /var/www/liquiswap -type d -exec chmod 755 {} \;
```

### Performance Issues

#### Slow Database Queries
```sql
-- Enable slow query logging
ALTER SYSTEM SET log_min_duration_statement = 1000;
SELECT pg_reload_conf();

-- Analyze slow queries
SELECT query, mean_time, calls 
FROM pg_stat_statements 
ORDER BY mean_time DESC 
LIMIT 10;
```

#### High Memory Usage
```bash
# Check PHP memory usage
ps aux | grep php-fpm

# Optimize PHP-FPM settings
# Edit /etc/php/8.2/fpm/pool.d/www.conf
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
```

## 📞 Support

If you encounter issues during installation:

1. **Check Logs**: Always check error logs first
2. **Verify Requirements**: Ensure all system requirements are met
3. **Test Components**: Test database, PHP, and web server separately
4. **Documentation**: Refer to the main README.md
5. **Community**: Check GitHub Issues for common problems
6. **Support**: Contact support@liquiswap.cm for enterprise support

## 🔄 Updates & Maintenance

### Updating the Application

```bash
# Backup current version
sudo cp -r /var/www/liquiswap /var/www/liquiswap.backup.$(date +%Y%m%d)

# Update files
git pull origin main

# Update database if needed
psql -h localhost -U liqui_user -d liqui_swap -f updates/update_v1.1.sql

# Clear caches
sudo systemctl reload php8.2-fpm
sudo systemctl reload apache2
```

### Regular Maintenance Tasks

```bash
# Weekly: Optimize database
sudo -u postgres psql -d liqui_swap -c "VACUUM ANALYZE;"

# Monthly: Update system packages
sudo apt update && sudo apt upgrade

# Quarterly: Review security logs
sudo grep "Failed password" /var/log/auth.log | wc -l
```

---

**Installation Complete! 🎉**

Your LiquiSwap instance should now be running at `https://your-domain.com`. 

Next steps:
1. Create your first user account
2. Configure payment providers
3. Set up monitoring
4. Train your team

For additional support, visit our [documentation portal](https://docs.liquiswap.cm).
