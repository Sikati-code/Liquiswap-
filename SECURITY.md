# LiquiSwap Security Documentation

## 🔐 Security Overview

LiquiSwap implements enterprise-grade security measures to protect user data and financial transactions. This document outlines our security architecture, best practices, and compliance measures.

## 🛡️ Security Architecture

### Multi-Layer Security Model

1. **Network Layer**: HTTPS/TLS 1.3, firewall rules, DDoS protection
2. **Application Layer**: Input validation, CSRF protection, rate limiting
3. **Authentication Layer**: JWT tokens, biometric auth, 2FA
4. **Data Layer**: Encrypted storage, secure backups, access controls
5. **Monitoring Layer**: Real-time threat detection, audit logging

### Security Headers

All HTTP responses include security headers:

```http
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
X-Content-Type-Options: nosniff
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

## 🔑 Authentication & Authorization

### JWT Token Security

```php
// JWT Configuration
$jwtConfig = [
    'algorithm' => 'HS256',
    'secret' => $_ENV['JWT_SECRET'], // 256-bit key
    'expiry' => 7200, // 2 hours
    'refresh_expiry' => 604800, // 7 days
    'issuer' => 'liquiswap.cm',
    'audience' => 'liquiswap-users'
];
```

### Token Structure

```json
{
  "header": {
    "alg": "HS256",
    "typ": "JWT"
  },
  "payload": {
    "sub": "user_id",
    "iat": 1647792000,
    "exp": 1647799200,
    "iss": "liquiswap.cm",
    "aud": "liquiswap-users",
    "jti": "unique_token_id",
    "role": "user",
    "permissions": ["swap", "bundles", "profile"]
  }
}
```

### Session Management

```php
class Session {
    private function generateSessionId(): string {
        return bin2hex(random_bytes(32));
    }
    
    private function validateSession(string $sessionId): bool {
        $session = $this->getSession($sessionId);
        
        // Check session exists and not expired
        if (!$session || $session['expires_at'] < time()) {
            return false;
        }
        
        // Verify IP address hasn't changed
        if ($session['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            $this->logSecurityEvent('session_ip_mismatch', $sessionId);
            return false;
        }
        
        // Verify user agent hasn't changed
        if ($session['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logSecurityEvent('session_ua_mismatch', $sessionId);
            return false;
        }
        
        return true;
    }
}
```

## 🛡️ CSRF Protection

### Token Generation

```php
class CSRF {
    public function generateToken(): string {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    public function validateToken(string $token): bool {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Check token matches
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        
        // Check token age (max 1 hour)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            return false;
        }
        
        return true;
    }
}
```

### Implementation

```html
<!-- Form with CSRF token -->
<form method="POST" action="/api/swap/create">
    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
    <!-- Other form fields -->
</form>
```

```javascript
// AJAX with CSRF token
fetch('/api/swap/create', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(data)
});
```

## 🔒 Input Validation & Sanitization

### Validation Rules

```php
class Validator {
    public function validatePhoneNumber(string $phone): bool {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check Cameroon phone format
        return preg_match('/^2376[0-9]{8}$/', $phone);
    }
    
    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public function validateAmount(float $amount): bool {
        return $amount >= 100 && $amount <= 5000000;
    }
    
    public function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
```

### SQL Injection Prevention

```php
// Always use prepared statements
public function getUserById(int $userId): ?array {
    $sql = "SELECT * FROM users WHERE id = :id AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $userId]);
    
    return $stmt->fetch() ?: null;
}

// Never do this (vulnerable):
// $sql = "SELECT * FROM users WHERE id = " . $_GET['id'];
```

## 🔐 Password Security

### Password Hashing

```php
class Password {
    private const COST = 12; // High cost for security
    
    public function hash(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => self::COST]);
    }
    
    public function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    public function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => self::COST]);
    }
}
```

### Password Policy

```php
class PasswordPolicy {
    public function validate(string $password): array {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }
}
```

## 🚨 Rate Limiting

### Implementation

```php
class RateLimiter {
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes
    
    public function checkRateLimit(string $identifier, int $maxAttempts = self::MAX_ATTEMPTS): bool {
        $key = "rate_limit:" . md5($identifier);
        $attempts = $this->getCache($key) ?: 0;
        
        if ($attempts >= $maxAttempts) {
            return false;
        }
        
        // Increment counter
        $this->setCache($key, $attempts + 1, self::LOCKOUT_TIME);
        
        return true;
    }
    
    public function isLocked(string $identifier): bool {
        $key = "rate_limit:" . md5($identifier);
        $attempts = $this->getCache($key) ?: 0;
        
        return $attempts >= self::MAX_ATTEMPTS;
    }
}
```

### Usage

```php
// Login rate limiting
if (!$rateLimiter->checkRateLimit($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many login attempts. Try again later.']);
    exit;
}
```

## 🔍 Security Logging

### Event Types

```php
class SecurityLogger {
    public const EVENTS = [
        'login_success',
        'login_failure',
        'password_reset',
        'account_locked',
        'suspicious_activity',
        'data_access',
        'privilege_escalation',
        'configuration_change'
    ];
    
    public function logEvent(string $event, array $data = []): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'user_id' => $_SESSION['user_id'] ?? null,
            'data' => $data
        ];
        
        // Log to file
        error_log(json_encode($logEntry), 3, '/var/log/liquiswap/security.log');
        
        // Log to database for analysis
        $this->storeSecurityLog($logEntry);
    }
}
```

### Monitoring Dashboard

```php
class SecurityMonitor {
    public function getThreatLevel(): string {
        $recentFailures = $this->getRecentLoginFailures();
        $suspiciousIPs = $this->getSuspiciousIPs();
        $lockedAccounts = $this->getLockedAccounts();
        
        if ($recentFailures > 100 || $suspiciousIPs > 50) {
            return 'HIGH';
        } elseif ($recentFailures > 50 || $suspiciousIPs > 20) {
            return 'MEDIUM';
        }
        
        return 'LOW';
    }
    
    public function getSecurityMetrics(): array {
        return [
            'login_failures_24h' => $this->getRecentLoginFailures(24),
            'suspicious_ips_24h' => $this->getSuspiciousIPs(24),
            'blocked_requests_24h' => $this->getBlockedRequests(24),
            'active_sessions' => $this->getActiveSessions(),
            'threat_level' => $this->getThreatLevel()
        ];
    }
}
```

## 🛡️ Data Protection

### Encryption at Rest

```php
class Encryption {
    private const METHOD = 'AES-256-GCM';
    private const KEY_LENGTH = 32;
    
    public function encrypt(string $data): string {
        $key = hash('sha256', $_ENV['ENCRYPTION_KEY'], true);
        $iv = random_bytes(openssl_cipher_iv_length(self::METHOD));
        $tag = '';
        
        $encrypted = openssl_encrypt($data, self::METHOD, $key, 0, $iv, $tag);
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    public function decrypt(string $encryptedData): string {
        $key = hash('sha256', $_ENV['ENCRYPTION_KEY'], true);
        $data = base64_decode($encryptedData);
        
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, 16);
        $encrypted = substr($data, $ivLength + 16);
        
        return openssl_decrypt($encrypted, self::METHOD, $key, 0, $iv, $tag);
    }
}
```

### Sensitive Data Handling

```php
class SensitiveDataHandler {
    public function storeBankAccount(array $accountData): void {
        // Encrypt sensitive fields
        $encrypted = [
            'account_number' => $this->encryption->encrypt($accountData['account_number']),
            'routing_number' => $this->encryption->encrypt($accountData['routing_number']),
            'bank_name' => $accountData['bank_name'], // Non-sensitive
            'user_id' => $accountData['user_id']
        ];
        
        $this->db->insert('bank_accounts', $encrypted);
    }
    
    public function maskAccountNumber(string $accountNumber): string {
        return substr($accountNumber, 0, 4) . '****' . substr($accountNumber, -4);
    }
}
```

## 🔒 Biometric Security

### Biometric Data Handling

```php
class BiometricAuth {
    public function storeBiometricTemplate(int $userId, string $biometricData): void {
        // Hash biometric data (never store raw data)
        $template = hash('sha256', $biometricData . $_ENV['BIOMETRIC_SALT']);
        
        // Store with device identifier
        $this->db->insert('biometric_templates', [
            'user_id' => $userId,
            'template_hash' => $template,
            'device_id' => $_POST['device_id'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function verifyBiometric(string $biometricData, string $deviceId): bool {
        $template = hash('sha256', $biometricData . $_ENV['BIOMETRIC_SALT']);
        
        $stored = $this->db->fetchOne(
            'SELECT template_hash FROM biometric_templates WHERE device_id = ? AND user_id = ?',
            [$deviceId, $_SESSION['user_id']]
        );
        
        return $stored && hash_equals($stored['template_hash'], $template);
    }
}
```

## 🚨 Incident Response

### Security Incident Procedure

1. **Detection**
   - Automated monitoring alerts
   - Manual security review
   - User reports

2. **Assessment**
   - Determine scope and impact
   - Classify severity level
   - Activate response team

3. **Containment**
   - Isolate affected systems
   - Block malicious IPs
   - Suspend compromised accounts

4. **Eradication**
   - Remove malware/backdoors
   - Patch vulnerabilities
   - Update security measures

5. **Recovery**
   - Restore from clean backups
   - Verify system integrity
   - Monitor for recurrence

6. **Post-Incident**
   - Document lessons learned
   - Update security policies
   - Improve detection capabilities

### Emergency Contacts

```php
class SecurityIncident {
    private const EMERGENCY_CONTACTS = [
        'security_team' => 'security@liquiswap.cm',
        'cto' => 'cto@liquiswap.cm',
        'legal' => 'legal@liquiswap.cm',
        'pr' => 'pr@liquiswap.cm'
    ];
    
    public function reportIncident(array $incident): void {
        $severity = $incident['severity'];
        $contacts = $this->getContactsForSeverity($severity);
        
        foreach ($contacts as $contact) {
            $this->sendAlert($contact, $incident);
        }
    }
}
```

## 📊 Compliance

### GDPR Compliance

```php
class GDPRCompliance {
    public function exportUserData(int $userId): array {
        return [
            'personal_data' => $this->getUserPersonalData($userId),
            'transaction_history' => $this->getUserTransactions($userId),
            'preferences' => $this->getUserPreferences($userId),
            'login_history' => $this->getUserLoginHistory($userId),
            'export_date' => date('Y-m-d H:i:s')
        ];
    }
    
    public function deleteUserData(int $userId): bool {
        // Anonymize rather than delete for audit purposes
        $this->anonymizeUserData($userId);
        
        // Delete sensitive data
        $this->deleteBiometricData($userId);
        $this->deleteSessions($userId);
        
        return true;
    }
}
```

### Financial Regulations

```php
class ComplianceChecker {
    public function checkTransactionLimits(float $amount, string $userId): bool {
        $dailyLimit = $this->getUserDailyLimit($userId);
        $monthlyLimit = $this->getUserMonthlyLimit($userId);
        
        $dailyTotal = $this->getUserDailyTotal($userId);
        $monthlyTotal = $this->getUserMonthlyTotal($userId);
        
        return ($dailyTotal + $amount) <= $dailyLimit && 
               ($monthlyTotal + $amount) <= $monthlyLimit;
    }
    
    public function reportSuspiciousTransaction(array $transaction): void {
        // Check for suspicious patterns
        if ($this->isSuspicious($transaction)) {
            $this->fileSAR($transaction); // Suspicious Activity Report
        }
    }
}
```

## 🧪 Security Testing

### Automated Security Scans

```bash
# OWASP ZAP Security Scan
docker run -t owasp/zap2docker-stable zap-baseline.py \
    -t https://api.liquiswap.cm \
    -J security-report.json

# SQL Injection Testing
sqlmap -u "https://api.liquiswap.cm/users?id=1" \
    --level=5 --risk=3 --batch

# XSS Testing
xsser -u "https://liquiswap.cm/search?q=test" \
    --auto --cookie="session=test"
```

### Penetration Testing Checklist

- [ ] Authentication bypass attempts
- [ ] SQL injection testing
- [ ] XSS vulnerability scanning
- [ ] CSRF token validation
- [ ] Session hijacking attempts
- [ ] Rate limiting effectiveness
- [ ] File upload security
- [ ] API endpoint security
- [ ] Database security
- [ ] Server configuration

## 📈 Security Metrics

### Key Performance Indicators

- **Mean Time to Detect (MTTD)**: < 5 minutes
- **Mean Time to Respond (MTTR)**: < 30 minutes
- **False Positive Rate**: < 2%
- **Security Incident Frequency**: < 1 per month
- **Vulnerability Remediation Time**: < 24 hours

### Monitoring Dashboard

```php
class SecurityDashboard {
    public function getMetrics(): array {
        return [
            'current_threat_level' => $this->getThreatLevel(),
            'active_incidents' => $this->getActiveIncidents(),
            'blocked_attempts_today' => $this->getBlockedAttempts(),
            'vulnerabilities_found' => $this->getVulnerabilityCount(),
            'compliance_score' => $this->getComplianceScore(),
            'security_score' => $this->calculateSecurityScore()
        ];
    }
}
```

## 🔄 Security Updates

### Patch Management

```php
class SecurityUpdater {
    public function checkForUpdates(): array {
        return [
            'php_version' => $this->checkPHPVersion(),
            'dependencies' => $this->checkDependencies(),
            'system_packages' => $this->checkSystemPackages(),
            'ssl_certificates' => $this->checkSSLCertificates()
        ];
    }
    
    public function applySecurityPatches(): void {
        // Automated patch application
        $this->updateDependencies();
        $this->restartServices();
        $this->verifyIntegrity();
    }
}
```

---

**Security Version**: v1.0.0  
**Last Updated**: March 20, 2024  
**Next Review**: June 20, 2024

For security concerns or vulnerabilities, please contact: security@liquiswap.cm
