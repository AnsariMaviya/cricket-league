<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerCareerStats extends Model
{
    protected $primaryKey = 'career_stat_id';
    protected $table = 'player_career_stats';
    
    protected $fillable = [
        'player_id',
        'total_matches',
        'total_innings_batted',
        'total_runs',
        'total_balls_faced',
        'total_fours',
        'total_sixes',
        'highest_score',
        'batting_average',
        'batting_strike_rate',
        'fifties',
        'centuries',
        'ducks',
        'not_outs',
        'total_innings_bowled',
        'total_wickets',
        'total_balls_bowled',
        'total_runs_conceded',
        'total_maidens',
        'bowling_average',
        'bowling_economy',
        'bowling_strike_rate',
        'best_bowling_figures',
        'five_wicket_hauls',
        'ten_wicket_hauls',
        'total_catches',
        'total_run_outs',
        'total_stumpings',
    ];
    
    protected $casts = [
        'batting_average' => 'decimal:2',
        'batting_strike_rate' => 'decimal:2',
        'bowling_average' => 'decimal:2',
        'bowling_economy' => 'decimal:2',
        'bowling_strike_rate' => 'decimal:2',
    ];
    
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }
}
