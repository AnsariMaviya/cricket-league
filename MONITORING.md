# 🔍 Laravel Monitoring & Debugging Guide

This project includes comprehensive **FREE** monitoring and debugging tools to help you track performance, queries, and issues.

---

## 🛠️ **Installed Tools**

### 1. **Laravel Debugbar** (Development Only)
Shows real-time debugging information at the bottom of your browser.

**What it shows:**
- ✅ Total queries executed
- ✅ Query execution time
- ✅ Memory usage
- ✅ Request/response details
- ✅ View rendering time
- ✅ Route information
- ✅ Session data
- ✅ Logs and exceptions

**How to use:**
1. Visit any page in your browser (e.g., `http://localhost:8000`)
2. Look at the bottom of the page for the debug bar
3. Click on tabs to see detailed information

**Screenshot location:** Bottom toolbar with tabs like "Timeline", "Queries", "Models", etc.

---

### 2. **Laravel Telescope** (Development Only)
Advanced monitoring dashboard for deep insights.

**What it shows:**
- ✅ All HTTP requests with timing
- ✅ Database queries with bindings
- ✅ Jobs and queues
- ✅ Cache operations
- ✅ Redis commands
- ✅ Exceptions and errors
- ✅ Logs
- ✅ Notifications
- ✅ Events

**How to access:**
1. Visit: `http://localhost:8000/telescope`
2. Browse through different sections:
   - **Requests**: See all HTTP requests with response times
   - **Queries**: View all database queries with execution time
   - **Exceptions**: Track errors and stack traces
   - **Logs**: View all application logs
   - **Cache**: Monitor cache hits/misses

**Pro Tips:**
- Filter by slow requests (> 500ms)
- Search for specific queries
- Track N+1 query problems
- Monitor memory usage per request

---

### 3. **Custom Query Monitoring**
Automatic logging of slow queries and performance issues.

**Log Files:**
- `storage/logs/slow-queries.log` - Queries taking > 100ms
- `storage/logs/performance.log` - Slow requests (> 500ms or > 20 queries)
- `storage/logs/queries.log` - All queries (if enabled)

**How to view logs:**
```bash
# View slow queries in real-time
tail -f storage/logs/slow-queries.log

# View performance issues
tail -f storage/logs/performance.log

# View all logs
tail -f storage/logs/laravel.log
```

---

### 4. **Performance Headers** (Development Only)
Every API response includes performance metrics in headers.

**Headers added:**
- `X-Debug-Time`: Total execution time (ms)
- `X-Debug-Memory`: Memory used (MB)
- `X-Debug-Queries`: Number of queries executed
- `X-Debug-Query-Time`: Total query time (ms)

**How to view:**
1. Open browser DevTools (F12)
2. Go to Network tab
3. Click on any API request
4. Check Response Headers

---

## 📊 **How to Monitor Your Application**

### **Check Query Performance:**
```bash
# 1. Visit a page (e.g., Teams page)
# 2. Look at Debugbar at bottom:
#    - Check "Queries" tab
#    - Look for duplicate queries (N+1 problem)
#    - Check total query count
#    - Look for slow queries (> 100ms)

# 3. Or check Telescope:
#    Visit: http://localhost:8000/telescope/queries
#    - Sort by "Duration" to find slow queries
#    - Look for queries executed multiple times
```

### **Find N+1 Query Problems:**
```bash
# Example: If you see this in Debugbar:
# SELECT * FROM teams
# SELECT * FROM countries WHERE id = 1
# SELECT * FROM countries WHERE id = 2
# SELECT * FROM countries WHERE id = 3
# ... (repeated for each team)

# This is N+1! Fix by eager loading:
Team::with('country')->get(); // ✅ Good (2 queries)
# Instead of:
Team::all(); // ❌ Bad (1 + N queries)
```

### **Monitor Slow Requests:**
```bash
# Check performance log
tail -f storage/logs/performance.log

# Look for entries like:
# [2026-01-16 14:30:00] Slow Request Detected
# - URL: /api/v1/teams
# - Execution Time: 850ms
# - Query Count: 45
# - Total Query Time: 720ms
```

### **Track Memory Usage:**
```bash
# Check response headers in browser DevTools
# X-Debug-Memory: 12.5MB

# If memory usage is high (> 50MB):
# - Check for large data sets
# - Use pagination
# - Optimize queries
# - Clear unnecessary data
```

---

## ⚙️ **Configuration**

### **Enable/Disable Query Logging:**
Add to `.env`:
```env
# Log all queries (verbose - use carefully)
LOG_ALL_QUERIES=false

# Slow query threshold (ms)
SLOW_QUERY_THRESHOLD=100

# Performance monitoring
PERFORMANCE_MONITORING=true
```

### **Telescope Configuration:**
Edit `config/telescope.php`:
```php
// Only enable in local environment
'enabled' => env('TELESCOPE_ENABLED', env('APP_ENV') === 'local'),

// Prune old entries after 24 hours
'prune' => [
    'hours' => 24,
],
```

### **Debugbar Configuration:**
Edit `config/debugbar.php`:
```php
// Enable/disable
'enabled' => env('DEBUGBAR_ENABLED', env('APP_DEBUG', false)),

// Collectors to enable
'collectors' => [
    'queries' => true,
    'models' => true,
    'route' => true,
    'views' => true,
],
```

---

## 🎯 **Performance Targets**

### **Good Performance:**
- ✅ Page load: < 200ms
- ✅ API response: < 100ms
- ✅ Query count: < 10 per request
- ✅ Query time: < 50ms total
- ✅ Memory usage: < 20MB

### **Needs Optimization:**
- ⚠️ Page load: 200-500ms
- ⚠️ API response: 100-300ms
- ⚠️ Query count: 10-20 per request
- ⚠️ Query time: 50-100ms total
- ⚠️ Memory usage: 20-50MB

### **Critical Issues:**
- 🔴 Page load: > 500ms
- 🔴 API response: > 300ms
- 🔴 Query count: > 20 per request
- 🔴 Query time: > 100ms total
- 🔴 Memory usage: > 50MB

---

## 🚀 **Quick Optimization Checklist**

### **When you see slow queries:**
1. ✅ Add database indexes
2. ✅ Use eager loading (`with()`)
3. ✅ Add query caching
4. ✅ Optimize WHERE clauses
5. ✅ Use `select()` to limit columns

### **When you see many queries:**
1. ✅ Check for N+1 problems
2. ✅ Use eager loading
3. ✅ Batch operations
4. ✅ Use query caching
5. ✅ Reduce relationship depth

### **When you see high memory:**
1. ✅ Use pagination
2. ✅ Use `chunk()` for large datasets
3. ✅ Clear unnecessary variables
4. ✅ Use generators
5. ✅ Optimize image handling

---

## 📝 **Common Issues & Solutions**

### **Issue: Too many queries**
```php
// ❌ Bad (N+1 problem)
$teams = Team::all();
foreach ($teams as $team) {
    echo $team->country->name; // Executes query for each team
}

// ✅ Good (2 queries total)
$teams = Team::with('country')->get();
foreach ($teams as $team) {
    echo $team->country->name;
}
```

### **Issue: Slow queries**
```php
// ❌ Bad (no index)
Team::where('team_name', 'like', '%Mumbai%')->get();

// ✅ Good (add index in migration)
Schema::table('teams', function (Blueprint $table) {
    $table->index('team_name');
});
```

### **Issue: Memory exhaustion**
```php
// ❌ Bad (loads all records)
$players = Player::all();

// ✅ Good (use pagination)
$players = Player::paginate(15);

// ✅ Better (use chunking for processing)
Player::chunk(100, function ($players) {
    foreach ($players as $player) {
        // Process player
    }
});
```

---

## 🔧 **Maintenance Commands**

```bash
# Clear old Telescope data
php artisan telescope:prune

# Clear all logs
php artisan log:clear

# Clear application cache
php artisan cache:clear

# View real-time logs
php artisan pail

# Run performance tests
php artisan test --parallel
```

---

## 📚 **Resources**

- **Debugbar Docs**: https://github.com/barryvdh/laravel-debugbar
- **Telescope Docs**: https://laravel.com/docs/telescope
- **Query Optimization**: https://laravel.com/docs/queries
- **Performance Tips**: https://laravel.com/docs/performance

---

## ✅ **Quick Start Checklist**

1. ✅ Visit `http://localhost:8000` - Check Debugbar at bottom
2. ✅ Visit `http://localhost:8000/telescope` - Browse monitoring dashboard
3. ✅ Open DevTools Network tab - Check X-Debug-* headers
4. ✅ Run `tail -f storage/logs/slow-queries.log` - Monitor slow queries
5. ✅ Check `storage/logs/performance.log` - Review slow requests

---

**🎉 You now have enterprise-level monitoring for FREE!**

Monitor your application, find bottlenecks, and optimize performance like a senior developer! 🚀
