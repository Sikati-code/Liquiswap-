# LiquiSwap Database Schema

## 📊 Overview

This document describes the complete database schema for LiquiSwap, including all tables, relationships, indexes, and constraints.

## 🗄️ Database Configuration

### PostgreSQL Settings

```sql
-- Database creation
CREATE DATABASE liqui_swap 
    WITH 
    OWNER = liqui_user
    ENCODING = 'UTF8'
    LC_COLLATE = 'en_US.UTF-8'
    LC_CTYPE = 'en_US.UTF-8'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1;

-- Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_stat_statements";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Schema
SET search_path TO public;
```

## 📋 Tables

### 1. Users Table

```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    uuid UUID DEFAULT uuid_generate_v4() UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    trust_score INTEGER DEFAULT 50 CHECK (trust_score >= 0 AND trust_score <= 100),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'suspended', 'deleted')),
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'Africa/Douala',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    biometric_enabled BOOLEAN DEFAULT FALSE,
    last_login_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone_number);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_trust_score ON users(trust_score);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Full-text search
CREATE INDEX idx_users_search ON users USING gin(to_tsvector('english', name || ' ' || email));
```

### 2. User Profiles Table

```sql
CREATE TABLE user_profiles (
    user_id INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    avatar_url VARCHAR(500),
    bio TEXT,
    date_of_birth DATE,
    gender VARCHAR(10) CHECK (gender IN ('male', 'female', 'other')),
    country VARCHAR(2) DEFAULT 'CM',
    city VARCHAR(100),
    occupation VARCHAR(100),
    notification_preferences JSONB DEFAULT '{"email": true, "push": true, "sms": false}',
    privacy_settings JSONB DEFAULT '{"profile_public": true, "show_phone": false}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_user_profiles_country ON user_profiles(country);
CREATE INDEX idx_user_profiles_city ON user_profiles(city);
```

### 3. Wallets Table

```sql
CREATE TABLE wallets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(20) NOT NULL CHECK (provider IN ('MTN', 'ORANGE', 'CAMTEL', 'NEXTTEL', 'BANK', 'CASH')),
    account_number VARCHAR(50),
    account_name VARCHAR(255),
    bank_name VARCHAR(100),
    bank_code VARCHAR(20),
    balance DECIMAL(15,2) DEFAULT 0.00 CHECK (balance >= 0),
    currency VARCHAR(3) DEFAULT 'XAF',
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    last_transaction_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(user_id, provider, account_number)
);

-- Indexes
CREATE INDEX idx_wallets_user_id ON wallets(user_id);
CREATE INDEX idx_wallets_provider ON wallets(provider);
CREATE INDEX idx_wallets_balance ON wallets(balance);
CREATE INDEX idx_wallets_active ON wallets(is_active);
```

### 4. Transactions Table

```sql
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    reference VARCHAR(50) UNIQUE NOT NULL,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL CHECK (type IN ('swap', 'bundle', 'airtime', 'topup', 'withdrawal')),
    subtype VARCHAR(50),
    amount DECIMAL(15,2) NOT NULL CHECK (amount > 0),
    fee DECIMAL(15,2) DEFAULT 0.00 CHECK (fee >= 0),
    currency VARCHAR(3) DEFAULT 'XAF',
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'completed', 'failed', 'cancelled')),
    
    -- Source information
    source_provider VARCHAR(20),
    source_wallet_id INTEGER REFERENCES wallets(id),
    source_account VARCHAR(50),
    
    -- Destination information
    destination_provider VARCHAR(20),
    destination_wallet_id INTEGER REFERENCES wallets(id),
    destination_account VARCHAR(50),
    destination_name VARCHAR(255),
    
    -- Additional data
    description TEXT,
    metadata JSONB DEFAULT '{}',
    provider_reference VARCHAR(100),
    external_id VARCHAR(100),
    
    -- Timestamps
    initiated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP,
    completed_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_reference ON transactions(reference);
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_status ON transactions(status);
CREATE INDEX idx_transactions_amount ON transactions(amount);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);
CREATE INDEX idx_transactions_provider_ref ON transactions(provider_reference);

-- Composite indexes
CREATE INDEX idx_transactions_user_status ON transactions(user_id, status);
CREATE INDEX idx_transactions_type_status ON transactions(type, status);
CREATE INDEX idx_transactions_date_range ON transactions(created_at DESC) WHERE status = 'completed';

-- Full-text search
CREATE INDEX idx_transactions_search ON transactions USING gin(to_tsvector('english', description || ' ' || COALESCE(destination_name, '')));
```

### 5. Swap Transactions Table

```sql
CREATE TABLE swap_transactions (
    id SERIAL PRIMARY KEY,
    transaction_id INTEGER NOT NULL REFERENCES transactions(id) ON DELETE CASCADE,
    exchange_rate DECIMAL(10,6) DEFAULT 1.000000,
    source_amount DECIMAL(15,2) NOT NULL,
    destination_amount DECIMAL(15,2) NOT NULL,
    cashout_fee BOOLEAN DEFAULT FALSE,
    cashout_fee_amount DECIMAL(15,2) DEFAULT 0.00,
    confirmation_code VARCHAR(10),
    confirmation_expires_at TIMESTAMP,
    confirmed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_swap_transactions_tx_id ON swap_transactions(transaction_id);
CREATE INDEX idx_swap_transactions_confirmation ON swap_transactions(confirmation_code, confirmation_expires_at);
```

### 6. Bundles Table

```sql
CREATE TABLE bundles (
    id SERIAL PRIMARY KEY,
    provider VARCHAR(20) NOT NULL CHECK (provider IN ('MTN', 'ORANGE', 'CAMTEL', 'NEXTTEL')),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL CHECK (category IN ('daily', 'weekly', 'monthly', 'custom')),
    data_volume VARCHAR(50) NOT NULL,
    validity_period VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price > 0),
    currency VARCHAR(3) DEFAULT 'XAF',
    features JSONB DEFAULT '[]',
    is_active BOOLEAN DEFAULT TRUE,
    is_popular BOOLEAN DEFAULT FALSE,
    is_deal BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_bundles_provider ON bundles(provider);
CREATE INDEX idx_bundles_category ON bundles(category);
CREATE INDEX idx_bundles_price ON bundles(price);
CREATE INDEX idx_bundles_active ON bundles(is_active);
CREATE INDEX idx_bundles_popular ON bundles(is_popular);
CREATE INDEX idx_bundles_sort ON bundles(sort_order);

-- Full-text search
CREATE INDEX idx_bundles_search ON bundles USING gin(to_tsvector('english', name || ' ' || COALESCE(description, '')));
```

### 7. Bundle Purchases Table

```sql
CREATE TABLE bundle_purchases (
    id SERIAL PRIMARY KEY,
    transaction_id INTEGER NOT NULL REFERENCES transactions(id) ON DELETE CASCADE,
    bundle_id INTEGER NOT NULL REFERENCES bundles(id),
    phone_number VARCHAR(20) NOT NULL,
    payment_method VARCHAR(20) NOT NULL CHECK (payment_method IN ('MTN', 'ORANGE', 'CAMTEL', 'NEXTTEL')),
    auto_renew BOOLEAN DEFAULT FALSE,
    activation_code VARCHAR(50),
    activation_status VARCHAR(20) DEFAULT 'pending' CHECK (activation_status IN ('pending', 'activated', 'failed')),
    activated_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_bundle_purchases_tx_id ON bundle_purchases(transaction_id);
CREATE INDEX idx_bundle_purchases_bundle_id ON bundle_purchases(bundle_id);
CREATE INDEX idx_bundle_purchases_phone ON bundle_purchases(phone_number);
CREATE INDEX idx_bundle_purchases_status ON bundle_purchases(activation_status);
```

### 8. USSD Codes Table

```sql
CREATE TABLE ussd_codes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    code VARCHAR(50) NOT NULL,
    provider VARCHAR(20) NOT NULL CHECK (provider IN ('MTN', 'ORANGE', 'CAMTEL', 'NEXTTEL', 'GENERAL')),
    category VARCHAR(50) NOT NULL CHECK (category IN ('balance', 'data', 'airtime', 'services', 'support')),
    is_popular BOOLEAN DEFAULT FALSE,
    usage_count INTEGER DEFAULT 0,
    last_used_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_ussd_codes_provider ON ussd_codes(provider);
CREATE INDEX idx_ussd_codes_category ON ussd_codes(category);
CREATE INDEX idx_ussd_codes_popular ON ussd_codes(is_popular);
CREATE INDEX idx_ussd_codes_active ON ussd_codes(is_active);
CREATE INDEX idx_ussd_codes_sort ON ussd_codes(sort_order);

-- Full-text search
CREATE INDEX idx_ussd_codes_search ON ussd_codes USING gin(to_tsvector('english', name || ' ' || description));
```

### 9. Sessions Table

```sql
CREATE TABLE sessions (
    id SERIAL PRIMARY KEY,
    session_id VARCHAR(128) UNIQUE NOT NULL,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    ip_address INET NOT NULL,
    user_agent TEXT,
    device_id VARCHAR(100),
    device_type VARCHAR(20) DEFAULT 'unknown',
    is_active BOOLEAN DEFAULT TRUE,
    last_activity_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_sessions_session_id ON sessions(session_id);
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_ip ON sessions(ip_address);
CREATE INDEX idx_sessions_active ON sessions(is_active);
CREATE INDEX idx_sessions_expires ON sessions(expires_at);
```

### 10. Login Attempts Table

```sql
CREATE TABLE login_attempts (
    id SERIAL PRIMARY KEY,
    email_or_phone VARCHAR(255) NOT NULL,
    ip_address INET NOT NULL,
    user_agent TEXT,
    attempt_type VARCHAR(20) DEFAULT 'login' CHECK (attempt_type IN ('login', 'password_reset', 'biometric')),
    success BOOLEAN DEFAULT FALSE,
    failure_reason VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_login_attempts_email ON login_attempts(email_or_phone);
CREATE INDEX idx_login_attempts_ip ON login_attempts(ip_address);
CREATE INDEX idx_login_attempts_created ON login_attempts(created_at);
CREATE INDEX idx_login_attempts_success ON login_attempts(success);

-- Cleanup old attempts (older than 30 days)
CREATE OR REPLACE FUNCTION cleanup_old_login_attempts()
RETURNS void AS $$
BEGIN
    DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL '30 days';
END;
$$ LANGUAGE plpgsql;
```

### 11. Biometric Templates Table

```sql
CREATE TABLE biometric_templates (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    device_id VARCHAR(100) NOT NULL,
    device_type VARCHAR(50) NOT NULL,
    template_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(user_id, device_id)
);

-- Indexes
CREATE INDEX idx_biometric_user_id ON biometric_templates(user_id);
CREATE INDEX idx_biometric_device_id ON biometric_templates(device_id);
CREATE INDEX idx_biometric_active ON biometric_templates(is_active);
```

### 12. Two Factor Auth Table

```sql
CREATE TABLE two_factor_auth (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    secret_key VARCHAR(255) NOT NULL,
    backup_codes TEXT[],
    is_enabled BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_2fa_user_id ON two_factor_auth(user_id);
CREATE INDEX idx_2fa_enabled ON two_factor_auth(is_enabled);
```

### 13. Notification Settings Table

```sql
CREATE TABLE notification_settings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    push_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(user_id, type)
);

-- Indexes
CREATE INDEX idx_notification_user_id ON notification_settings(user_id);
CREATE INDEX idx_notification_type ON notification_settings(type);
```

### 14. Audit Log Table

```sql
CREATE TABLE audit_log (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INTEGER,
    old_values JSONB,
    new_values JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_audit_user_id ON audit_log(user_id);
CREATE INDEX idx_audit_action ON audit_log(action);
CREATE INDEX idx_audit_table ON audit_log(table_name);
CREATE INDEX idx_audit_created ON audit_log(created_at);
```

## 🔗 Relationships

### Entity Relationship Diagram

```
Users (1) -----> (N) Wallets
Users (1) -----> (N) Transactions
Users (1) -----> (N) Sessions
Users (1) -----> (N) Login Attempts
Users (1) -----> (N) Biometric Templates
Users (1) -----> (1) User Profiles
Users (1) -----> (1) Two Factor Auth
Users (1) -----> (N) Notification Settings
Users (1) -----> (N) Audit Log

Transactions (1) -----> (1) Swap Transactions
Transactions (1) -----> (1) Bundle Purchases
Transactions (N) -----> (2) Wallets (source/destination)

Bundles (1) -----> (N) Bundle Purchases
```

## 🚀 Triggers

### Updated At Trigger

```sql
-- Function to update updated_at column
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Apply to all tables with updated_at
CREATE TRIGGER update_users_updated_at 
    BEFORE UPDATE ON users 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_user_profiles_updated_at 
    BEFORE UPDATE ON user_profiles 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_wallets_updated_at 
    BEFORE UPDATE ON wallets 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_transactions_updated_at 
    BEFORE UPDATE ON transactions 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_swap_transactions_updated_at 
    BEFORE UPDATE ON swap_transactions 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_bundles_updated_at 
    BEFORE UPDATE ON bundles 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_bundle_purchases_updated_at 
    BEFORE UPDATE ON bundle_purchases 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_ussd_codes_updated_at 
    BEFORE UPDATE ON ussd_codes 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_two_factor_auth_updated_at 
    BEFORE UPDATE ON two_factor_auth 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_notification_settings_updated_at 
    BEFORE UPDATE ON notification_settings 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

### Transaction Reference Trigger

```sql
-- Generate unique transaction reference
CREATE OR REPLACE FUNCTION generate_transaction_reference()
RETURNS TRIGGER AS $$
BEGIN
    NEW.reference = 'LS-' || TO_CHAR(NOW(), 'YYYY-MM-DD') || '-' || LPAD(NEW.id::TEXT, 6, '0');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER generate_transaction_reference_trigger
    BEFORE INSERT ON transactions
    FOR EACH ROW EXECUTE FUNCTION generate_transaction_reference();
```

### Trust Score Update Trigger

```sql
-- Update trust score based on transaction history
CREATE OR REPLACE FUNCTION update_trust_score()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE users 
        SET trust_score = LEAST(100, trust_score + 1)
        WHERE id = NEW.user_id;
    ELSIF NEW.status = 'failed' AND OLD.status != 'failed' THEN
        UPDATE users 
        SET trust_score = GREATEST(0, trust_score - 2)
        WHERE id = NEW.user_id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_trust_score_trigger
    AFTER UPDATE ON transactions
    FOR EACH ROW EXECUTE FUNCTION update_trust_score();
```

## 📊 Views

### User Summary View

```sql
CREATE VIEW user_summary AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.phone_number,
    u.trust_score,
    u.status,
    u.created_at,
    u.last_login_at,
    COUNT(DISTINCT w.id) as wallet_count,
    COALESCE(SUM(w.balance), 0) as total_balance,
    COUNT(DISTINCT t.id) as transaction_count,
    COALESCE(SUM(CASE WHEN t.status = 'completed' THEN t.amount ELSE 0 END), 0) as total_volume
FROM users u
LEFT JOIN wallets w ON u.id = w.user_id AND w.is_active = TRUE
LEFT JOIN transactions t ON u.id = t.user_id AND t.status = 'completed'
GROUP BY u.id, u.name, u.email, u.phone_number, u.trust_score, u.status, u.created_at, u.last_login_at;
```

### Transaction Summary View

```sql
CREATE VIEW transaction_summary AS
SELECT 
    DATE_TRUNC('day', created_at) as transaction_date,
    type,
    status,
    COUNT(*) as transaction_count,
    SUM(amount) as total_amount,
    SUM(fee) as total_fees,
    AVG(amount) as average_amount
FROM transactions
GROUP BY DATE_TRUNC('day', created_at), type, status
ORDER BY transaction_date DESC;
```

## 🗃️ Stored Procedures

### Get User Statistics

```sql
CREATE OR REPLACE FUNCTION get_user_statistics(p_user_id INTEGER)
RETURNS JSONB AS $$
DECLARE
    result JSONB;
BEGIN
    SELECT jsonb_build_object(
        'total_transactions', COUNT(CASE WHEN status = 'completed' THEN 1 END),
        'total_volume', COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0),
        'success_rate', ROUND(
            (COUNT(CASE WHEN status = 'completed' THEN 1 END)::DECIMAL / NULLIF(COUNT(*), 0)) * 100, 2
        ),
        'wallet_count', COUNT(DISTINCT w.id),
        'total_balance', COALESCE(SUM(w.balance), 0),
        'this_month_transactions', COUNT(CASE 
            WHEN status = 'completed' AND created_at >= DATE_TRUNC('month', CURRENT_DATE) 
            THEN 1 END
        ),
        'this_month_volume', COALESCE(SUM(CASE 
            WHEN status = 'completed' AND created_at >= DATE_TRUNC('month', CURRENT_DATE) 
            THEN amount ELSE 0 END), 0)
    ) INTO result
    FROM transactions t
    LEFT JOIN wallets w ON w.user_id = p_user_id AND w.is_active = TRUE
    WHERE t.user_id = p_user_id;
    
    RETURN result;
END;
$$ LANGUAGE plpgsql;
```

### Get Popular Bundles

```sql
CREATE OR REPLACE FUNCTION get_popular_bundles(p_limit INTEGER DEFAULT 10)
RETURNS TABLE (
    id INTEGER,
    name VARCHAR(255),
    provider VARCHAR(20),
    price DECIMAL(10,2),
    purchase_count BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        b.id,
        b.name,
        b.provider,
        b.price,
        COUNT(bp.id) as purchase_count
    FROM bundles b
    LEFT JOIN bundle_purchases bp ON b.id = bp.bundle_id 
        AND bp.created_at >= CURRENT_DATE - INTERVAL '30 days'
    WHERE b.is_active = TRUE
    GROUP BY b.id, b.name, b.provider, b.price
    ORDER BY purchase_count DESC, b.sort_order ASC
    LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;
```

## 🧹 Maintenance Functions

### Cleanup Old Sessions

```sql
CREATE OR REPLACE FUNCTION cleanup_old_sessions()
RETURNS INTEGER AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    DELETE FROM sessions 
    WHERE expires_at < NOW() OR (last_activity_at < NOW() - INTERVAL '7 days' AND is_active = FALSE);
    
    GET DIAGNOSTICS deleted_count = ROW_COUNT;
    RETURN deleted_count;
END;
$$ LANGUAGE plpgsql;
```

### Archive Old Transactions

```sql
CREATE OR REPLACE FUNCTION archive_old_transactions()
RETURNS INTEGER AS $$
DECLARE
    archived_count INTEGER;
BEGIN
    -- Move transactions older than 2 years to archive table
    INSERT INTO transactions_archive 
    SELECT * FROM transactions 
    WHERE created_at < NOW() - INTERVAL '2 years';
    
    DELETE FROM transactions 
    WHERE created_at < NOW() - INTERVAL '2 years';
    
    GET DIAGNOSTICS archived_count = ROW_COUNT;
    RETURN archived_count;
END;
$$ LANGUAGE plpgsql;
```

## 📈 Performance Optimization

### Partitioning for Large Tables

```sql
-- Partition transactions by year (for large datasets)
CREATE TABLE transactions_2024 PARTITION OF transactions
    FOR VALUES FROM ('2024-01-01') TO ('2025-01-01');

CREATE TABLE transactions_2025 PARTITION OF transactions
    FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');
```

### Materialized Views

```sql
-- Materialized view for user statistics
CREATE MATERIALIZED VIEW user_stats AS
SELECT 
    user_id,
    COUNT(*) as total_transactions,
    SUM(amount) as total_amount,
    AVG(amount) as avg_amount,
    MAX(created_at) as last_transaction
FROM transactions
WHERE status = 'completed'
GROUP BY user_id;

-- Create unique index for refresh
CREATE UNIQUE INDEX idx_user_stats_user_id ON user_stats(user_id);

-- Function to refresh materialized view
CREATE OR REPLACE FUNCTION refresh_user_stats()
RETURNS void AS $$
BEGIN
    REFRESH MATERIALIZED VIEW CONCURRENTLY user_stats;
END;
$$ LANGUAGE plpgsql;
```

## 🔍 Sample Data

### Seed Data

```sql
-- Insert sample users
INSERT INTO users (name, email, phone_number, password_hash, trust_score) VALUES
('John Doe', 'john@example.com', '+237677123456', '$2y$12$...', 85),
('Jane Smith', 'jane@example.com', '+237677234567', '$2y$12$...', 92),
('Bob Johnson', 'bob@example.com', '+237677345678', '$2y$12$...', 78);

-- Insert sample bundles
INSERT INTO bundles (provider, name, description, category, data_volume, validity_period, price, is_popular) VALUES
('MTN', 'MTN 2GB', '2GB data for 7 days with unlimited WhatsApp', 'weekly', '2GB', '7 days', 2000, TRUE),
('ORANGE', 'Orange 5GB', '5GB data for 30 days', 'monthly', '5GB', '30 days', 5000, TRUE),
('CAMTEL', 'Camtel 10GB', '10GB data for 30 days', 'monthly', '10GB', '30 days', 8000, FALSE);

-- Insert USSD codes
INSERT INTO ussd_codes (name, description, code, provider, category, is_popular) VALUES
('Check Balance', 'Check main account balance', '*155#', 'ORANGE', 'balance', TRUE),
('Buy Data', 'Purchase data bundles', '*141*2#', 'MTN', 'data', TRUE),
('Transfer Money', 'Send money to another number', '*126#', 'GENERAL', 'services', TRUE);
```

---

**Schema Version**: v1.0.0  
**Last Updated**: March 20, 2024  
**Database Engine**: PostgreSQL 15+
