<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerMatchStats extends Model
{
    protected $table = 'player_match_stats';
    protected $primaryKey = 'stat_id';

    protected $fillable = [
        'match_id',
        'player_id',
        'team_id',
        'runs_scored',
        'balls_faced',
        'fours',
        'sixes',
        'strike_rate',
        'wickets_taken',
        'overs_bowled',
        'balls_bowled',
        'runs_conceded',
        'maidens',
        'economy',
        'catches',
        'stumpings',
        'run_outs',
    ];

    protected $casts = [
        'strike_rate' => 'decimal:2',
        'economy' => 'decimal:2',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function calculateStrikeRate()
    {
        if ($this->balls_faced == 0) return 0;
        $this->strike_rate = round(($this->runs_scored / $this->balls_faced) * 100, 2);
        return $this->strike_rate;
    }

    public function calculateEconomy()
    {
        if ($this->overs_bowled == 0) return 0;
        $this->economy = round($this->runs_conceded / $this->overs_bowled, 2);
        return $this->economy;
    }

    public function recalculateOvers()
    {
        $this->overs_bowled = $this->balls_bowled / 6;
        return $this->overs_bowled;
    }
}
