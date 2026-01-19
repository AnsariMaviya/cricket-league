<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tournament extends Model
{
    protected $primaryKey = 'tournament_id';
    
    protected $fillable = [
        'name',
        'tournament_type',
        'format',
        'start_date',
        'end_date',
        'status',
        'max_teams',
        'current_teams',
        'prize_pool',
        'description',
        'logo_url',
        'rules',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'prize_pool' => 'decimal:2',
        'rules' => 'array',
    ];
    
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'tournament_teams', 'tournament_id', 'team_id')
            ->withPivot(['group_name', 'points', 'matches_played', 'wins', 'losses', 'ties', 'net_run_rate', 'position', 'qualified'])
            ->withTimestamps();
    }
    
    public function tournamentTeams(): HasMany
    {
        return $this->hasMany(TournamentTeam::class, 'tournament_id', 'tournament_id');
    }
    
    public function stages(): HasMany
    {
        return $this->hasMany(TournamentStage::class, 'tournament_id', 'tournament_id')
            ->orderBy('stage_order');
    }
    
    public function matches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'tournament_id', 'tournament_id');
    }
}
