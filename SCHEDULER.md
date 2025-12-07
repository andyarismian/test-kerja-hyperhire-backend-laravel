# Laravel Scheduler - Popular People Notification

## Overview
Cronjob yang berjalan setiap jam untuk memeriksa apakah ada orang yang mendapat lebih dari 50 likes dan mengirim notifikasi email ke admin.

## Command
```bash
php artisan people:check-popular
```

## Schedule
Berjalan otomatis setiap jam (hourly) melalui Laravel Scheduler.

## Setup di Production

### 1. Menjalankan Scheduler dengan Docker
Tambahkan service scheduler di `docker-compose.yml`:

```yaml
scheduler:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: laravel-scheduler
  restart: unless-stopped
  working_dir: /var/www/html
  volumes:
    - .:/var/www/html
  environment:
    - APP_NAME=${APP_NAME}
    - APP_ENV=${APP_ENV}
    - APP_KEY=${APP_KEY}
    - DB_CONNECTION=${DB_CONNECTION}
    - DB_HOST=mysql
    - DB_PORT=${DB_PORT}
    - DB_DATABASE=${DB_DATABASE}
    - DB_USERNAME=${DB_USERNAME}
    - DB_PASSWORD=${DB_PASSWORD}
    - MAIL_MAILER=${MAIL_MAILER}
    - MAIL_HOST=${MAIL_HOST}
    - MAIL_PORT=${MAIL_PORT}
    - MAIL_USERNAME=${MAIL_USERNAME}
    - MAIL_PASSWORD=${MAIL_PASSWORD}
    - MAIL_ADMIN=${MAIL_ADMIN}
  command: php artisan schedule:work
  depends_on:
    mysql:
      condition: service_healthy
  networks:
    - laravel-network
```

### 2. Menjalankan dengan Cron (Server Linux)
Tambahkan ke crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Testing Manual
```bash
# Test command
docker-compose exec app php artisan people:check-popular

# Lihat daftar schedule
docker-compose exec app php artisan schedule:list

# Run scheduler secara manual (test)
docker-compose exec app php artisan schedule:run
```

## Configuration
Email admin dapat dikonfigurasi di file `.env`:
```env
MAIL_ADMIN=admin@example.com
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

## How it Works
1. Command berjalan setiap jam
2. Mencari people dengan `likes_count > 50` dan `notified_at = NULL`
3. Mengirim email ke admin untuk setiap person yang memenuhi kriteria
4. Update `notified_at` agar tidak mengirim notifikasi berulang
5. Log hasil di console

## Database Field
Table `people` memiliki field:
- `likes_count`: Jumlah likes yang diterima
- `notified_at`: Timestamp ketika admin sudah dinotifikasi (NULL = belum dinotifikasi)
