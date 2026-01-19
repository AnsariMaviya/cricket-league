# External Live Score API Integration Guide

## Overview

This guide shows how to integrate real-time cricket scores from external APIs into your system.

---

## 🌐 Available APIs

### 1. CricAPI (Recommended)
- **Website**: https://www.cricapi.com/
- **Free Tier**: 100 requests/day
- **Paid Plans**: From $10/month
- **Features**: Live scores, match info, player stats

### 2. Entity Sport API
- **Website**: https://www.entitysport.com/
- **Free Trial**: Available
- **Features**: Comprehensive cricket data

### 3. CricBuzz (via RapidAPI)
- **Website**: https://rapidapi.com/
- **Free Tier**: Limited requests
- **Features**: Live scores, commentary

### 4. SportMonks Cricket API
- **Website**: https://www.sportmonks.com/cricket-api/
- **Pricing**: Pay-as-you-go
- **Features**: International and domestic cricket

---

## 🔧 Implementation

### Step 1: Create External Score Service

```bash
php artisan make:service ExternalScoreService
```

`app/Services/ExternalScoreService.php`:

```php
<?php

namespace App\Services;

use App\Models\CricketMatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExternalScoreService
{
    protected $apiKey;
    protected $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.cricapi.key');
        $this->baseUrl = 'https://api.cricapi.com/v1';
    }
    
    public function fetchLiveMatches()
    {
        $cacheKey = 'external_live_matches';
        
        return Cache::remember($cacheKey, 60, function () {
            $response = Http::get("{$this->baseUrl}/currentMatches", [
                'apikey' => $this->apiKey,
                'offset' => 0
            ]);
            
            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
            
            return [];
        });
    }
    
    public function fetchMatchInfo($externalMatchId)
    {
        $response = Http::get("{$this->baseUrl}/match_info", [
            'apikey' => $this->apiKey,
            'id' => $externalMatchId
        ]);
        
        if ($response->successful()) {
            return $response->json()['data'] ?? null;
        }
        
        return null;
    }
    
    public function syncMatchToDatabase($externalData)
    {
        $match = CricketMatch::updateOrCreate(
            ['external_match_id' => $externalData['id']],
            [
                'match_type' => $this->mapMatchType($externalData['matchType']),
                'status' => $this->mapStatus($externalData['status']),
                'first_team_score' => $this->formatScore($externalData['score'][0] ?? null),
                'second_team_score' => $this->formatScore($externalData['score'][1] ?? null),
                'match_date' => $externalData['dateTimeGMT'] ?? now(),
                'venue_id' => $this->findOrCreateVenue($externalData['venue']),
                'first_team_id' => $this->findOrCreateTeam($externalData['teams'][0]),
                'second_team_id' => $this->findOrCreateTeam($externalData['teams'][1]),
            ]
        );
        
        return $match;
    }
    
    protected function mapMatchType($type)
    {
        $mapping = [
            't20' => 'T20',
            'odi' => 'ODI',
            'test' => 'Test',
        ];
        
        return $mapping[strtolower($type)] ?? 'T20';
    }
    
    protected function mapStatus($status)
    {
        $mapping = [
            'Match not started' => 'scheduled',
            'Live' => 'live',
            'Match ended' => 'completed',
            'Match abandoned' => 'cancelled',
        ];
        
        return $mapping[$status] ?? 'scheduled';
    }
    
    protected function formatScore($scoreData)
    {
        if (!$scoreData) return null;
        
        return "{$scoreData['r']}/{$scoreData['w']} ({$scoreData['o']} ov)";
    }
    
    protected function findOrCreateVenue($venueName)
    {
        if (!$venueName) return null;
        
        $venue = \App\Models\Venue::firstOrCreate(
            ['name' => $venueName],
            ['city' => 'Unknown']
        );
        
        return $venue->venue_id;
    }
    
    protected function findOrCreateTeam($teamName)
    {
        if (!$teamName) return null;
        
        $team = \App\Models\Team::firstOrCreate(
            ['team_name' => $teamName],
            ['country_id' => 1] // Default country
        );
        
        return $team->team_id;
    }
    
    public function syncAllLiveMatches()
    {
        $liveMatches = $this->fetchLiveMatches();
        $synced = [];
        
        foreach ($liveMatches as $matchData) {
            $match = $this->syncMatchToDatabase($matchData);
            $synced[] = $match;
        }
        
        return $synced;
    }
}
```

### Step 2: Add Configuration

Add to `config/services.php`:

```php
'cricapi' => [
    'key' => env('CRICAPI_KEY'),
],

'entitysport' => [
    'token' => env('ENTITYSPORT_TOKEN'),
],

'rapidapi' => [
    'key' => env('RAPIDAPI_KEY'),
],
```

Add to `.env`:

```env
CRICAPI_KEY=your_cricapi_key
ENTITYSPORT_TOKEN=your_entity_token
RAPIDAPI_KEY=your_rapidapi_key
```

### Step 3: Add Migration for External ID

```bash
php artisan make:migration add_external_match_id_to_matches_table
```

```php
public function up()
{
    Schema::table('matches', function (Blueprint $table) {
        $table->string('external_match_id')->nullable()->after('match_id');
        $table->string('external_source')->nullable()->after('external_match_id');
        $table->index('external_match_id');
    });
}
```

Run migration:
```bash
php artisan migrate
```

### Step 4: Create Artisan Command

```bash
php artisan make:command SyncExternalScores
```

`app/Console/Commands/SyncExternalScores.php`:

```php
<?php

namespace App\Console\Commands;

use App\Services\ExternalScoreService;
use Illuminate\Console\Command;

class SyncExternalScores extends Command
{
    protected $signature = 'cricket:sync-external-scores';
    protected $description = 'Sync live scores from external API';

    public function handle()
    {
        $service = new ExternalScoreService();
        
        $this->info('Fetching live matches from external API...');
        
        $matches = $service->syncAllLiveMatches();
        
        $this->info('Synced ' . count($matches) . ' matches');
        
        foreach ($matches as $match) {
            $this->line("- {$match->firstTeam->team_name} vs {$match->secondTeam->team_name}");
        }
        
        return 0;
    }
}
```

### Step 5: Schedule Automatic Sync

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync every minute during live matches
    $schedule->command('cricket:sync-external-scores')
        ->everyMinute()
        ->when(function () {
            return \App\Models\CricketMatch::where('status', 'live')
                ->whereNotNull('external_match_id')
                ->exists();
        });
}
```

### Step 6: Create API Endpoint

Add to `routes/api.php`:

```php
Route::prefix('v1/external')->group(function () {
    Route::post('/sync-live-matches', [ExternalScoreController::class, 'syncLiveMatches']);
    Route::post('/sync-match/{externalId}', [ExternalScoreController::class, 'syncMatch']);
    Route::get('/available-matches', [ExternalScoreController::class, 'getAvailableMatches']);
});
```

Create controller:

```php
<?php

namespace App\Http\Controllers;

use App\Services\ExternalScoreService;
use Illuminate\Http\Request;

class ExternalScoreController extends Controller
{
    protected $externalScoreService;
    
    public function __construct(ExternalScoreService $externalScoreService)
    {
        $this->externalScoreService = $externalScoreService;
    }
    
    public function syncLiveMatches()
    {
        $matches = $this->externalScoreService->syncAllLiveMatches();
        
        return response()->json([
            'success' => true,
            'matches_synced' => count($matches),
            'matches' => $matches
        ]);
    }
    
    public function syncMatch($externalId)
    {
        $matchData = $this->externalScoreService->fetchMatchInfo($externalId);
        
        if (!$matchData) {
            return response()->json(['error' => 'Match not found'], 404);
        }
        
        $match = $this->externalScoreService->syncMatchToDatabase($matchData);
        
        return response()->json([
            'success' => true,
            'match' => $match
        ]);
    }
    
    public function getAvailableMatches()
    {
        $matches = $this->externalScoreService->fetchLiveMatches();
        
        return response()->json($matches);
    }
}
```

---

## 📡 Alternative APIs

### Entity Sport API Example

```php
public function fetchFromEntitySport($matchId)
{
    $token = config('services.entitysport.token');
    
    $response = Http::get("https://rest.entitysport.com/v2/matches/{$matchId}/live", [
        'token' => $token
    ]);
    
    if ($response->successful()) {
        $data = $response->json()['response'];
        return [
            'score' => $data['score'] ?? null,
            'status' => $data['status_str'] ?? null,
            'teams' => [
                $data['teama']['name'] ?? null,
                $data['teamb']['name'] ?? null
            ]
        ];
    }
    
    return null;
}
```

### RapidAPI CricBuzz Example

```php
public function fetchFromRapidAPI($matchId)
{
    $response = Http::withHeaders([
        'X-RapidAPI-Key' => config('services.rapidapi.key'),
        'X-RapidAPI-Host' => 'cricbuzz-cricket.p.rapidapi.com'
    ])->get("https://cricbuzz-cricket.p.rapidapi.com/mcenter/v1/{$matchId}");
    
    if ($response->successful()) {
        return $response->json();
    }
    
    return null;
}
```

---

## 🔄 Hybrid Approach

Combine simulated and real data:

```php
public function getMatchData($matchId)
{
    $match = CricketMatch::find($matchId);
    
    // If it's a simulated match
    if (!$match->external_match_id) {
        return $this->getSimulatedData($match);
    }
    
    // If it's a real match, fetch from external API
    return $this->externalScoreService->fetchMatchInfo($match->external_match_id);
}
```

---

## 🎯 Best Practices

1. **Rate Limiting**: Cache API responses
2. **Error Handling**: Graceful fallbacks
3. **Data Validation**: Verify external data before saving
4. **Monitoring**: Log API failures
5. **Cost Management**: Track API usage

---

## 📊 Usage Example

```bash
# Manually sync all live matches
php artisan cricket:sync-external-scores

# Via API
curl -X POST http://localhost:8000/api/v1/external/sync-live-matches

# Get available matches
curl http://localhost:8000/api/v1/external/available-matches

# Sync specific match
curl -X POST http://localhost:8000/api/v1/external/sync-match/12345
```

---

## 🚀 Production Checklist

- [ ] Get API keys from providers
- [ ] Configure rate limits
- [ ] Set up caching strategy
- [ ] Add error logging
- [ ] Monitor API usage
- [ ] Set up webhooks (if available)
- [ ] Configure queue workers for background sync
- [ ] Add retry logic for failed requests

---

**Now you can integrate real live cricket scores alongside your simulated matches!** 🏏
