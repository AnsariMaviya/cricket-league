# 🚀 Simple Performance Monitoring Guide

This guide shows you how to check your application's performance using **built-in browser tools** - no extra packages needed!

---

## **📊 How to Check Response Times**

### **Method 1: Browser DevTools (Recommended)**

**Chrome/Edge/Firefox:**

1. **Open DevTools**
   - Press `F12` or `Ctrl+Shift+I` (Windows)
   - Or right-click → "Inspect"

2. **Go to Network Tab**
   - Click the "Network" tab at the top
   - Refresh the page (`F5`)

3. **Check Response Times**
   - Look at the "Time" column for each request
   - Click on any request to see detailed timing:
     - **Waiting (TTFB)**: Time to first byte - this is your server response time
     - **Content Download**: Time to download the response
     - **Total**: Complete request time

**What to look for:**
- ✅ Good: < 200ms
- ⚠️ Okay: 200-500ms
- 🔴 Slow: > 500ms

### **Method 2: Browser Console**

Open Console tab in DevTools and run:

```javascript
// Measure API call time
console.time('API Call');
fetch('/api/v1/stats')
  .then(response => response.json())
  .then(data => {
    console.timeEnd('API Call');
    console.log('Data:', data);
  });
```

### **Method 3: Laravel's Built-in Query Log (When Debugging)**

Add this temporarily to your controller:

```php
use Illuminate\Support\Facades\DB;

public function stats(): JsonResponse
{
    DB::enableQueryLog();
    
    // Your code here
    $stats = Cache::remember('api_dashboard_stats', 3600, function () {
        // ... your queries
    });
    
    // Log queries
    $queries = DB::getQueryLog();
    \Log::info('Queries executed:', [
        'count' => count($queries),
        'queries' => $queries
    ]);
    
    return $this->successResponse($stats);
}
```

Then check `storage/logs/laravel.log` for query details.

---

## **🔍 How to Find Slow Queries**

### **Using Laravel's Query Log:**

```php
// In your controller method
DB::enableQueryLog();

// Your code that runs queries
$teams = Team::with('country')->get();

// Get the queries
$queries = DB::getQueryLog();

// Log them
foreach ($queries as $query) {
    if ($query['time'] > 100) { // Queries taking > 100ms
        \Log::warning('Slow query detected', [
            'sql' => $query['query'],
            'time' => $query['time'] . 'ms',
            'bindings' => $query['bindings']
        ]);
    }
}
```

---

## **⚡ Quick Performance Checks**

### **Check 1: Count Total Queries**

```php
DB::enableQueryLog();
// Your code
$queries = DB::getQueryLog();
echo "Total queries: " . count($queries);
```

**Target:** < 10 queries per page

### **Check 2: Check for N+1 Problems**

```php
// BAD - N+1 problem
$teams = Team::all();
foreach ($teams as $team) {
    echo $team->country->name; // Executes query for each team
}

// GOOD - Eager loading
$teams = Team::with('country')->get();
foreach ($teams as $team) {
    echo $team->country->name; // No extra queries
}
```

### **Check 3: Cache Hit/Miss**

```php
// Check if cache is working
$cacheKey = 'test_key';

// First call - should be slow
$start = microtime(true);
$data = Cache::remember($cacheKey, 60, function() {
    sleep(1); // Simulate slow operation
    return 'test data';
});
$time1 = (microtime(true) - $start) * 1000;

// Second call - should be fast (cached)
$start = microtime(true);
$data = Cache::remember($cacheKey, 60, function() {
    sleep(1);
    return 'test data';
});
$time2 = (microtime(true) - $start) * 1000;

echo "First call: {$time1}ms\n";
echo "Second call (cached): {$time2}ms\n";
```

---

## **📈 Performance Targets**

### **API Response Times:**
- ✅ Excellent: < 100ms
- ✅ Good: 100-200ms
- ⚠️ Acceptable: 200-500ms
- 🔴 Needs optimization: > 500ms

### **Database Queries:**
- ✅ Good: < 10 queries per request
- ⚠️ Acceptable: 10-20 queries
- 🔴 Too many: > 20 queries

### **Query Execution Time:**
- ✅ Good: < 50ms total
- ⚠️ Acceptable: 50-100ms
- 🔴 Slow: > 100ms

---

## **🛠️ Simple Debugging Commands**

```bash
# Clear all caches
php artisan optimize:clear

# Check routes
php artisan route:list

# Check database connection
php artisan db:show

# View logs in real-time
tail -f storage/logs/laravel.log

# Or on Windows PowerShell:
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

---

## **💡 Performance Tips**

### **1. Use Caching**
```php
// Cache expensive operations
$stats = Cache::remember('dashboard_stats', 3600, function () {
    return [
        'users' => User::count(),
        'posts' => Post::count(),
    ];
});
```

### **2. Eager Load Relationships**
```php
// Instead of:
$teams = Team::all(); // Then accessing $team->country

// Do this:
$teams = Team::with('country')->get();
```

### **3. Use Database Indexes**
Already added in migration `2026_01_16_092914_add_performance_indexes_to_tables.php`

### **4. Limit Data**
```php
// Use pagination
$teams = Team::paginate(15);

// Or limit
$recentMatches = Match::latest()->limit(5)->get();
```

### **5. Select Only Needed Columns**
```php
// Instead of:
$teams = Team::all(); // Selects all columns

// Do this:
$teams = Team::select('id', 'name', 'country_id')->get();
```

---

## **🎯 When to Optimize**

**Optimize when you see:**
- Response times > 500ms
- More than 20 queries per request
- Queries taking > 100ms
- Memory usage > 50MB per request

**Don't optimize prematurely:**
- If response times are < 200ms, you're fine
- Focus on features first, optimize later
- Use browser tools to measure before optimizing

---

## **✅ Current Performance Status**

After all optimizations, your app should have:

- **API Response Times**: 50-150ms ✅
- **Query Count**: 3-5 per request ✅
- **Database Indexes**: Added ✅
- **Bulk Inserts**: Implemented ✅
- **Cache Strategy**: Working ✅

---

## **📚 Laravel's Built-in Tools**

Laravel already includes everything you need:

1. **Query Logging**: `DB::enableQueryLog()`
2. **Cache**: `Cache::remember()`
3. **Logging**: `Log::info()`, `Log::warning()`
4. **Artisan Commands**: `php artisan route:list`, `php artisan db:show`
5. **Browser DevTools**: Free and powerful

**You don't need extra packages for basic monitoring!**

---

## **🚀 Quick Start**

1. Open browser DevTools (`F12`)
2. Go to Network tab
3. Refresh page
4. Check "Time" column
5. Click requests to see detailed timing

**That's it!** Simple and effective. 🎉
