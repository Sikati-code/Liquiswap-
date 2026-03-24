-- LiquiSwap Database Schema for PostgreSQL 14+
-- Run this script to create all tables, indexes, and seed data

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Drop existing tables (caution: this will delete all data!)
DROP TABLE IF EXISTS escrow CASCADE;
DROP TABLE IF EXISTS login_attempts CASCADE;
DROP TABLE IF EXISTS sessions CASCADE;
DROP TABLE IF EXISTS transactions CASCADE;
DROP TABLE IF EXISTS wallets CASCADE;
DROP TABLE IF EXISTS bundles CASCADE;
DROP TABLE IF EXISTS ussd_codes CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- 1. Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    uuid UUID DEFAULT gen_random_uuid() UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'agent')),
    trust_score DECIMAL(5,2) DEFAULT 50.00 CHECK (trust_score >= 0 AND trust_score <= 100),
    total_swaps INTEGER DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0.00,
    member_since TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    biometric_enabled BOOLEAN DEFAULT FALSE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Wallets Table
CREATE TABLE wallets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(50) NOT NULL CHECK (provider IN ('MTN', 'ORANGE', 'BANK', 'CASH')),
    account_identifier VARCHAR(100) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00 CHECK (balance >= 0),
    is_primary BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, provider, account_identifier)
);

-- 3. Bundles Table
CREATE TABLE bundles (
    id SERIAL PRIMARY KEY,
    operator VARCHAR(50) NOT NULL CHECK (operator IN ('MTN', 'ORANGE', 'CAMTEL', 'NEXTTEL')),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    data_amount VARCHAR(50),
    voice_minutes VARCHAR(50),
    sms_count VARCHAR(50),
    validity VARCHAR(50),
    price DECIMAL(15,2) NOT NULL CHECK (price > 0),
    original_price DECIMAL(15,2),
    is_hot BOOLEAN DEFAULT FALSE,
    is_good_deal BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. USSD Codes Table
CREATE TABLE ussd_codes (
    id SERIAL PRIMARY KEY,
    operator VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL CHECK (category IN ('balance', 'airtime', 'data', 'services', 'support', 'banking')),
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

-- 5. Transactions Table
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    transaction_uuid UUID DEFAULT gen_random_uuid() UNIQUE NOT NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL CHECK (type IN ('swap', 'airtime', 'bundle', 'conversion', 'deposit', 'withdrawal')),
    subtype VARCHAR(50),
    amount DECIMAL(15,2) NOT NULL CHECK (amount > 0),
    fee DECIMAL(15,2) DEFAULT 0.00,
    receiver_identifier VARCHAR(100),
    operator VARCHAR(50),
    bundle_id INTEGER REFERENCES bundles(id),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'success', 'failed', 'cancelled')),
    reference VARCHAR(100) UNIQUE,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Sessions Table
CREATE TABLE sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(500) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Login Attempts Table (for rate limiting)
CREATE TABLE login_attempts (
    id SERIAL PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45),
    successful BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Escrow Table (for swap protection)
CREATE TABLE escrow (
    id SERIAL PRIMARY KEY,
    swap_transaction_id INTEGER REFERENCES transactions(id),
    buyer_id INTEGER REFERENCES users(id),
    seller_id INTEGER REFERENCES users(id),
    amount DECIMAL(15,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'buyer_paid', 'released', 'disputed', 'refunded')),
    buyer_confirmed BOOLEAN DEFAULT FALSE,
    seller_confirmed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Indexes for performance
CREATE INDEX idx_users_phone ON users(phone_number);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_uuid ON users(uuid);
CREATE INDEX idx_wallets_user ON wallets(user_id);
CREATE INDEX idx_transactions_user ON transactions(user_id);
CREATE INDEX idx_transactions_uuid ON transactions(transaction_uuid);
CREATE INDEX idx_transactions_status ON transactions(status);
CREATE INDEX idx_transactions_created ON transactions(created_at DESC);
CREATE INDEX idx_sessions_token ON sessions(token);
CREATE INDEX idx_sessions_expires ON sessions(expires_at);
CREATE INDEX idx_sessions_user ON sessions(user_id);
CREATE INDEX idx_login_attempts_phone ON login_attempts(phone_number);
CREATE INDEX idx_login_attempts_time ON login_attempts(attempted_at);
CREATE INDEX idx_bundles_operator ON bundles(operator);
CREATE INDEX idx_bundles_active ON bundles(is_active);
CREATE INDEX idx_ussd_codes_operator ON ussd_codes(operator);
CREATE INDEX idx_ussd_codes_category ON ussd_codes(category);

-- Create function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_wallets_updated_at BEFORE UPDATE ON wallets
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_transactions_updated_at BEFORE UPDATE ON transactions
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_escrow_updated_at BEFORE UPDATE ON escrow
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- SEED DATA
-- ============================================

-- Sample User (password: Password123!)
INSERT INTO users (full_name, email, phone_number, password_hash, role, trust_score, total_swaps, success_rate) VALUES
('Jean Paul', 'jean@example.com', '+237612345678', '$2y$12$LQvP6NxMD9L7X6QyQJ9Xhe.Q8J9QJ9QJ9QJ9QJ9QJ9QJ9QJ9QJ9QJ9Qi', 'user', 98.00, 150, 100.00);

-- Sample Wallets for demo user
INSERT INTO wallets (user_id, provider, account_identifier, balance, is_primary, is_verified) VALUES
(1, 'MTN', '677123456', 450000.00, TRUE, TRUE),
(1, 'ORANGE', '699987654', 300000.00, FALSE, TRUE),
(1, 'BANK', 'UBA-1234567890', 500000.00, FALSE, TRUE),
(1, 'CASH', 'CASH-001', 0.00, FALSE, TRUE);

-- MTN Bundles
INSERT INTO bundles (operator, name, description, data_amount, voice_minutes, validity, price, original_price, is_hot, is_good_deal) VALUES
('MTN', 'Monthly Maxi', '30 days high-speed data with unlimited WhatsApp', '12 GB', NULL, '30 Days', 2500.00, 3000.00, FALSE, TRUE),
('MTN', 'Turbo 20GB', '20GB per month with nightly bonus data', '20 GB', NULL, '30 Days', 5000.00, 6500.00, TRUE, FALSE),
('MTN', '5GB Combo', '5GB data + 100 minutes calls', '5 GB', '100', '30 Days', 3000.00, 3500.00, FALSE, TRUE),
('MTN', 'Gaming Pack', 'Low latency pack optimized for gaming', '10 GB', NULL, '15 Days', 4000.00, 5000.00, FALSE, TRUE),
('MTN', 'Weekly Social', 'Optimized for WhatsApp, Facebook & TikTok', '5 GB', NULL, '7 Days', 2500.00, 3000.00, FALSE, FALSE);

-- Orange Bundles
INSERT INTO bundles (operator, name, description, data_amount, voice_minutes, validity, price, original_price, is_hot, is_good_deal) VALUES
('ORANGE', 'Home Premium', 'Best for remote workers and streaming', '50 GB', NULL, '90 Days', 25000.00, 30000.00, FALSE, TRUE),
('ORANGE', 'Max Social Packs', 'Unlimited social media access', NULL, NULL, '30 Days', 2500.00, 3000.00, TRUE, FALSE),
('ORANGE', 'Weeky Data+', '4.5GB anytime use data', '4.5 GB', NULL, '7 Days', 1000.00, 1500.00, FALSE, TRUE),
('ORANGE', 'Flash Daily', 'Unlimited data for 24 hours', 'Unlimited', NULL, '24 Hours', 500.00, 1000.00, TRUE, TRUE);

-- Camtel Bundles
INSERT INTO bundles (operator, name, description, data_amount, validity, price, is_hot) VALUES
('CAMTEL', 'Blue One Daily', 'Truly unlimited data with zero speed throttling', 'Unlimited', '24 Hours', 500.00, TRUE),
('CAMTEL', 'Blue Weekly', 'Unlimited data for one week', 'Unlimited', '7 Days', 2500.00, FALSE);

-- Nexttel Bundles
INSERT INTO bundles (operator, name, description, data_amount, voice_minutes, validity, price) VALUES
('NEXTTEL', 'G-Special Social', 'Optimized for social media apps', '5 GB', NULL, '7 Days', 2500.00),
('NEXTTEL', 'Monthly Standard', 'Standard monthly data plan', '3 GB', NULL, '30 Days', 2000.00);

-- USSD Codes - Balance
INSERT INTO ussd_codes (operator, category, name, code, description) VALUES
('ALL', 'balance', 'Check Main Balance', '*155#', 'Check your main account balance (Orange & MTN)'),
('MTN', 'balance', 'MTN Balance', '*155#', 'Check MTN MoMo and airtime balance'),
('ORANGE', 'balance', 'Orange Balance', '*155#', 'Check Orange Money and airtime balance'),
('CAMTEL', 'balance', 'Camtel Balance', '*825#', 'Check Camtel Blue account balance');

-- USSD Codes - Data
INSERT INTO ussd_codes (operator, category, name, code, description) VALUES
('MTN', 'data', 'Buy Data Bundles', '*141*2#', 'Purchase daily, weekly, and monthly data plans'),
('MTN', 'data', 'Night Data Plan', '*150*47#', 'Low-cost midnight surfing bundles'),
('ORANGE', 'data', 'Orange Data Plans', '*144#', 'Access Orange data bundle menu'),
('CAMTEL', 'data', 'Blue Data Options', '*825*1#', 'Purchase Camtel data bundles');

-- USSD Codes - Airtime
INSERT INTO ussd_codes (operator, category, name, code, description) VALUES
('ALL', 'airtime', 'Recharge Card', '*135*PIN#', 'Direct scratch card top-up portal'),
('MTN', 'airtime', 'MTN Recharge', '*155*PIN#', 'Recharge MTN airtime with scratch card'),
('ORANGE', 'airtime', 'Orange Recharge', '*144*PIN#', 'Recharge Orange airtime with scratch card');

-- USSD Codes - Services
INSERT INTO ussd_codes (operator, category, name, code, description) VALUES
('MTN', 'services', 'MoMo Transfer', '*126#', 'Send money to any mobile number via MTN MoMo'),
('MTN', 'services', 'MoMo Menu', '*126#', 'Access MTN Mobile Money services'),
('ORANGE', 'services', 'Orange Money', '*150#', 'Access Orange Money services'),
('ORANGE', 'services', 'Send Money', '*150*1#', 'Send money via Orange Money');

-- USSD Codes - Support
INSERT INTO ussd_codes (operator, category, name, code, description) VALUES
('MTN', 'support', 'Customer Service', '950', 'Speak with MTN customer care agent'),
('ORANGE', 'support', 'Customer Service', '955', 'Speak with Orange customer care agent'),
('CAMTEL', 'support', 'Camtel Support', '821', 'Contact Camtel customer support'),
('NEXTTEL', 'support', 'Nexttel Support', '811', 'Contact Nexttel customer support');

-- USSD Codes - Banking
INSERT INTO ussd_codes (operator, category, name, code, description) VALUES
('UBA', 'banking', 'UBA Mobile Banking', '*919#', 'UBA mobile banking services'),
('ECOBANK', 'banking', 'Ecobank Xpress', '*326#', 'Ecobank mobile money and banking'),
('MTN', 'banking', 'Bank to MoMo', '*126*1#', 'Transfer from bank to MTN MoMo'),
('ORANGE', 'banking', 'Bank to OM', '*150*2#', 'Transfer from bank to Orange Money');

-- Sample Transactions
INSERT INTO transactions (user_id, type, subtype, amount, fee, receiver_identifier, operator, status, reference, created_at) VALUES
(1, 'swap', 'om_to_momo', 15000.00, 150.00, '677123456', 'MTN', 'success', 'LS-20250120-A1B2C3', NOW() - INTERVAL '2 hours'),
(1, 'airtime', 'mtn_refill', 2000.00, 0.00, '677123456', 'MTN', 'success', 'LS-20250119-D4E5F6', NOW() - INTERVAL '1 day'),
(1, 'bundle', 'mtn_data', 2500.00, 0.00, '677123456', 'MTN', 'success', 'LS-20250118-G7H8I9', NOW() - INTERVAL '2 days'),
(1, 'withdrawal', 'bank_transfer', 150000.00, 3000.00, 'UBA-1234567890', 'BANK', 'success', 'LS-20250110-J0K1L2', NOW() - INTERVAL '10 days'),
(1, 'swap', 'momo_to_om', 25500.00, 255.00, '699987654', 'ORANGE', 'success', 'LS-20250120-M3N4O5', NOW() - INTERVAL '4 hours');

-- Verify seed data
SELECT 'Users count: ' || COUNT(*) FROM users;
SELECT 'Wallets count: ' || COUNT(*) FROM wallets;
SELECT 'Bundles count: ' || COUNT(*) FROM bundles;
SELECT 'USSD codes count: ' || COUNT(*) FROM ussd_codes;
SELECT 'Transactions count: ' || COUNT(*) FROM transactions;
