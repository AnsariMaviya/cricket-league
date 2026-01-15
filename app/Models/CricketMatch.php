<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CricketMatch extends Model
{
    protected $table = 'matches';
    protected $primaryKey = 'match_id';

    protected $fillable = [
        'venue_id',
        'first_team_id',
        'second_team_id',
        'match_type',
        'overs',
        'first_team_score',
        'second_team_score',
        'outcome',
        'match_date',
        'status',
    ];

    protected $casts = [
        'match_date' => 'date',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'venue_id');
    }

    public function firstTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'first_team_id', 'team_id');
    }

    public function secondTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'second_team_id', 'team_id');
    }
}
