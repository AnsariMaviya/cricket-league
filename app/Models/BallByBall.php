<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BallByBall extends Model
{
    protected $table = 'ball_by_ball';
    protected $primaryKey = 'ball_id';

    protected $fillable = [
        'innings_id',
        'match_id',
        'batsman_id',
        'bowler_id',
        'over_number',
        'ball_number',
        'runs_scored',
        'is_wicket',
        'wicket_type',
        'fielder_id',
        'extra_type',
        'extra_runs',
        'is_four',
        'is_six',
        'commentary',
    ];

    protected $casts = [
        'is_wicket' => 'boolean',
        'is_four' => 'boolean',
        'is_six' => 'boolean',
    ];

    public function innings(): BelongsTo
    {
        return $this->belongsTo(MatchInnings::class, 'innings_id', 'innings_id');
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function batsman(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'batsman_id', 'player_id');
    }

    public function bowler(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'bowler_id', 'player_id');
    }

    public function fielder(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'fielder_id', 'player_id');
    }

    public function getTotalRunsAttribute()
    {
        return $this->runs_scored + $this->extra_runs;
    }
}
