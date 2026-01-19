<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Http\Resources\MatchResource;
use App\Http\Resources\MatchCollection;
use Illuminate\Support\Facades\Cache;

class MatchService
{
    public function getAllMatches($filters = [])
    {
        $cacheKey = 'matches_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 1800, function () use ($filters) {
            // Optimize: Only load country if needed, remove nested eager loading
            $query = CricketMatch::with(['firstTeam', 'secondTeam', 'venue']);
            
            // Apply filters first to reduce dataset
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (isset($filters['venue_id'])) {
                $query->where('venue_id', $filters['venue_id']);
            }
            
            if (isset($filters['team_id'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('first_team_id', $filters['team_id'])
                      ->orWhere('second_team_id', $filters['team_id']);
                });
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('match_type', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('outcome', 'LIKE', "%{$filters['search']}%")
                      ->orWhereHas('firstTeam', function ($subQ) use ($filters) {
                          $subQ->where('team_name', 'LIKE', "%{$filters['search']}%");
                      })
                      ->orWhereHas('secondTeam', function ($subQ) use ($filters) {
                          $subQ->where('team_name', 'LIKE', "%{$filters['search']}%");
                      })
                      ->orWhereHas('venue', function ($subQ) use ($filters) {
                          $subQ->where('name', 'LIKE', "%{$filters['search']}%");
                      });
                });
            }
            
            if (isset($filters['match_type'])) {
                $query->where('match_type', $filters['match_type']);
            }
            
            if (isset($filters['date_from'])) {
                $query->whereDate('match_date', '>=', $filters['date_from']);
            }
            
            if (isset($filters['date_to'])) {
                $query->whereDate('match_date', '<=', $filters['date_to']);
            }
            
            $matches = $query->orderBy('match_date', 'desc')->paginate($filters['per_page'] ?? 15);
            
            return new MatchCollection($matches);
        });
    }
    
    public function getMatchById($id)
    {
        $cacheKey = "match_{$id}";
        
        return Cache::remember($cacheKey, 600, function () use ($id) {
            $match = CricketMatch::with([
                'firstTeam.country',
                'secondTeam.country',
                'venue',
                'firstTeam.players',
                'secondTeam.players'
            ])->findOrFail($id);
            return new MatchResource($match);
        });
    }
    
    public function createMatch(array $data)
    {
        $match = CricketMatch::create($data);
        $this->clearCache();
        return new MatchResource($match);
    }
    
    public function updateMatch($id, array $data)
    {
        $match = CricketMatch::findOrFail($id);
        $match->update($data);
        $this->clearCache();
        return new MatchResource($match);
    }
    
    public function deleteMatch($id)
    {
        $match = CricketMatch::findOrFail($id);
        $match->delete();
        $this->clearCache();
        return true;
    }
    
    private function clearCache()
    {
        // Clear all application cache since file/database drivers don't support selective clearing
        Cache::flush();
    }
}
