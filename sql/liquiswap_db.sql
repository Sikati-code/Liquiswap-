-- LiquiSwap Database Schema and Sample Data
-- MySQL 8.0+ Compatible

-- Create database
CREATE DATABASE IF NOT EXISTS liquiswap_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE liquiswap_db;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) DEFAULT (UUID()) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    trust_score DECIMAL(5,2) DEFAULT 0.00,
    total_swaps INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0.00,
    member_since TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    biometric_enabled BOOLEAN DEFAULT FALSE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone_number),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallets Table
CREATE TABLE wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL COMMENT 'MTN, ORANGE, BANK, CASH, EXPRESS_UNION',
    account_identifier VARCHAR(100) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    is_primary BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bundles Table
CREATE TABLE bundles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operator VARCHAR(50) NOT NULL COMMENT 'MTN, ORANGE, CAMTEL, NEXTTEL',
    name VARCHAR(100) NOT NULL,
    description TEXT,
    data_amount VARCHAR(50),
    voice_minutes VARCHAR(50),
    sms_count VARCHAR(50),
    validity VARCHAR(50),
    price DECIMAL(15,2) NOT NULL,
    original_price DECIMAL(15,2),
    is_hot BOOLEAN DEFAULT FALSE,
    is_good_deal BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_operator (operator),
    INDEX idx_is_hot (is_hot),
    INDEX idx_is_good_deal (is_good_deal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_uuid VARCHAR(36) DEFAULT (UUID()) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'swap, airtime, bundle, conversion',
    subtype VARCHAR(50) COMMENT 'om_to_momo, momo_to_om, airtime_to_bundle',
    amount DECIMAL(15,2) NOT NULL,
    fee DECIMAL(15,2) DEFAULT 0.00,
    receiver_identifier VARCHAR(100),
    operator VARCHAR(50),
    bundle_id INT NULL,
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, success, failed',
    reference VARCHAR(100),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bundle_id) REFERENCES bundles(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USSD Codes Table
CREATE TABLE ussd_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operator VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL COMMENT 'balance, airtime, data, services',
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_operator (operator),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Escrow Table
CREATE TABLE escrow (
    id INT AUTO_INCREMENT PRIMARY KEY,
    swap_transaction_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, buyer_paid, released, disputed',
    buyer_confirmed BOOLEAN DEFAULT FALSE,
    seller_confirmed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (swap_transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions Table
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(500) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token(255)),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Data

-- Insert sample bundles
INSERT INTO bundles (operator, name, description, data_amount, voice_minutes, sms_count, validity, price, original_price, is_hot, is_good_deal) VALUES
('MTN', 'Turbo 4G+', 'Unlimited high-speed data with nightly bonus', 'Unlimited', '0', '0', '30 Days', 10000, 12000, TRUE, FALSE),
('MTN', 'Monthly Maxi', '12GB data + Unlimited WhatsApp', '12 GB', 'Unlimited WhatsApp', '0', '30 Days', 2500, 3000, TRUE, TRUE),
('Orange', 'Home Premium', 'Best for remote workers and streaming', '50 GB', '0', '0', '90 Days', 25000, 30000, FALSE, TRUE),
('Orange', 'Weekly Data+', '4.5GB data anytime use', '4.5 GB', '0', '0', '7 Days', 1000, 1500, FALSE, TRUE),
('Camtel', 'Blue One', 'Truly unlimited data with zero throttling', 'Unlimited', '30 min', '200 SMS', '30 Days', 3000, 3500, FALSE, FALSE),
('MTN', 'Night Data', '5GB from 10pm to 6am', '5 GB', '0', '0', '24 Hours', 250, 500, TRUE, FALSE),
('Orange', 'Social Pack', 'Optimized for WhatsApp, Facebook & TikTok', '5 GB', '0', '0', '7 Days', 2500, 3000, FALSE, TRUE),
('MTN', 'Gaming Pack', 'Low latency for PUBG, FreeFire & COD', '10 GB', '0', '0', '15 Days', 4000, 5000, TRUE, FALSE);

-- Insert sample USSD codes
INSERT INTO ussd_codes (operator, category, name, code, description) VALUES
('MTN', 'balance', 'Check Main Balance', '*556#', 'Check your MTN mobile money and airtime balance'),
('Orange', 'balance', 'Check Main Balance', '#155#', 'Check your Orange money and airtime balance'),
('MTN', 'data', 'Buy Data Bundles', '*141*2#', 'Purchase daily, weekly or monthly data bundles'),
('Orange', 'data', 'Buy Data Bundles', '#144#', 'Purchase data bundles from Orange'),
('MTN', 'airtime', 'Transfer Airtime', '*126#', 'Send airtime to any MTN number'),
('Orange', 'airtime', 'Transfer Airtime', '#144#', 'Send airtime to any Orange number'),
('MTN', 'services', 'Customer Support', '155', 'Contact MTN customer service'),
('Orange', 'services', 'Customer Support', '955', 'Contact Orange customer service'),
('Camtel', 'balance', 'Check Balance', '*100#', 'Check your Camtel data balance'),
('Camtel', 'data', 'Buy Data', '*101#', 'Purchase Camtel data bundles');

-- Insert sample user (password: password123)
INSERT INTO users (full_name, phone_number, password_hash, trust_score, total_swaps, success_rate) VALUES
('Jean Paul', '+237699999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 98.5, 127, 99.2);

-- Insert sample wallets for the demo user
INSERT INTO wallets (user_id, provider, account_identifier, balance, is_primary, is_verified) VALUES
(1, 'MTN', '+237699999999', 450000, TRUE, TRUE),
(1, 'ORANGE', '+237699999998', 300000, FALSE, TRUE),
(1, 'BANK', '1234567890', 500000, FALSE, TRUE),
(1, 'CASH', 'Cash', 0, FALSE, TRUE);

-- Insert sample transactions
INSERT INTO transactions (user_id, type, subtype, amount, fee, receiver_identifier, operator, status, reference) VALUES
(1, 'swap', 'om_to_momo', 15000, 150, '+237699999998', 'ORANGE', 'success', 'LSW20240322001'),
(1, 'airtime', 'airtime_purchase', 2000, 0, '+237699999999', 'MTN', 'success', 'LSW20240322002'),
(1, 'bundle', 'bundle_purchase', 2500, 0, '+237699999999', 'MTN', 'success', 'LSW20240322003');

-- Insert sample escrow records
INSERT INTO escrow (swap_transaction_id, buyer_id, seller_id, amount, status) VALUES
(1, 1, 1, 15000, 'released');
