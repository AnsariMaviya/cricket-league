<?php

namespace App\Services;

use App\Models\Venue;
use App\Http\Resources\VenueResource;
use App\Http\Resources\VenueCollection;
use Illuminate\Support\Facades\Cache;

class VenueService
{
    public function getAllVenues($filters = [])
    {
        $cacheKey = 'venues_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Venue::withCount('matches');
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('address', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('city', 'LIKE', "%{$filters['search']}%");
                });
            }
            
            if (isset($filters['city'])) {
                $query->where('city', $filters['city']);
            }
            
            $venues = $query->paginate($filters['per_page'] ?? 15);
            
            return new VenueCollection($venues);
        });
    }
    
    public function getVenueById($id)
    {
        $cacheKey = "venue_{$id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            $venue = Venue::withCount('matches')->findOrFail($id);
            return new VenueResource($venue);
        });
    }
    
    public function createVenue(array $data)
    {
        $venue = Venue::create($data);
        $this->clearCache();
        return new VenueResource($venue);
    }
    
    public function updateVenue($id, array $data)
    {
        $venue = Venue::findOrFail($id);
        $venue->update($data);
        $this->clearCache();
        return new VenueResource($venue);
    }
    
    public function deleteVenue($id)
    {
        $venue = Venue::findOrFail($id);
        $venue->delete();
        $this->clearCache();
        return true;
    }
    
    private function clearCache()
    {
        Cache::forget('venues_');
        Cache::forget('dashboard_stats');
        Cache::forget('analytics_dashboard');
    }
}
