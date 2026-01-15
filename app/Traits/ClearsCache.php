<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearsCache
{
    protected static function bootClearsCache()
    {
        static::created(function () {
            self::clearRelatedCaches();
        });

        static::updated(function () {
            self::clearRelatedCaches();
        });

        static::deleted(function () {
            self::clearRelatedCaches();
        });
    }

    protected static function clearRelatedCaches()
    {
        $cacheKeys = [
            'dashboard_stats',
            'recent_matches',
            'upcoming_matches',
            'analytics_dashboard',
            'api_dashboard_stats',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
