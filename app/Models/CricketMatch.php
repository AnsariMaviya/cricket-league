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
        'tournament_id',
        'stage_id',
        'match_number',
        'is_knockout',
        'match_type',
        'overs',
        'first_team_score',
        'second_team_score',
        'outcome',
        'match_date',
        'status',
        'current_innings',
        'current_over',
        'current_batsman_1',
        'current_batsman_2',
        'current_bowler',
        'target_score',
        'toss_winner',
        'toss_decision',
        'match_summary',
        'viewers_count',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'match_date' => 'date',
        'current_over' => 'decimal:1',
        'is_knockout' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
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

    public function innings()
    {
        return $this->hasMany(MatchInnings::class, 'match_id', 'match_id');
    }

    public function balls()
    {
        return $this->hasMany(BallByBall::class, 'match_id', 'match_id');
    }

    public function playerStats()
    {
        return $this->hasMany(PlayerMatchStats::class, 'match_id', 'match_id');
    }

    public function commentary()
    {
        return $this->hasMany(MatchCommentary::class, 'match_id', 'match_id');
    }

    public function prediction()
    {
        return $this->hasOne(MatchPrediction::class, 'match_id', 'match_id');
    }

    public function userPredictions()
    {
        return $this->hasMany(UserPrediction::class, 'match_id', 'match_id');
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'tournament_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TournamentStage::class, 'stage_id', 'stage_id');
    }
}
