<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentTeam extends Model
{
    protected $primaryKey = 'tournament_team_id';
    
    protected $fillable = [
        'tournament_id',
        'team_id',
        'group_name',
        'points',
        'matches_played',
        'wins',
        'losses',
        'ties',
        'no_results',
        'net_run_rate',
        'runs_scored',
        'runs_conceded',
        'overs_faced',
        'overs_bowled',
        'position',
        'qualified',
    ];
    
    protected $casts = [
        'net_run_rate' => 'decimal:3',
        'overs_faced' => 'decimal:1',
        'overs_bowled' => 'decimal:1',
        'qualified' => 'boolean',
    ];
    
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'tournament_id');
    }
    
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }
}
