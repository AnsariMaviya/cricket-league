<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentStage extends Model
{
    protected $primaryKey = 'stage_id';
    
    protected $fillable = [
        'tournament_id',
        'stage_name',
        'stage_order',
        'stage_format',
        'status',
        'teams_qualify',
    ];
    
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'tournament_id');
    }
    
    public function matches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'stage_id', 'stage_id');
    }
}
