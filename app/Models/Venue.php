<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $primaryKey = 'venue_id';

    protected $fillable = [
        'name',
        'address',
        'city',
        'capacity',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'venue_id', 'venue_id');
    }
}
