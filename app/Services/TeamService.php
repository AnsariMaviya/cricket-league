<?php

namespace App\Services;

use App\Models\Team;
use App\Http\Resources\TeamResource;
use App\Http\Resources\TeamCollection;
use Illuminate\Support\Facades\Cache;

class TeamService
{
    public function getAllTeams($filters = [])
    {
        $cacheKey = 'teams_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Team::with(['country', 'players'])
                    ->withCount('players');
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('team_name', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('in_match', 'LIKE', "%{$filters['search']}%");
                });
            }
            
            if (isset($filters['country_id'])) {
                $query->where('country_id', $filters['country_id']);
            }
            
            $teams = $query->paginate($filters['per_page'] ?? 15);
            
            return new TeamCollection($teams);
        });
    }
    
    public function getTeamById($id)
    {
        $cacheKey = "team_{$id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            $team = Team::with(['country', 'players'])->findOrFail($id);
            return new TeamResource($team);
        });
    }
    
    public function createTeam(array $data)
    {
        $team = Team::create($data);
        $this->clearCache();
        return new TeamResource($team);
    }
    
    public function updateTeam($id, array $data)
    {
        $team = Team::findOrFail($id);
        $team->update($data);
        $this->clearCache();
        return new TeamResource($team);
    }
    
    public function deleteTeam($id)
    {
        $team = Team::findOrFail($id);
        $team->delete();
        $this->clearCache();
        return true;
    }
    
    private function clearCache()
    {
        Cache::forget('teams_');
        Cache::forget('dashboard_stats');
        Cache::forget('analytics_dashboard');
    }
}
