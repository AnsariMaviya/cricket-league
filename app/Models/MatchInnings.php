<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchInnings extends Model
{
    protected $table = 'match_innings';
    protected $primaryKey = 'innings_id';

    protected $fillable = [
        'match_id',
        'batting_team_id',
        'bowling_team_id',
        'innings_number',
        'total_runs',
        'wickets',
        'overs',
        'extras',
        'status',
    ];

    protected $casts = [
        'overs' => 'decimal:1',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function battingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'batting_team_id', 'team_id');
    }

    public function bowlingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'bowling_team_id', 'team_id');
    }

    public function balls(): HasMany
    {
        return $this->hasMany(BallByBall::class, 'innings_id', 'innings_id');
    }

    public function getRunRateAttribute()
    {
        if ($this->overs == 0) return 0;
        return round($this->total_runs / $this->overs, 2);
    }
}
