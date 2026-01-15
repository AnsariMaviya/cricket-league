<?php

namespace App\Services;

use App\Models\Player;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\PlayerCollection;
use Illuminate\Support\Facades\Cache;

class PlayerService
{
    public function getAllPlayers($filters = [])
    {
        $cacheKey = 'players_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Player::with(['team.country']);
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('role', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('batting_style', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('bowling_style', 'LIKE', "%{$filters['search']}%");
                });
            }
            
            if (isset($filters['team_id'])) {
                $query->where('team_id', $filters['team_id']);
            }
            
            if (isset($filters['role'])) {
                $query->where('role', $filters['role']);
            }
            
            $players = $query->paginate($filters['per_page'] ?? 15);
            
            return new PlayerCollection($players);
        });
    }
    
    public function getPlayerById($id)
    {
        $cacheKey = "player_{$id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            $player = Player::with(['team.country'])->findOrFail($id);
            return new PlayerResource($player);
        });
    }
    
    public function createPlayer(array $data)
    {
        $player = Player::create($data);
        $this->clearCache();
        return new PlayerResource($player);
    }
    
    public function updatePlayer($id, array $data)
    {
        $player = Player::findOrFail($id);
        $player->update($data);
        $this->clearCache();
        return new PlayerResource($player);
    }
    
    public function deletePlayer($id)
    {
        $player = Player::findOrFail($id);
        $player->delete();
        $this->clearCache();
        return true;
    }
    
    private function clearCache()
    {
        Cache::forget('players_');
        Cache::forget('dashboard_stats');
        Cache::forget('analytics_dashboard');
    }
}
