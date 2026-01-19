# Redis Setup for Ubuntu

## Redis Already Installed ✅

Since you already have Redis installed, just configure Laravel:

### 1. Install PHP Redis Client
```bash
composer require predis/predis
```

### 2. Update .env
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Start Redis (if not running)
```bash
sudo service redis-server start

# Check status
sudo service redis-server status

# Test connection
redis-cli ping
# Should return: PONG
```

### 4. Test Laravel Connection
```bash
php artisan tinker
>>> Cache::put('test', 'Redis works!', 60);
>>> Cache::get('test');
# Output: "Redis works!"
```

### 5. Run Queue Worker (for background jobs)
```bash
# Terminal 1: Run queue worker
php artisan queue:work --sleep=3 --tries=3

# Terminal 2: Your Laravel app
php artisan serve
```

### Usage in Code

Already implemented! The system will automatically:
- Cache scoreboards for 2-5 seconds
- Cache completed match stats forever
- Run background jobs for statistics generation

### Clear Cache if Needed
```bash
php artisan cache:clear
php artisan config:clear
```

## Done!
Redis is now handling all caching and queue jobs for optimal performance.
