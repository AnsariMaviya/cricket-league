<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Player extends Model
{
    protected $primaryKey = 'player_id';

    protected $fillable = [
        'name',
        'team_id',
        'dob',
        'profile_image',
        'role',
        'batting_style',
        'bowling_style',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function matchStats(): HasMany
    {
        return $this->hasMany(PlayerMatchStats::class, 'player_id', 'player_id');
    }

    public function careerStats(): HasOne
    {
        return $this->hasOne(PlayerCareerStats::class, 'player_id', 'player_id');
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->dob ? $this->dob->age : null,
        );
    }
}
