# 🎯 3-Tier AI Commentary System

## Overview

Your cricket app now uses a **smart 3-tier fallback system** for commentary generation:

```
Tier 1: Gemini AI (Best Quality) 
   ↓ if quota exhausted
Tier 2: Event ML Hybrid (Context-Aware, Free)
   ↓ if fails
Tier 3: Enhanced Markov Chain (Statistical, Free)
   ↓ final fallback
Tier 4: Original System (Guaranteed)
```

## 🔧 Setup

### 1. Get Free Gemini API Key

```bash
# Visit: https://makersuite.google.com/app/apikey
# Click "Get API Key"
# Copy your key
```

### 2. Configure `.env`

```env
GEMINI_API_KEY=your_actual_api_key_here
GEMINI_DAILY_LIMIT=1500  # 1500 requests per day
GEMINI_RPM_LIMIT=15      # 15 requests per minute
```

### 3. Install Dependencies

```bash
composer require predis/predis
```

### 4. Build Markov Chain (Optional)

```bash
php artisan tinker
>>> app(App\Services\MarkovCommentaryGenerator::class)->buildChain();
```

## 📊 How It Works

### **Tier 1: Gemini AI** ⭐⭐⭐⭐⭐

**Quality:** Best  
**Cost:** Free (1000 requests/day)  
**Speed:** ~1-2 seconds

**Example Output:**
```
"What a savage slog! Chahal departs, sent sky-high over long-off!"
"BOOM! Kohli smashes it straight back for a massive six!"
```

**When Used:**
- Quota available
- API responds successfully
- Best for exciting moments (wickets, sixes)

### **Tier 2: Event ML Hybrid** ⭐⭐⭐⭐

**Quality:** Very Good  
**Cost:** FREE (Unlimited)  
**Speed:** <100ms

**Example Output:**
```
"OUT! Chahal is caught by the fielder."
"FOUR! Beautiful shot by Kohli!"
"Dot ball. Good defense by Sharma."
```

**When Used:**
- Gemini quota exhausted
- Primary fallback
- Always reliable

**How It Works:**
- Classifies events (wicket_bowled, six, four, dot, etc.)
- 50+ context-aware templates per event
- Future: ML-based template selection

### **Tier 3: Enhanced Markov Chain** ⭐⭐⭐

**Quality:** Good  
**Cost:** FREE (Unlimited)  
**Speed:** <50ms

**Example Output:**
```
"WICKET! The batsman is dismissed by the bowler."
"Massive six goes straight over the boundary!"
```

**When Used:**
- Event ML fails (rare)
- Backup for exotic sequences

**How It Works:**
- Statistical word-chain model
- Learns from existing commentary
- Generates variations

## 🎮 Usage

### Automatic (Already Integrated)

The system **automatically** switches between tiers during match simulation. No code changes needed!

```php
// In MatchSimulationEngine.php
$commentary = $this->commentaryService->generate($ball, $batsman, $bowler);
// ✅ Automatically uses best available tier
```

### Check Quota Status

```bash
php artisan tinker
>>> app(App\Services\CommentaryService::class)->getQuotaStats();
```

**Output:**
```php
[
  'gemini' => [
    'daily_used' => 450,
    'daily_limit' => 1500,
    'rpm_used' => 5,
    'rpm_limit' => 15,
    'available' => true
  ],
  'event_ml' => [
    'status' => 'unlimited',
    'available' => true
  ],
  'markov' => [
    'status' => 'unlimited',
    'available' => true
  ]
]
```

### Reset Daily Quota

Quota resets automatically at midnight. Or manually:

```bash
php artisan tinker
>>> Cache::forget('gemini_quota_' . now()->format('Y-m-d'));
```

## 📈 Training & Optimization

### Train Markov Model

Run nightly to improve statistical patterns:

```bash
php artisan tinker
>>> app(App\Services\MarkovCommentaryGenerator::class)->buildChain();
```

### Future: Train Event ML

```php
// In EventMLCommentaryGenerator
public function train() {
    // TODO: Analyze ball_by_ball + commentary
    // Build context weights
    // Improve template selection
}
```

## 🧪 Testing

### Test All Tiers

```bash
php artisan tinker
```

```php
// Test Tier 1: Gemini
$gemini = app(App\Services\GeminiCommentaryGenerator::class);
$context = ['runs' => 6, 'is_six' => true, 'batsman' => (object)['name' => 'Kohli']];
echo $gemini->generate($context);

// Test Tier 2: Event ML
$eventML = app(App\Services\EventMLCommentaryGenerator::class);
echo $eventML->generate($context);

// Test Tier 3: Markov
$markov = app(App\Services\MarkovCommentaryGenerator::class);
echo $markov->generate($context);
```

### Test Live Commentary

```bash
# Start a match
curl http://localhost:8000/api/v1/live-matches/11/start

# Simulate balls and watch commentary quality
curl http://localhost:8000/api/v1/live-matches/11/simulate-ball
```

## 🔍 Monitoring

### Check Logs

```bash
tail -f storage/logs/laravel.log | grep Commentary
```

**You'll see:**
```
Commentary generated via Gemini AI
Commentary generated via Event ML Hybrid
Commentary generated via Markov Chain
```

### Quota Usage

```php
// Track which tier is being used
Cache::get('gemini_quota_' . now()->format('Y-m-d'))
```

## 🚀 Performance

| Tier | Speed | Quality | Cost |
|------|-------|---------|------|
| Gemini AI | 1-2s | ⭐⭐⭐⭐⭐ | Free* |
| Event ML | <100ms | ⭐⭐⭐⭐ | Free |
| Markov | <50ms | ⭐⭐⭐ | Free |

*1500 requests/day, 15 requests/minute free tier

## 💡 Best Practices

1. **Let it run**: System handles fallback automatically
2. **Monitor quota**: Check daily usage
3. **Train Markov**: Run weekly for better patterns
4. **Test locally**: Use Event ML during development
5. **Production**: Enable Gemini for best quality

## 🛠 Troubleshooting

### "Gemini API returns no content"

**Fix:** Check API key in `.env`
```bash
GEMINI_API_KEY=AIzaSy...actual_key
```

### "Quota exhausted"

**Expected behavior** - System automatically switches to Event ML.  
Reset tomorrow or increase limit in `.env`

### "All tiers failing"

**Impossible** - Final fallback (original system) always works.  
Check logs: `storage/logs/laravel.log`

## 📝 Configuration

### Adjust Quota Limit

```env
# .env
GEMINI_DAILY_LIMIT=2000  # Increase if needed
```

### Disable Gemini

```env
# .env
GEMINI_API_KEY=  # Leave empty
```

System will use Event ML by default.

## 🎯 Summary

✅ **3-tier system** ensures quality never drops completely  
✅ **Auto-switching** requires zero code changes  
✅ **Free fallbacks** always available  
✅ **Smart monitoring** tracks usage  
✅ **Easy testing** with tinker commands  

**Result:** Best possible commentary quality at all times! 🏏
