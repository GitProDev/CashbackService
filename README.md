# Cashback Service

## Project Overview

This service implements a two-day promotional Cashback Service for a fintech platform. It awards users with cashback rewards in real time while ensuring concurrency safety and enforcing per-day user and global limits.

Key requirements:
- Two-day campaign with 100 total rewards (50 rewards per day).  
- Rewards disabled between 00:00–09:00 and 20:00–23:59 (server time).  
- One reward per user per day.  
- Strong concurrency guarantees so rewards are never over-allocated.  
- RESTful JSON API.  
- Merchant names localized for `en`, `ro`, `ru`.

## Tech Stack
- Backend: PHP 8+, Laravel 12  
- Database: MySQL  
- Cache / Concurrency: DB transactions with lockForUpdate()  
- Authentication: Laravel Sanctum (token-based)  
- Testing: Pest / PHPUnit (for critical business logic)

## Architecture & Design Decisions

### Data model
- User — end users eligible for cashback.  
- DailyLimit — tracks remaining rewards per campaign day and enforces global daily capacity.  
- MerchantTranslation — localized merchant names.

### Concurrency handling
- Claim flow runs inside a DB transaction.  
- The relevant `daily_limits` row is locked with `SELECT ... FOR UPDATE` (Laravel: `lockForUpdate()`) to prevent race conditions.  
- The daily counter is decremented only after the lock and all business checks succeed.

### Localization
- Locale can be provided via `Accept-Language` header or `locale` query parameter.  
- Supported locales: `en`, `ro`, `ru` (configured in config/app.php).  
- Missing merchant translations fall back to the default locale.

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | /api/v1/register | Register a new user |
| POST   | /api/v1/login    | Authenticate and receive Sanctum token |
| POST   | /api/v1/logout   | Invalidate token |
| GET    | /api/v1/user     | Get authenticated user info |

### Cashback
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | /api/v1/play | Simulate a transaction and attempt to award cashback |
| GET    | /api/v1/reward_status | Check if the user has received a reward today |

### Merchants
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | /api/v1/merchants | Retrieve merchants with localized names (Accept-Language / locale) |

Example successful response for POST /api/v1/play:
```json
{
    "won": true,
    "winner_name": "John",
    "merchant": {
        "id": 123,
        "name": "Acme Store",
        "locale": "en"
    },
    "awarded_at": "2025-11-19T12:34:56Z"
}
```

Example failure response (no reward available or user already won today):
```json
{
    "won": false,
    "reason": "daily_limit_reached"
}
```

## Installation & Usage

Clone and enter the repo:
```bash
git clone https://github.com/GitProDev/CashbackService
cd CashbackService
```

Install dependencies:
```bash
composer install
```

Configure environment:
- Copy `.env.example` to `.env` and set DB credentials.
```bash
php artisan key:generate
```

Run migrations and seeders:
```bash
php artisan migrate --seed
```

Serve the application (using Sail if available):
```bash
# with Laravel Sail
./vendor/bin/sail up -d

# or using local PHP server
php artisan serve
```

Use the Sanctum token for authenticated requests to `/api/v1/play`, `/api/v1/reward_status`, and `/api/v1/merchants`.

## Testing
Run automated tests (Pest / PHPUnit):
```bash
php artisan test
```