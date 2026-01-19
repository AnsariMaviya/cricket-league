# Quick Start Guide - Live Match Simulation System

## 🚀 Get Started in 5 Minutes

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Seed Some Data (Optional)
Create a few teams, players, and a match through your existing interface or:

```bash
php artisan tinker
```

```php
// Create teams
$team1 = Team::create(['team_name' => 'Mumbai Indians', 'country_id' => 1]);
$team2 = Team::create(['team_name' => 'Chennai Super Kings', 'country_id' => 1]);

// Create players for team 1
Player::create(['name' => 'Rohit Sharma', 'team_id' => $team1->team_id, 'role' => 'Batsman']);
Player::create(['name' => 'Jasprit Bumrah', 'team_id' => $team1->team_id, 'role' => 'Bowler']);

// Create players for team 2
Player::create(['name' => 'MS Dhoni', 'team_id' => $team2->team_id, 'role' => 'Batsman']);
Player::create(['name' => 'Ravindra Jadeja', 'team_id' => $team2->team_id, 'role' => 'All-Rounder']);

// Create venue
$venue = Venue::create(['name' => 'Wankhede Stadium', 'city' => 'Mumbai']);

// Create match
$match = CricketMatch::create([
    'venue_id' => $venue->venue_id,
    'first_team_id' => $team1->team_id,
    'second_team_id' => $team2->team_id,
    'match_type' => 'T20',
    'overs' => 20,
    'match_date' => now(),
    'status' => 'scheduled'
]);
```

### Step 3: Initialize Achievements
```php
$service = new \App\Services\GamificationService();
$service->initializeAchievements();
```

### Step 4: Simulate Your First Match!

**Option A: Command Line**
```bash
php artisan match:simulate 1 --delay=1
```

**Option B: API**
```bash
# Start the match
curl -X POST http://localhost:8000/api/v1/live-matches/1/start

# Simulate each ball
curl -X POST http://localhost:8000/api/v1/live-matches/1/simulate-ball

# Or auto-simulate the entire match
curl -X POST http://localhost:8000/api/v1/live-matches/1/auto-simulate \
  -H "Content-Type: application/json" \
  -d '{"delay": 3}'
```

**Option C: PHP Code**
```php
use App\Services\MatchSimulationEngine;
use App\Models\CricketMatch;

$engine = new MatchSimulationEngine();
$match = CricketMatch::find(1);

// Auto-simulate with 3-second delays
$result = $engine->autoSimulate($match, 3);

echo "Match completed!\n";
echo "Result: " . $result->outcome . "\n";
```

### Step 5: View the Scoreboard
```bash
# Get full scoreboard
curl http://localhost:8000/api/v1/live-matches/1/scoreboard

# Get mini scoreboard
curl http://localhost:8000/api/v1/live-matches/1/mini-scoreboard

# Get match summary (after completion)
curl http://localhost:8000/api/v1/live-matches/1/summary
```

---

## 🎮 Test AI Predictions

```bash
# Generate AI prediction for a match
curl http://localhost:8000/api/v1/predictions/match/1

# Analyze player performance
curl http://localhost:8000/api/v1/predictions/player/1/analysis

# Get team recommendation
curl http://localhost:8000/api/v1/predictions/team/1/recommend?match_type=T20
```

---

## 🏆 Test Gamification

First, register a user or use existing credentials:

```bash
# Get user stats (requires authentication)
curl http://localhost:8000/api/v1/gamification/stats \
  -H "Authorization: Bearer YOUR_TOKEN"

# View leaderboard
curl http://localhost:8000/api/v1/gamification/leaderboard?timeframe=all&limit=10

# View achievements
curl http://localhost:8000/api/v1/gamification/achievements
```

---

## 🌐 Frontend Integration Example

Add this to your Vue.js app:

```vue
<template>
  <div>
    <LiveScoreboard :match-id="1" :auto-refresh="true" />
  </div>
</template>

<script>
import LiveScoreboard from './components/LiveScoreboard.vue';

export default {
  components: {
    LiveScoreboard
  }
}
</script>
```

---

## 📊 View Live Matches

```bash
# Get all live matches
curl http://localhost:8000/api/v1/live-matches

# Get upcoming matches
curl http://localhost:8000/api/v1/live-matches/upcoming
```

---

## 🎯 Common Use Cases

### 1. Fantasy League
- Simulate matches daily
- Users earn points based on their selected players
- Track statistics in `player_match_stats` table

### 2. Prediction Game
- Generate AI predictions before each match
- Users submit their predictions
- Award points for correct predictions
- Display leaderboard

### 3. Live Tournament
- Schedule multiple matches
- Auto-simulate at specific times using Laravel scheduler
- Broadcast updates to all viewers

---

## 🔧 Configuration

### Customize Ball Speed
Edit the delay in auto-simulation:
```php
$engine->autoSimulate($match, delaySeconds: 1); // Fast
$engine->autoSimulate($match, delaySeconds: 5); // Slow
```

### Customize Match Format
```php
CricketMatch::create([
    'match_type' => 'ODI',
    'overs' => 50, // ODI format
    // ... other fields
]);
```

### Enable Real-time Broadcasting
Update `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=your_cluster
```

---

## 🐛 Troubleshooting

**Match won't start?**
- Ensure status is 'scheduled'
- Check teams have at least 2 batsmen and 1 bowler

**No commentary generated?**
- Check database for `ball_by_ball` records
- Verify `CommentaryGenerator` is working

**API returns 500 error?**
- Check Laravel logs: `storage/logs/laravel.log`
- Run: `php artisan config:clear`

---

## 📚 Next Steps

1. Read the full guide: `LIVE_MATCH_SYSTEM_GUIDE.md`
2. Explore API endpoints
3. Customize commentary templates
4. Add custom achievements
5. Build your frontend UI

---

## 🎉 That's It!

You now have a fully functional live cricket match simulation system with:
- ✅ Ball-by-ball simulation
- ✅ AI commentary
- ✅ Live scoreboard
- ✅ AI predictions
- ✅ Gamification
- ✅ Real-time updates

**Happy Simulating! 🏏**
