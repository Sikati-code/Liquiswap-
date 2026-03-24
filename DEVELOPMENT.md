# LiquiSwap Development Guide

## 🛠 Development Setup

This guide helps you set up a local development environment for LiquiSwap.

## 📋 Prerequisites

### Required Software
- **PHP**: 8.0+ with extensions:
  - `php-pgsql`
  - `php-curl`
  - `php-json`
  - `php-mbstring`
  - `php-xml`
  - `php-tokenizer`
  - `php-bcmath`
- **PostgreSQL**: 14+
- **Node.js**: 16+ (for build tools)
- **Composer**: Latest version
- **Git**: Version control

### Development Tools (Recommended)
- **VS Code**: With PHP and PostgreSQL extensions
- **Postman**: For API testing
- **pgAdmin**: Database management
- **Docker**: For containerized development

## 🚀 Local Setup

### Step 1: Clone Repository

```bash
git clone https://github.com/your-org/liquiswap.git
cd liquiswap
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (if using build tools)
npm install
```

### Step 3: Configure Environment

```bash
# Copy environment template
cp .env.example .env.development

# Edit development configuration
nano .env.development
```

Development environment variables:
```env
# Development Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=liquiswap_dev
DB_USER=dev_user
DB_PASSWORD=dev_password

# Development URL
APP_URL=http://localhost:8080
APP_NAME=LiquiSwap (Dev)
APP_ENV=development

# Development Security (use test keys)
JWT_SECRET=dev-jwt-secret-key-for-testing-only
ENCRYPTION_KEY=dev-encryption-key-for-testing-only

# Debug Mode
DEBUG=true
LOG_LEVEL=DEBUG
```

### Step 4: Database Setup

```bash
# Create development database
createdb liqui_swap_dev

# Create development user
psql -d postgres
CREATE USER dev_user WITH PASSWORD 'dev_password';
GRANT ALL PRIVILEGES ON DATABASE liqui_swap_dev TO dev_user;
\q

# Import schema
psql -h localhost -U dev_user -d liqui_swap_dev -f database.sql
```

### Step 5: Local Web Server

#### Option A: PHP Built-in Server (Quick Start)

```bash
# Start development server
php -S localhost:8080

# Or with custom configuration
php -S localhost:8080 -t . router.php
```

#### Option B: Apache with XAMPP/MAMP

1. Install XAMPP/MAMP
2. Copy project to htdocs folder
3. Configure virtual host
4. Enable Apache modules: `rewrite`, `headers`

#### Option C: Docker Development

```dockerfile
# Dockerfile.dev
FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install pdo_pgsql pgsql curl json mbstring

# Install PostgreSQL client
RUN apt-get update && apt-get install -y postgresql-client

# Copy application
COPY . /var/www/html/
```

```yaml
# docker-compose.yml
version: '3.8'
services:
  web:
    build:
      context: .
      dockerfile: Dockerfile.dev
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    environment:
      - APP_ENV=development
  
  db:
    image: postgres:15
    environment:
      POSTGRES_DB: liqui_swap_dev
      POSTGRES_USER: dev_user
      POSTGRES_PASSWORD: dev_password
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data

volumes:
  postgres_data:
```

```bash
# Start development environment
docker-compose up -d
```

## 🔧 Development Workflow

### Code Structure

```
liquiswap/
├── api/                    # API endpoints
│   ├── auth.php           # Authentication API
│   ├── user.php           # User management
│   ├── swap.php           # Swap operations
│   └── ...
├── includes/              # Core classes
│   ├── Database.php       # Database wrapper
│   ├── Auth.php           # Authentication
│   ├── JWT.php            # JWT handling
│   └── ...
├── pages/                 # Frontend pages
│   ├── dashboard.php      # Main dashboard
│   ├── login.php          # Login page
│   └── ...
├── assets/               # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── images/           # Images
├── tests/                # Test files
├── docs/                 # Documentation
└── scripts/              # Utility scripts
```

### Coding Standards

#### PHP Standards (PSR-12)

```php
<?php
declare(strict_types=1);

namespace App\Api;

use App\Includes\Database;

class UserAPI
{
    private Database $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    
    /**
     * Get user profile
     */
    public function getProfile(int $userId): array
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        
        return $stmt->fetch() ?: [];
    }
}
```

#### JavaScript Standards (ES6+)

```javascript
// Use modern ES6+ syntax
class APIClient {
    constructor(baseURL) {
        this.baseURL = baseURL;
        this.headers = {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.CSRF_TOKEN
        };
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: { ...this.headers, ...options.headers },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
}
```

#### CSS Standards (BEM Methodology)

```css
/* Block */
.swap-calculator {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Element */
.swap-calculator__input {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
}

/* Modifier */
.swap-calculator__input--error {
    border-color: var(--error-color);
    background-color: var(--error-bg);
}
```

### Git Workflow

#### Branch Naming

```bash
# Feature branches
feature/user-authentication
feature/swap-calculator

# Bugfix branches
bugfix/login-validation-error
bugfix/database-connection-issue

# Release branches
release/v1.1.0
release/v1.2.0-beta
```

#### Commit Messages

```bash
# Format: type(scope): description
feat(auth): add biometric login support
fix(swap): resolve fee calculation error
docs(readme): update installation instructions
style(css): improve button hover effects
refactor(db): optimize transaction queries
test(api): add user endpoint tests
```

#### Pull Request Template

```markdown
## Description
Brief description of changes and their purpose.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed
- [ ] Cross-browser testing done

## Checklist
- [ ] Code follows PSR-12 standards
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Security considerations addressed
```

## 🧪 Testing

### Unit Testing

```php
// tests/AuthTest.php
use PHPUnit\Framework\TestCase;
use App\Includes\Auth;

class AuthTest extends TestCase
{
    private Auth $auth;
    
    protected function setUp(): void
    {
        $this->auth = new Auth();
    }
    
    public function testValidLogin(): void
    {
        $result = $this->auth->login('test@example.com', 'password123');
        $this->assertTrue($result['success']);
    }
    
    public function testInvalidLogin(): void
    {
        $result = $this->auth->login('test@example.com', 'wrongpassword');
        $this->assertFalse($result['success']);
    }
}
```

### Integration Testing

```php
// tests/ApiTest.php
class ApiTest extends TestCase
{
    public function testUserRegistration(): void
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('user', $response->getData());
    }
}
```

### Frontend Testing

```javascript
// tests/frontend.test.js
import { APIClient } from '../assets/js/api.js';

describe('APIClient', () => {
    let api;
    
    beforeEach(() => {
        api = new APIClient('http://localhost:8080/api');
    });
    
    test('should make successful API call', async () => {
        const mockFetch = jest.fn().mockResolvedValue({
            json: () => Promise.resolve({ success: true })
        });
        global.fetch = mockFetch;
        
        const result = await api.request('/test');
        expect(result.success).toBe(true);
    });
});
```

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/AuthTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run JavaScript tests
npm test
```

## 🐛 Debugging

### PHP Debugging

#### Xdebug Configuration

```ini
; php.ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.idekey=VSCODE
```

#### VS Code Debug Configuration

```json
// .vscode/launch.json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

### Database Debugging

```sql
-- Enable query logging
ALTER SYSTEM SET log_statement = 'all';
ALTER SYSTEM SET log_min_duration_statement = 0;
SELECT pg_reload_conf();

-- View slow queries
SELECT query, mean_time, calls 
FROM pg_stat_statements 
ORDER BY mean_time DESC;

-- Analyze query plan
EXPLAIN ANALYZE SELECT * FROM users WHERE email = 'test@example.com';
```

### Frontend Debugging

```javascript
// Enable debug mode
window.DEBUG = true;

// Debug API calls
api.request('/test', { debug: true })
    .then(data => console.log('API Response:', data))
    .catch(error => console.error('API Error:', error));
```

## 📊 Performance Profiling

### PHP Performance

```php
// Profile execution time
$start = microtime(true);

// Your code here
$result = $auth->login($email, $password);

$end = microtime(true);
$duration = ($end - $start) * 1000;
error_log("Login took {$duration}ms");
```

### Database Performance

```sql
-- Monitor slow queries
SELECT query, mean_time, calls, total_time
FROM pg_stat_statements
WHERE mean_time > 100
ORDER BY mean_time DESC;

-- Check index usage
SELECT schemaname, tablename, attname, n_distinct, correlation
FROM pg_stats
WHERE tablename = 'users';
```

### Frontend Performance

```javascript
// Measure page load time
window.addEventListener('load', () => {
    const perfData = window.performance.timing;
    const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
    console.log(`Page load time: ${pageLoadTime}ms`);
});

// Profile API calls
const originalFetch = window.fetch;
window.fetch = function(...args) {
    const start = performance.now();
    return originalFetch.apply(this, args)
        .finally(() => {
            const duration = performance.now() - start;
            console.log(`API call took ${duration}ms`);
        });
};
```

## 🔒 Security Testing

### Common Vulnerabilities to Test

1. **SQL Injection**
   ```php
   // Vulnerable
   $sql = "SELECT * FROM users WHERE id = " . $_GET['id'];
   
   // Secure
   $sql = "SELECT * FROM users WHERE id = :id";
   $stmt = $pdo->prepare($sql);
   $stmt->execute(['id' => $_GET['id']]);
   ```

2. **XSS Prevention**
   ```php
   // Vulnerable
   echo $_POST['name'];
   
   // Secure
   echo htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
   ```

3. **CSRF Protection**
   ```php
   // Always include CSRF token
   <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
   ```

### Security Testing Tools

```bash
# OWASP ZAP for security scanning
docker run -t owasp/zap2docker-stable zap-baseline.py -t http://localhost:8080

# SQLMap for SQL injection testing
sqlmap -u "http://localhost:8080/api/test?id=1" --dbs

# Nmap for port scanning
nmap -sV localhost
```

## 📝 Documentation

### API Documentation

```php
/**
 * Authenticate user
 * 
 * @param string $email User email
 * @param string $password User password
 * @return array Authentication result
 * 
 * @throws InvalidArgumentException If email is invalid
 * @throws AuthenticationException If credentials are wrong
 * 
 * @example
 * $result = $auth->login('user@example.com', 'password123');
 * if ($result['success']) {
 *     echo "Login successful";
 * }
 */
public function login(string $email, string $password): array
```

### Code Comments

```php
// TODO: Implement rate limiting for login attempts
// FIXME: This query is slow, needs optimization
// NOTE: This is a temporary solution
// WARNING: Do not use in production
```

## 🚀 Deployment

### Pre-deployment Checklist

- [ ] All tests pass
- [ ] Code review completed
- [ ] Security scan passed
- [ ] Performance benchmarks met
- [ ] Documentation updated
- [ ] Backup created
- [ ] Rollback plan ready

### Staging Environment

```bash
# Deploy to staging
git checkout develop
git pull origin develop
./scripts/deploy.sh staging

# Run smoke tests
./tests/smoke-tests.sh http://staging.liquiswap.cm
```

### Production Deployment

```bash
# Deploy to production
git checkout main
git pull origin main
./scripts/deploy.sh production

# Post-deployment verification
./scripts/health-check.sh https://liquiswap.cm
```

## 🤝 Contributing

### Before Contributing

1. Read the [Code of Conduct](CODE_OF_CONDUCT.md)
2. Set up development environment
3. Run existing tests
4. Create issue for your feature/bug
5. Fork and create feature branch

### Submitting Changes

1. Write tests for new functionality
2. Ensure all tests pass
3. Update documentation
4. Submit pull request
5. Respond to code review feedback

---

**Happy Coding! 🎉**

For questions or support, join our developer community at [dev.liquiswap.cm](https://dev.liquiswap.cm).
