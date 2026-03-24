# LiquiSwap API Documentation

## 📖 Overview

LiquiSwap provides a RESTful API for seamless financial transactions across Cameroon and Central Africa. This API enables developers to integrate swap operations, bundle purchases, wallet management, and more into their applications.

## 🔐 Authentication

### JWT Token Authentication

All API endpoints (except authentication endpoints) require a valid JWT token.

```http
Authorization: Bearer <jwt_token>
```

### CSRF Protection

All POST/PUT/DELETE requests must include a CSRF token:

```http
X-CSRF-Token: <csrf_token>
```

The CSRF token is available in the HTML meta tag:
```html
<meta name="csrf-token" content="<csrf_token>">
```

## 🌐 Base URL

- **Production**: `https://api.liquiswap.cm`
- **Staging**: `https://staging-api.liquiswap.cm`
- **Development**: `http://localhost:8080/api`

## 📊 Response Format

All API responses follow this standard format:

```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation completed successfully",
  "timestamp": "2024-03-20T12:00:00Z"
}
```

Error responses:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input parameters",
    "details": {
      "email": "Invalid email format"
    }
  },
  "timestamp": "2024-03-20T12:00:00Z"
}
```

## 🔑 Authentication Endpoints

### Register User

```http
POST /auth/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone_number": "+237677123456",
  "password": "securePassword123",
  "confirm_password": "securePassword123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "phone_number": "+237677123456",
      "created_at": "2024-03-20T12:00:00Z"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  },
  "message": "User registered successfully"
}
```

### Login

```http
POST /auth/login
```

**Request Body:**
```json
{
  "phone_number": "+237677123456",
  "password": "securePassword123",
  "remember_me": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "phone_number": "+237677123456"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 7200
  },
  "message": "Login successful"
}
```

### Biometric Login

```http
POST /auth/biometric
```

**Request Body:**
```json
{
  "biometric_data": "base64_encoded_biometric_hash",
  "device_id": "device_unique_identifier"
}
```

### Logout

```http
POST /auth/logout
```

**Headers:**
```http
Authorization: Bearer <jwt_token>
X-CSRF-Token: <csrf_token>
```

### Refresh Token

```http
POST /auth/refresh
```

**Headers:**
```http
Authorization: Bearer <jwt_token>
```

## 👤 User Management

### Get Profile

```http
GET /user/profile
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "phone_number": "+237677123456",
    "trust_score": 94,
    "member_since": "2024-01-15T00:00:00Z",
    "total_swaps": 1247,
    "success_rate": 98.5,
    "wallets": [
      {
        "provider": "MTN",
        "balance": 450000,
        "currency": "XAF"
      },
      {
        "provider": "ORANGE",
        "balance": 300000,
        "currency": "XAF"
      }
    ]
  }
}
```

### Update Profile

```http
PUT /user/profile
```

**Request Body:**
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com",
  "notification_preferences": {
    "email": true,
    "push": false,
    "sms": true
  }
}
```

### Get Wallets

```http
GET /user/wallets
```

### Add Wallet

```http
POST /user/wallets
```

**Request Body:**
```json
{
  "provider": "BANK",
  "account_number": "CM1234567890",
  "account_name": "John Doe",
  "bank_name": "UBA Cameroon"
}
```

### Get Trust Score

```http
GET /user/trust-score
```

**Response:**
```json
{
  "success": true,
  "data": {
    "score": 94,
    "level": "Excellent",
    "factors": {
      "transaction_history": 95,
      "identity_verification": 100,
      "network_activity": 88
    },
    "next_milestone": 95,
    "recommendations": [
      "Complete identity verification",
      "Increase transaction volume"
    ]
  }
}
```

### Get User Stats

```http
GET /user/stats
```

### Get Recent Contacts

```http
GET /user/contacts
```

## 💱 Swap Operations

### Get Exchange Rate

```http
GET /swap/rate
```

**Query Parameters:**
- `from`: Source provider (MTN, ORANGE)
- `to`: Destination provider (MTN, ORANGE)
- `amount`: Amount to exchange (optional)

**Response:**
```json
{
  "success": true,
  "data": {
    "rate": 1.00,
    "from": "ORANGE",
    "to": "MTN",
    "fee_percentage": 1.5,
    "min_amount": 100,
    "max_amount": 5000000,
    "updated_at": "2024-03-20T12:00:00Z"
  }
}
```

### Calculate Fees

```http
POST /swap/calculate
```

**Request Body:**
```json
{
  "amount": 10000,
  "source_provider": "ORANGE",
  "destination_provider": "MTN",
  "cashout_fee": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "amount": 10000,
    "fee": 150,
    "fee_percentage": 1.5,
    "receiver_gets": 9850,
    "total_deductible": 10150,
    "cashout_fee": 0,
    "estimated_time": "2-5 minutes"
  }
}
```

### Create Swap

```http
POST /swap/create
```

**Request Body:**
```json
{
  "amount": 10000,
  "source_provider": "ORANGE",
  "recipient_number": "+237677654321",
  "cashout_fee": false,
  "reference": "LS-2024-03-20-001"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "transaction_id": "tx_123456789",
    "reference": "LS-2024-03-20-001",
    "status": "pending",
    "amount": 10000,
    "fee": 150,
    "receiver_gets": 9850,
    "estimated_completion": "2024-03-20T12:05:00Z"
  },
  "message": "Swap initiated successfully"
}
```

### Confirm Swap

```http
POST /swap/confirm
```

**Request Body:**
```json
{
  "transaction_id": "tx_123456789",
  "confirmation_code": "123456"
}
```

### Get Swap Status

```http
GET /swap/status/{transaction_id}
```

### Get Swap History

```http
GET /swap/history
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `limit`: Items per page (default: 20)
- `status`: Filter by status (pending, completed, failed)
- `date_from`: Start date (YYYY-MM-DD)
- `date_to`: End date (YYYY-MM-DD)

## 📦 Bundle Management

### List Bundles

```http
GET /bundles
```

**Query Parameters:**
- `provider`: Filter by provider (MTN, ORANGE, CAMTEL, NEXTTEL)
- `category`: Filter by category (daily, weekly, monthly)
- `min_price`: Minimum price
- `max_price`: Maximum price
- `search`: Search term

**Response:**
```json
{
  "success": true,
  "data": {
    "bundles": [
      {
        "id": 1,
        "name": "MTN 2GB",
        "provider": "MTN",
        "category": "weekly",
        "data_volume": "2GB",
        "validity": "7 days",
        "price": 2000,
        "currency": "XAF",
        "features": [
          "4G LTE",
          "Unlimited WhatsApp",
          "Night bonus 250MB"
        ],
        "is_popular": true,
        "is_deal": false
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 45,
      "pages": 3
    }
  }
}
```

### Get Bundle Details

```http
GET /bundles/{id}
```

### Search Bundles

```http
GET /bundles/search
```

**Query Parameters:**
- `q`: Search query
- `provider`: Provider filter
- `category`: Category filter

### Purchase Bundle

```http
POST /bundles/purchase
```

**Request Body:**
```json
{
  "bundle_id": 1,
  "phone_number": "+237677123456",
  "payment_method": "MTN",
  "auto_renew": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "transaction_id": "bundle_tx_123456",
    "bundle": {
      "id": 1,
      "name": "MTN 2GB",
      "data_volume": "2GB",
      "validity": "7 days"
    },
    "status": "pending",
    "estimated_activation": "2024-03-20T12:02:00Z"
  },
  "message": "Bundle purchase initiated"
}
```

### Get Bundle Categories

```http
GET /bundles/categories
```

### Convert Airtime to Bundle

```http
POST /bundles/airtime-convert
```

**Request Body:**
```json
{
  "airtime_amount": 5000,
  "bundle_id": 1,
  "phone_number": "+237677123456",
  "provider": "MTN"
}
```

## 📱 USSD Codes

### List USSD Codes

```http
GET /ussd
```

**Query Parameters:**
- `category`: Filter by category (balance, data, airtime, services, support)
- `operator`: Filter by operator (MTN, ORANGE, CAMTEL, NEXTTEL)
- `search`: Search term

**Response:**
```json
{
  "success": true,
  "data": {
    "codes": [
      {
        "id": 1,
        "name": "Check Balance",
        "description": "Check main account balance",
        "code": "*155#",
        "operator": "ORANGE",
        "category": "balance",
        "is_popular": true
      }
    ]
  }
}
```

### Search USSD Codes

```http
GET /ussd/search
```

### Get USSD Categories

```http
GET /ussd/categories
```

### Get Operator Codes

```http
GET /ussd/operator/{operator}
```

## 📋 Transaction History

### Get Transactions

```http
GET /transactions
```

**Query Parameters:**
- `page`: Page number
- `limit`: Items per page
- `type`: Transaction type (swap, bundle, airtime, topup)
- `status`: Status (success, pending, failed)
- `date_from`: Start date
- `date_to`: End date
- `search`: Search term

**Response:**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": "tx_123456",
        "type": "swap",
        "status": "success",
        "amount": 10000,
        "fee": 150,
        "currency": "XAF",
        "description": "OM to MOMO Transfer",
        "created_at": "2024-03-20T12:00:00Z",
        "completed_at": "2024-03-20T12:03:00Z"
      }
    ],
    "summary": {
      "total_count": 1247,
      "total_amount": 12500000,
      "success_rate": 98.5,
      "this_month": {
        "count": 47,
        "amount": 470000
      }
    },
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 1247,
      "pages": 63
    }
  }
}
```

### Get Transaction Details

```http
GET /transactions/{id}
```

### Get Transaction Stats

```http
GET /transactions/stats
```

### Export Transactions

```http
GET /transactions/export
```

**Query Parameters:**
- `format`: Export format (csv, pdf, excel)
- `date_from`: Start date
- `date_to`: End date
- `type`: Transaction type

## ⚙️ Settings

### Get Settings

```http
GET /settings
```

**Response:**
```json
{
  "success": true,
  "data": {
    "profile": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone_number": "+237677123456"
    },
    "preferences": {
      "notifications": {
        "email": true,
        "push": true,
        "sms": false
      },
      "theme": "dark",
      "language": "en",
      "currency": "XAF"
    },
    "security": {
      "biometric_enabled": true,
      "two_factor_enabled": false,
      "session_timeout": 7200
    }
  }
}
```

### Update Settings

```http
PUT /settings
```

**Request Body:**
```json
{
  "notifications": {
    "email": true,
    "push": false,
    "sms": true
  },
  "theme": "light",
  "language": "fr"
}
```

### Change Password

```http
POST /settings/password
```

**Request Body:**
```json
{
  "current_password": "oldPassword123",
  "new_password": "newPassword123",
  "confirm_password": "newPassword123"
}
```

### Toggle Biometric

```http
POST /settings/biometric
```

**Request Body:**
```json
{
  "enabled": true,
  "biometric_data": "base64_encoded_hash"
}
```

### Setup 2FA

```http
POST /settings/2fa/setup
```

### Verify 2FA

```http
POST /settings/2fa/verify
```

### Get Active Sessions

```http
GET /settings/sessions
```

### Revoke Session

```http
DELETE /settings/sessions/{session_id}
```

## 🚨 Error Codes

| Code | Description | HTTP Status |
|------|-------------|-------------|
| `VALIDATION_ERROR` | Invalid input parameters | 400 |
| `UNAUTHORIZED` | Invalid or missing authentication | 401 |
| `FORBIDDEN` | Access denied | 403 |
| `NOT_FOUND` | Resource not found | 404 |
| `RATE_LIMITED` | Too many requests | 429 |
| `INSUFFICIENT_BALANCE` | Insufficient funds | 400 |
| `INVALID_PHONE` | Invalid phone number | 400 |
| `TRANSACTION_FAILED` | Transaction processing failed | 500 |
| `SERVER_ERROR` | Internal server error | 500 |

## 📝 Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **Authentication endpoints**: 5 requests per minute
- **Transaction endpoints**: 30 requests per minute
- **General endpoints**: 100 requests per minute

Rate limit headers are included in responses:
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1647792000
```

## 🧪 Testing

### Test Environment

Use the test environment for development and testing:

```http
POST https://staging-api.liquiswap.cm/auth/login
```

### Mock Data

The test environment includes mock data for testing:
- Test users: `+237677000001` to `+237677000010`
- Default password: `Test123!@#`
- Mock transaction IDs: `test_tx_123456`

### Webhooks

For real-time notifications, configure webhooks:

```http
POST /webhooks/notify
```

**Webhook Payload:**
```json
{
  "event": "transaction.completed",
  "data": {
    "transaction_id": "tx_123456",
    "status": "success",
    "amount": 10000
  },
  "timestamp": "2024-03-20T12:00:00Z"
}
```

## 📞 Support

For API support and questions:

- **Documentation**: [docs.liquiswap.cm](https://docs.liquiswap.cm)
- **API Status**: [status.liquiswap.cm](https://status.liquiswap.cm)
- **Email**: api-support@liquiswap.cm
- **Developer Discord**: [discord.liquiswap.cm](https://discord.liquiswap.cm)

## 🔄 Changelog

### v1.0.0 (2024-03-20)
- Initial API release
- Authentication endpoints
- Swap operations
- Bundle management
- Transaction history
- User settings

### v1.1.0 (Planned)
- Advanced analytics
- Bulk operations
- Enhanced webhook support
- Multi-currency support

---

**API Version**: v1.0.0  
**Last Updated**: March 20, 2024  
**Base URL**: https://api.liquiswap.cm
