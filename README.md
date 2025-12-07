# HyperHire People API

A Laravel 12 API for managing people and their likes/dislikes with intelligent location-based recommendations.

## Features

- 📍 **Location-based Recommendations** - Prioritizes people from the same city, then same country, then others
- 👥 **Age-based Matching** - Prefers people within ±5 years age range
- ❤️ **Like/Dislike System** - Track interactions between people
- 📧 **Email Notifications** - Automatic alerts when someone gets 50+ likes
- ⏰ **Automated Cronjob** - Hourly checks for popular people
- 📚 **Swagger Documentation** - Interactive API documentation

## Tech Stack

- **Laravel 12** (PHP 8.2)
- **MySQL 8.0**
- **Docker & Docker Compose**
- **Swagger/OpenAPI 3.0**

## Quick Start

### Prerequisites
- Docker & Docker Compose installed
- Git

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/andyarismian/test-kerja-hyperhire-backend-laravel.git
cd test-kerja-hyperhire-backend-laravel
```

2. **Setup environment**
```bash
cp .env.example .env
# Edit .env if needed (database credentials, mail settings, etc.)
```

3. **Start Docker containers**
```bash
docker-compose up -d --build
```

4. **Run migrations and seeders**
```bash
docker-compose exec app php artisan migrate --seed
```

5. **Access the application**
- API Base URL: `http://localhost:8000`
- Swagger Documentation: `http://localhost:8000/api/docs`

## API Documentation

### Interactive Documentation
Visit **http://localhost:8000/api/docs** for the full interactive Swagger UI documentation.

### Endpoints

#### 1. Get Recommended People
```http
GET /api/v1/people/{id}?per_page=10
```
Returns recommended people for a specific person based on:
- Location priority (same city > same country > different country)
- Age similarity (±5 years preferred)
- Popularity (likes_count)

**Parameters:**
- `id` (path, required): Person ID
- `per_page` (query, optional): Results per page (default: 10)

**Example Request:**
```bash
curl http://localhost:8000/api/v1/people/1?per_page=10
```

#### 2. Like a Person
```http
POST /api/v1/people/{id}/like
Content-Type: application/json

{
  "liker_id": 1
}
```

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/people/5/like \
  -H "Content-Type: application/json" \
  -d '{"liker_id": 1}'
```

**Response:**
```json
{
  "status": "liked",
  "likes_count": 42
}
```

#### 3. Dislike a Person
```http
POST /api/v1/people/{id}/dislike
Content-Type: application/json

{
  "liker_id": 1
}
```

#### 4. Get Liked People
```http
GET /api/v1/people/{id}/likes
```
Returns all people that a specific person has liked.

**Example Response:**
```json
{
  "person_id": 1,
  "person_name": "John Doe",
  "liked_people": [...],
  "total_likes": 5
}
```

## Architecture

### Database Schema

**people table:**
- `id`, `name`, `age`, `pictures` (JSON), `location`
- `likes_count`: Total likes received
- `notified_at`: Timestamp when admin was notified (for 50+ likes)

**person_likes table:**
- `liker_id`: Person who liked
- `liked_id`: Person being liked
- `is_like`: Boolean (true = like, false = dislike)

### Recommendation Algorithm

1. **Location Priority:**
   - Priority 1: Same city
   - Priority 2: Same country, different city
   - Priority 3: Different country

2. **Age Priority:**
   - Priority 1: Age within ±5 years
   - Priority 2: Age outside range

3. **Final Sort:** By `likes_count` (descending) within each priority group

### Cronjob

Runs every hour to check for people with 50+ likes:
```bash
# Manual test
docker-compose exec app php artisan people:check-popular

# View schedule
docker-compose exec app php artisan schedule:list
```

See [SCHEDULER.md](SCHEDULER.md) for production deployment guide.

## Development

### Useful Commands

```bash
# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear

# View routes
docker-compose exec app php artisan route:list

# Run migrations
docker-compose exec app php artisan migrate

# Fresh migration with seed
docker-compose exec app php artisan migrate:fresh --seed

# Access MySQL
docker-compose exec mysql mysql -u laravel -psecret laravel

# View logs
docker-compose logs -f app
```

### Running Tests

```bash
docker-compose exec app php artisan test
```

## Configuration

### Environment Variables

Key settings in `.env`:

```env
# Application
APP_NAME=HyperHire
APP_URL=http://localhost
APP_PORT=8000

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

# Mail (for admin notifications)
MAIL_MAILER=log
MAIL_ADMIN=admin@example.com
```

## Production Deployment

### With Docker

1. Update `.env` for production
2. Build and deploy:
```bash
docker-compose -f docker-compose.prod.yml up -d --build
```

3. Setup scheduler (add to docker-compose):
```yaml
scheduler:
  command: php artisan schedule:work
```

See [SCHEDULER.md](SCHEDULER.md) for detailed scheduler setup.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues or questions, please open an issue on GitHub or contact admin@example.com.


In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
