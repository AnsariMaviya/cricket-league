<?php

namespace App\Services;

use App\Models\Country;
use App\Http\Resources\CountryResource;
use App\Http\Resources\CountryCollection;
use Illuminate\Support\Facades\Cache;

class CountryService
{
    public function getAllCountries($filters = [])
    {
        $cacheKey = 'countries_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Country::withCount('teams');
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'LIKE', "%{$filters['search']}%")
                      ->orWhere('short_name', 'LIKE', "%{$filters['search']}%");
                });
            }
            
            $countries = $query->paginate($filters['per_page'] ?? 15);
            
            return new CountryCollection($countries);
        });
    }
    
    public function getCountryById($id)
    {
        $cacheKey = "country_{$id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            $country = Country::withCount('teams')->findOrFail($id);
            return new CountryResource($country);
        });
    }
    
    public function createCountry(array $data)
    {
        $country = Country::create($data);
        $this->clearCache();
        return new CountryResource($country);
    }
    
    public function updateCountry($id, array $data)
    {
        $country = Country::findOrFail($id);
        $country->update($data);
        $this->clearCache();
        return new CountryResource($country);
    }
    
    public function deleteCountry($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();
        $this->clearCache();
        return true;
    }
    
    private function clearCache()
    {
        Cache::forget('countries_');
        Cache::forget('dashboard_stats');
        Cache::forget('analytics_dashboard');
    }
}
