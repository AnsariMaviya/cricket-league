# Live Match Simulation & Gamification System - Complete Guide

## 🏏 Overview

This comprehensive cricket league system includes:
- **Live Match Simulation Engine** - Ball-by-ball cricket match simulation with AI commentary
- **Real-time Scoreboard** - Live updates with WebSocket broadcasting
- **AI-Powered Predictions** - Machine learning-based match outcome predictions
- **Gamification System** - Points, achievements, leaderboards, and user engagement
- **Player Analytics** - Performance tracking and analysis
- **Team Management** - AI-powered team recommendations

---

## 📋 Table of Contents

1. [Installation & Setup](#installation--setup)
2. [Database Structure](#database-structure)
3. [Live Match Simulation](#live-match-simulation)
4. [AI Predictions](#ai-predictions)
5. [Gamification Features](#gamification-features)
6. [API Endpoints](#api-endpoints)
7. [Frontend Integration](#frontend-integration)
8. [Real-time Broadcasting](#real-time-broadcasting)
9. [External Live Score APIs](#external-live-score-apis)

---

## 🚀 Installation & Setup

### Step 1: Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- `match_innings` - Innings tracking
- `ball_by_ball` - Ball-by-ball match data
- `player_match_stats` - Player statistics per match
- `match_commentary` - AI-generated commentary
- `achievements` - Gamification achievements
- `user_points` - User points and levels
- `match_predictions` - AI predictions
- `user_predictions` - User predictions

### Step 2: Initialize Achievements

```bash
php artisan tinker
```

```php
$service = new \App\Services\GamificationService();
$service->initializeAchievements();
```

Or via API:
```bash
POST /api/v1/gamification/achievements/initialize
```

### Step 3: Configure Broadcasting (Optional)

Update `.env` for real-time features:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=your_cluster
```

---

## 🗄️ Database Structure

### Core Tables

**match_innings**
- Tracks each innings of a match
- Stores runs, wickets, overs, extras
- Links to batting and bowling teams

**ball_by_ball**
- Complete ball-by-ball data
- Runs, wickets, boundaries
- AI-generated commentary for each ball
- Links batsman, bowler, fielder

**player_match_stats**
- Batting: runs, balls faced, strike rate, boundaries
- Bowling: wickets, overs, economy, maidens
- Fielding: catches, stumpings, run-outs

**match_commentary**
- Different types: ball, milestone, wicket, boundary, over_summary
- Timestamped commentary feed
- Links to specific balls

### Gamification Tables

**achievements**
- Name, description, icon, points, rarity
- Criteria stored as JSON
- Categories: points, predictions, matches, level

**user_points**
- Total points, level, matches watched
- Prediction statistics
- Auto-calculated level progression

**point_transactions**
- Complete audit trail of all point awards
- Links to reference objects (matches, predictions)

---

## 🎮 Live Match Simulation

### Method 1: Manual Ball-by-Ball Simulation

```php
use App\Services\MatchSimulationEngine;
use App\Models\CricketMatch;

$engine = new MatchSimulationEngine();
$match = CricketMatch::find(1);

// Start match
$engine->startMatch($match);

// Simulate each ball manually
while ($match->fresh()->status === 'live') {
    $ball = $engine->simulateBall();
    
    // Ball data includes:
    // - runs_scored
    // - is_wicket, wicket_type
    // - is_four, is_six
    // - commentary (AI-generated)
    
    sleep(3); // 3 second delay between balls
}
```

### Method 2: Auto-Simulation

```php
$engine = new MatchSimulationEngine();
$match = CricketMatch::find(1);

// Fully automatic simulation with 3-second delays
$result = $engine->autoSimulate($match, delaySeconds: 3);
```

### Method 3: Command Line

```bash
php artisan match:simulate 1 --delay=3
```

This provides a beautiful CLI experience with:
- Progress bar
- Live commentary output
- Final match summary

### Method 4: API Endpoints

**Start a match:**
```bash
POST /api/v1/live-matches/{matchId}/start
```

**Simulate next ball:**
```bash
POST /api/v1/live-matches/{matchId}/simulate-ball
```

**Auto-simulate entire match:**
```bash
POST /api/v1/live-matches/{matchId}/auto-simulate
{
  "delay": 3
}
```

### Match Simulation Features

✅ **Realistic Cricket Logic**
- Toss simulation (bat/bowl decision)
- Player selection (batsmen, bowlers, all-rounders)
- Wicket types: bowled, caught, lbw, run out, stumped, caught & bowled
- Extras: wides, no-balls, byes, leg-byes
- Over completion with bowler rotation
- Innings breaks with target calculation
- Match completion logic

✅ **AI Commentary Generator**
- Contextual commentary for every ball
- Different templates for:
  - Dot balls
  - Singles, doubles, triples
  - Boundaries (4s)
  - Sixes
  - Wickets (by type)
  - Milestones (50s, 100s)
  - Over summaries

✅ **Real-time Updates**
- WebSocket broadcasting on every ball
- Live scoreboard updates
- Match status changes
- Innings transitions

---

## 🤖 AI Predictions

### Generate Match Prediction

```php
use App\Services\AIMatchPredictionService;

$service = new AIMatchPredictionService();
$match = CricketMatch::find(1);

$prediction = $service->predictMatch($match);

// Returns:
// - predicted_winner_id
// - confidence_score (0-100)
// - factors: team analysis, venue advantage, form, head-to-head
```

### API Endpoint

```bash
GET /api/v1/predictions/match/{matchId}
```

Response:
```json
{
  "prediction_id": 1,
  "match_id": 1,
  "predicted_winner_id": 5,
  "confidence_score": 75,
  "factors": {
    "team1_analysis": {
      "batting_strength": 8.5,
      "bowling_strength": 7.2,
      "consistency": 6.8,
      "overall_rating": 7.5
    },
    "team2_analysis": {...},
    "venue_advantage": {"team1": 1.2, "team2": 1.0},
    "recent_form": {"team1": 4, "team2": 2},
    "head_to_head": {"team1": 6, "team2": 4, "total_matches": 10},
    "team1_probability": 75.23,
    "team2_probability": 24.77
  }
}
```

### Player Performance Analysis

```bash
GET /api/v1/predictions/player/{playerId}/analysis
```

Returns:
- Average runs, strike rate
- Average wickets, economy
- Performance trend (improving/declining/stable)
- Consistency score
- Last N matches analyzed

### Team Recommendation

```bash
GET /api/v1/predictions/team/{teamId}/recommend?match_type=T20
```

AI-powered best XI selection based on:
- Recent form (last 3 months)
- Role-based selection
- Balanced team composition

---

## 🏆 Gamification Features

### Points System

Users earn points for:
- **Watching matches**: 10 points
- **Making predictions**: 5 points
- **Correct predictions**: 50 points
- **Unlocking achievements**: Variable points

### Level Progression

- Level = floor(total_points / 1000) + 1
- Level 1: 0-999 points
- Level 2: 1000-1999 points
- Level 3: 2000-2999 points
- etc.

### Achievements

Pre-configured achievements:
- **First Step**: 100 points (50 pts reward)
- **Rising Star**: 1000 points (200 pts reward)
- **Cricket Legend**: 10000 points (1000 pts reward)
- **Prediction Master**: 10 correct predictions (300 pts)
- **Oracle**: 80% accuracy with 20+ predictions (500 pts)
- **Cricket Fan**: Watch 10 matches (100 pts)
- **Super Fan**: Watch 50 matches (400 pts)
- **Level Milestones**: Level 5, 10, etc.

### Leaderboard

```bash
GET /api/v1/gamification/leaderboard?timeframe=week&limit=10
```

Timeframes: `all`, `week`, `month`, `year`

### User Stats

```bash
GET /api/v1/gamification/stats
```

Returns:
- Total points and level
- Global rank
- Points to next level
- Prediction accuracy
- Matches watched
- Unlocked achievements

---

## 🔌 API Endpoints

### Live Match Endpoints

```
GET    /api/v1/live-matches                      - Get all live matches
GET    /api/v1/live-matches/upcoming             - Get upcoming matches
GET    /api/v1/live-matches/{id}/scoreboard      - Full scoreboard
GET    /api/v1/live-matches/{id}/mini-scoreboard - Mini scoreboard
GET    /api/v1/live-matches/{id}/summary         - Match summary
GET    /api/v1/live-matches/{id}/over/{num}      - Over summary
POST   /api/v1/live-matches/{id}/start           - Start match
POST   /api/v1/live-matches/{id}/simulate-ball   - Simulate next ball
POST   /api/v1/live-matches/{id}/auto-simulate   - Auto-simulate match
POST   /api/v1/live-matches/{id}/stop            - Stop match
```

### Prediction Endpoints

```
GET    /api/v1/predictions/match/{id}            - AI prediction
GET    /api/v1/predictions/match/{id}/user       - User's prediction
POST   /api/v1/predictions/match/{id}            - Submit prediction
GET    /api/v1/predictions/player/{id}/analysis  - Player analysis
GET    /api/v1/predictions/team/{id}/recommend   - Team recommendation
```

### Gamification Endpoints (Auth Required)

```
GET    /api/v1/gamification/stats                - User stats
GET    /api/v1/gamification/leaderboard          - Leaderboard
GET    /api/v1/gamification/achievements         - All achievements
GET    /api/v1/gamification/transactions         - Point history
POST   /api/v1/gamification/achievements/init    - Initialize achievements
```

---

## 🎨 Frontend Integration

### Live Scoreboard Component (Example)

```javascript
// Fetch live scoreboard
async function getScoreboard(matchId) {
  const response = await fetch(`/api/v1/live-matches/${matchId}/scoreboard`);
  const data = await response.json();
  
  return {
    match: data.match,
    currentInnings: data.current_innings,
    currentBatsmen: data.current_batsmen,
    currentBowler: data.current_bowler,
    recentBalls: data.recent_balls,
    commentary: data.commentary,
    battingStats: data.batting_stats,
    bowlingStats: data.bowling_stats,
    runRate: data.run_rate,
    requiredRunRate: data.required_run_rate
  };
}

// Display scoreboard
function displayScoreboard(data) {
  // Team scores
  document.getElementById('team1-score').textContent = 
    `${data.match.first_team.team_name}: ${data.match.first_team_score}`;
  document.getElementById('team2-score').textContent = 
    `${data.match.second_team.team_name}: ${data.match.second_team_score}`;
  
  // Current batsmen
  data.currentBatsmen.forEach((batsman, index) => {
    document.getElementById(`batsman-${index}`).textContent = 
      `${batsman.player.name}: ${batsman.runs_scored} (${batsman.balls_faced})`;
  });
  
  // Commentary feed
  data.commentary.forEach(comment => {
    addCommentaryLine(comment.commentary_text, comment.over_number);
  });
}
```

### Real-time Updates with WebSockets

```javascript
// Using Laravel Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY,
  cluster: process.env.MIX_PUSHER_APP_CLUSTER,
  forceTLS: true
});

// Listen for ball updates
Echo.channel(`match.${matchId}`)
  .listen('.ball.simulated', (e) => {
    console.log('New ball:', e);
    updateScoreboard(matchId);
    addCommentary(e.commentary);
    
    if (e.is_wicket) {
      showWicketAnimation();
    } else if (e.is_six) {
      showSixAnimation();
    } else if (e.is_four) {
      showFourAnimation();
    }
  });

// Listen for match updates
Echo.channel(`match.${matchId}`)
  .listen('.match.updated', (e) => {
    console.log('Match updated:', e);
    refreshFullScoreboard();
  });
```

### Prediction Component

```javascript
async function submitPrediction(matchId, predictedWinnerId) {
  const response = await fetch(`/api/v1/predictions/match/${matchId}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ predicted_winner_id: predictedWinnerId })
  });
  
  const result = await response.json();
  if (result.success) {
    showNotification('Prediction submitted! +5 points earned');
  }
}

async function showAIPrediction(matchId) {
  const response = await fetch(`/api/v1/predictions/match/${matchId}`);
  const prediction = await response.json();
  
  displayPrediction({
    winner: prediction.predicted_winner_id,
    confidence: prediction.confidence_score,
    factors: prediction.factors
  });
}
```

---

## 📡 Real-time Broadcasting

### Event Broadcasting

The system broadcasts these events:

**MatchUpdated**
- Triggered on: match start, innings change, match end
- Channel: `matches`, `match.{id}`, `team.{id}`
- Data: Complete match data

**BallSimulated** (Optional)
- Triggered on: every ball
- Channel: `match.{id}`
- Data: Ball details with commentary

### Configure Broadcasting

1. Install Pusher PHP SDK (already in composer.json):
```bash
composer require pusher/pusher-php-server
```

2. Update `.env`:
```env
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=redis
```

3. Frontend setup:
```bash
npm install --save-dev laravel-echo pusher-js
```

---

## 🌐 External Live Score APIs

### Option 1: CricAPI (cricapi.com)

```php
namespace App\Services;

class ExternalScoreService
{
    protected $apiKey;
    
    public function fetchLiveScore($matchId)
    {
        $url = "https://api.cricapi.com/v1/match_info?apikey={$this->apiKey}&id={$matchId}";
        $response = Http::get($url);
        
        return $response->json();
    }
    
    public function syncToOurSystem($externalData)
    {
        // Map external API data to our match structure
        $match = CricketMatch::updateOrCreate(
            ['external_id' => $externalData['id']],
            [
                'first_team_score' => $externalData['score'][0] ?? null,
                'second_team_score' => $externalData['score'][1] ?? null,
                'status' => $this->mapStatus($externalData['matchStarted']),
                // ... more mappings
            ]
        );
        
        return $match;
    }
}
```

### Option 2: Entity Sport API

```php
public function fetchFromEntitySport($matchId)
{
    $url = "https://rest.entitysport.com/v2/matches/{$matchId}/live";
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$this->apiToken}"
    ])->get($url);
    
    return $response->json();
}
```

### Option 3: RapidAPI Cricket Live

```php
public function fetchFromRapidAPI($matchId)
{
    $url = "https://cricbuzz-cricket.p.rapidapi.com/mcenter/v1/{$matchId}";
    $response = Http::withHeaders([
        'X-RapidAPI-Key' => $this->rapidApiKey,
        'X-RapidAPI-Host' => 'cricbuzz-cricket.p.rapidapi.com'
    ])->get($url);
    
    return $response->json();
}
```

### Scheduled Sync Job

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('cricket:sync-live-scores')
        ->everyMinute()
        ->when(function () {
            return CricketMatch::where('status', 'live')->exists();
        });
}
```

---

## 🎯 Use Cases

### 1. Fantasy Cricket League
- Users create teams from available players
- Earn points based on real match simulation
- Compete in leagues with friends

### 2. Cricket Prediction Game
- AI generates predictions for upcoming matches
- Users submit their predictions before match starts
- Earn points for correct predictions
- Climb the leaderboard

### 3. Cricket Education Platform
- Simulate historical matches
- Teach cricket rules and strategies
- Interactive learning experience

### 4. Tournament Management
- Create and manage tournaments
- Automatic match scheduling
- Live scoring during matches
- Final standings and statistics

---

## 🔧 Advanced Configuration

### Customize Ball Outcome Probabilities

Edit `MatchSimulationEngine::generateBallOutcome()`:

```php
protected function generateBallOutcome()
{
    $rand = rand(1, 100);
    
    // Adjust these probabilities as needed
    if ($rand <= 5) return ['is_wicket' => true]; // 5% wicket
    if ($rand <= 10) return ['runs' => 6, 'is_six' => true]; // 5% six
    if ($rand <= 20) return ['runs' => 4, 'is_four' => true]; // 10% four
    // ... etc
}
```

### Custom Commentary Templates

Edit `CommentaryGenerator` class to add your own commentary styles.

### Achievement Customization

Create custom achievements by modifying `GamificationService::initializeAchievements()`.

---

## 📊 Performance Optimization

### Caching Strategy

Scoreboards are cached for 10 seconds:
```php
Cache::remember("match_scoreboard_{$matchId}", 10, function () {
    // ... fetch data
});
```

### Database Indexing

All performance indexes are included in the migrations:
- `matches`: venue_id, team_ids, status, match_date
- `ball_by_ball`: match_id, innings_id, over_number
- `player_match_stats`: player_id, match_id

### Queue Processing

For auto-simulation, consider using queues:

```php
dispatch(new SimulateMatchJob($matchId))->onQueue('match-simulation');
```

---

## 🐛 Troubleshooting

### Match Won't Start
- Check match status is 'scheduled'
- Ensure teams have players with roles
- Verify venue exists

### No Commentary Generated
- Check `CommentaryGenerator` service is initialized
- Verify ball data is saved correctly

### WebSocket Not Broadcasting
- Confirm Pusher credentials in `.env`
- Check queue worker is running: `php artisan queue:work`
- Verify event implements `ShouldBroadcast`

---

## 📝 Summary

This system provides a **complete cricket league management solution** with:

✅ **Realistic match simulation** with AI commentary
✅ **Live scoring and real-time updates**
✅ **AI-powered predictions and analytics**
✅ **Gamification with achievements and leaderboards**
✅ **Comprehensive API** for frontend integration
✅ **WebSocket support** for real-time features
✅ **Extensible architecture** for external API integration

Perfect for building fantasy leagues, prediction games, tournament management systems, and more!

---

## 🤝 Contributing

To extend this system:
1. Add new achievement types in `GamificationService`
2. Enhance AI prediction algorithm in `AIMatchPredictionService`
3. Add more commentary variations in `CommentaryGenerator`
4. Integrate additional external APIs in `ExternalScoreService`
5. Create custom frontend components for your needs

---

**Happy Coding! 🏏**
