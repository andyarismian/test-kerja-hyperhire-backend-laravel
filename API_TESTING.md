# API Testing Guide

## Access Points

### Swagger UI (Recommended)
**URL:** http://localhost:8000/api/docs

Interactive documentation where you can:
- View all endpoints and parameters
- Test API calls directly from browser
- See request/response examples
- Understand data models

### Postman Collection
Import `HyperHire_API.postman_collection.json` into Postman for quick testing.

## Testing Scenarios

### 1. Basic Flow Test

#### Step 1: Get Recommended People
```bash
GET http://localhost:8000/api/v1/people/1?per_page=10
```

**Expected Result:**
- Returns paginated list of people
- Ordered by location priority and age similarity
- Does not include person with ID 1 (self)

#### Step 2: Like Someone
```bash
POST http://localhost:8000/api/v1/people/5/like
Content-Type: application/json

{
  "liker_id": 1
}
```

**Expected Result:**
```json
{
  "status": "liked",
  "likes_count": 43
}
```

#### Step 3: View Liked People
```bash
GET http://localhost:8000/api/v1/people/1/likes
```

**Expected Result:**
- List includes person with ID 5
- Shows total likes count

#### Step 4: Get Recommendations Again
```bash
GET http://localhost:8000/api/v1/people/1?per_page=10
```

**Expected Result:**
- Person with ID 5 should NOT appear (already liked/seen)

### 2. Location-Based Recommendation Test

#### Setup
Person ID 1 is in "Jakarta, Indonesia"

#### Test
```bash
GET http://localhost:8000/api/v1/people/1?per_page=20
```

**Expected Order:**
1. People from Jakarta, Indonesia (same city)
2. People from other cities in Indonesia (same country)
3. People from other countries
4. Within each group: sorted by age similarity (±5 years) then likes_count

### 3. Age-Based Priority Test

If person ID 1 is 30 years old:
- People aged 25-35 appear first (within each location priority)
- People outside this range appear after (but still included)

### 4. Like/Dislike Flow Test

```bash
# Like person 10
POST http://localhost:8000/api/v1/people/10/like
{"liker_id": 1}

# Check likes_count increased
GET http://localhost:8000/api/v1/people/1/likes
# Should show person 10

# Dislike person 10
POST http://localhost:8000/api/v1/people/10/dislike
{"liker_id": 1}

# Verify interaction recorded (even though disliked)
GET http://localhost:8000/api/v1/people/1
# Person 10 should not appear in recommendations
```

### 5. Cronjob Test (Popular People Notification)

#### Setup
1. Create or update a person to have 51+ likes:
```bash
# Update via database or multiple API calls
POST http://localhost:8000/api/v1/people/2/like
{"liker_id": 1}
# Repeat with different liker_ids until likes_count > 50
```

2. Run cronjob manually:
```bash
docker-compose exec app php artisan people:check-popular
```

**Expected Result:**
```
Notification sent for: Person Name (ID: 2, Likes: 51)
Total notifications sent: 1
```

3. Check email was sent (check logs if using mail driver "log"):
```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

4. Run again - should show:
```
No popular people found that need notification.
```
(Because person 2 already has `notified_at` timestamp)

## CURL Examples

### Get Recommendations
```bash
curl http://localhost:8000/api/v1/people/1?per_page=10
```

### Like
```bash
curl -X POST http://localhost:8000/api/v1/people/5/like \
  -H "Content-Type: application/json" \
  -d '{"liker_id": 1}'
```

### Dislike
```bash
curl -X POST http://localhost:8000/api/v1/people/5/dislike \
  -H "Content-Type: application/json" \
  -d '{"liker_id": 1}'
```

### Get Liked People
```bash
curl http://localhost:8000/api/v1/people/1/likes
```

## Browser Testing (Swagger UI)

1. Open http://localhost:8000/api/docs
2. Click on any endpoint to expand
3. Click "Try it out" button
4. Fill in parameters
5. Click "Execute"
6. View response below

## Response Validation

### Success Response Structure

**Paginated List:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 2,
      "name": "Jane Smith",
      "age": 28,
      "pictures": ["url1", "url2", "url3"],
      "location": "Jakarta, Indonesia",
      "likes_count": 42,
      "notified_at": null,
      "created_at": "2025-12-06T10:00:00.000000Z",
      "updated_at": "2025-12-07T11:30:00.000000Z"
    }
  ],
  "first_page_url": "http://localhost:8000/api/v1/people/1?page=1",
  "from": 1,
  "last_page": 5,
  "per_page": 10,
  "to": 10,
  "total": 49
}
```

**Like/Dislike Response:**
```json
{
  "status": "liked",
  "likes_count": 43
}
```

**Get Likes Response:**
```json
{
  "person_id": 1,
  "person_name": "John Doe",
  "liked_people": [...],
  "total_likes": 5
}
```

### Error Responses

**404 Not Found:**
```json
{
  "message": "No query results for model [App\\Models\\Person] 999"
}
```

**400 Bad Request:**
```json
{
  "message": "liker_id is required"
}
```

## Database Verification

```bash
# Access MySQL
docker-compose exec mysql mysql -u laravel -psecret laravel

# Check people table
SELECT id, name, location, age, likes_count FROM people LIMIT 10;

# Check person_likes table
SELECT * FROM person_likes WHERE liker_id = 1;

# Check popular people (50+ likes)
SELECT id, name, likes_count, notified_at 
FROM people 
WHERE likes_count > 50 
ORDER BY likes_count DESC;
```

## Performance Testing

### Load Test with Apache Bench
```bash
# Test get recommendations endpoint
ab -n 1000 -c 10 http://localhost:8000/api/v1/people/1

# Test like endpoint
ab -n 100 -c 5 -p like.json -T application/json \
  http://localhost:8000/api/v1/people/5/like
```

Where `like.json` contains:
```json
{"liker_id": 1}
```

## Troubleshooting

### API Returns Empty Results
- Check if database is seeded: `docker-compose exec app php artisan db:seed`
- Verify person ID exists
- Check if all people are already liked/disliked by that person

### Like/Dislike Not Working
- Ensure `liker_id` is provided in request body
- Verify `liker_id` exists in people table
- Check for proper Content-Type header: `application/json`

### Swagger UI Not Loading
- Clear browser cache
- Verify `openapi.json` exists in `public/` directory
- Check Docker logs: `docker-compose logs -f app`

### Schedule Not Running
- Verify schedule is registered: `docker-compose exec app php artisan schedule:list`
- Check cache table exists: `docker-compose exec app php artisan migrate`
- Run manually to test: `docker-compose exec app php artisan people:check-popular`

## Production Testing Checklist

- [ ] All endpoints return expected responses
- [ ] Pagination works correctly
- [ ] Location-based sorting works
- [ ] Age-based priority works
- [ ] Like/Dislike updates counts correctly
- [ ] Already liked people excluded from recommendations
- [ ] Cronjob runs and sends emails
- [ ] Cronjob doesn't send duplicate notifications
- [ ] Error responses are proper JSON
- [ ] Response times are acceptable (<500ms)
- [ ] Database queries are optimized (no N+1)
