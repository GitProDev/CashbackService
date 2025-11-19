# Cashback Service

## Project Overview
This project implements a **Cashback Service** for a Fintech platform. The service handles a two-day promotional cashback campaign, awarding users with cashback rewards in real-time while ensuring concurrency safety and enforcing daily reward limits.

Key requirements:
- Two-day campaign with a total of 100 rewards (split evenly between the two days).  
- Rewards unavailable between 00:00–09:00 and 20:00–23:59 (server time).  
- Users can only receive **one reward per day**.  
- High-volume concurrency handling: rewards are never over-allocated.  
- RESTful API design with JSON responses.  
- Merchant names are localized (`en`, `ro`, `ru`).

---

## Tech Stack
- **Backend:** PHP 8+, Laravel 12  
- **Database:** MySQL  
- **Cache / Concurrency:** DB transactions + `lockForUpdate()`  
- **Authentication:** Laravel Sanctum (token-based API auth)  
- **Testing:** Pest / PHPUnit (to be added for critical business logic)  

---

## Architecture & Design Decisions

### Data Model
Main models:
- **User** – represents the end-users who can receive cashback.  
- **DailyLimit** – tracks the remaining rewards per day to enforce daily limits.  
- **MerchantTranslation** – stores localized merchant names.  

### Concurrency Handling
To prevent multiple users from claiming the last reward simultaneously:
- Transactions are wrapped in a **DB transaction**.  
- The relevant row in `daily_limits` is locked using **`lockForUpdate()`**.  
- This ensures that the daily reward count is accurately decremented even under high concurrent load.

### Localization
- Users can specify their preferred locale via request headers (`Accept-Language`) or query parameters (`locale`).  
- Supported locales are defined in `config/app.php`: `'en', 'ro', 'ru'`.  
- If a translation is missing for a merchant, the system falls back to the default locale.

---

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | `/api/v1/register` | Register a new user |
| POST   | `/api/v1/login` | Log in and receive Sanctum token |
| POST   | `/api/v1/logout` | Log out and invalidate token |
| GET    | `/api/v1/user` | Retrieve authenticated user info |

### Cashback
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | `/api/v1/play` | Simulate a transaction ("play") and attempt to award cashback. Response indicates success or failure. |
| GET    | `/api/v1/reward_status` | Check if the user has already received a reward today. Returns reward details if available. |

### Merchants
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/api/v1/merchants` | Retrieve all merchants with localized names based on requested locale |

**Example `POST /api/v1/play` Response**
```json
{
  "won": true,
  "winner_name": "John",
  ""
}


## Installation & Usage

Clone the repository:
git clone https://github.com/GitProDev/CashbackService
cd CashbackService


Install dependencies:
composer install


Configure database credentials in your .env

php artisan key:generate


Run migrations and seeders:
php artisan migrate --seed


Serve the application:

sail up


Use Sanctum token for authenticated requests to /api/v1/play, /api/v1/reward_status, and /api/v1/merchants.