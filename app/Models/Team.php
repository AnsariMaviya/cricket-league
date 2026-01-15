<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $primaryKey = 'team_id';

    protected $fillable = [
        'team_name',
        'country_id',
        'in_match',
        'logo',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'team_id', 'team_id');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'first_team_id', 'team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'second_team_id', 'team_id');
    }
}
