<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->dob ? $this->dob->age : null,
        );
    }
}
