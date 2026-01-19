<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPrediction extends Model
{
    protected $primaryKey = 'prediction_id';

    protected $fillable = [
        'match_id',
        'predicted_winner_id',
        'predicted_margin',
        'margin_type',
        'confidence_score',
        'factors',
        'is_ai_generated',
    ];

    protected $casts = [
        'factors' => 'array',
        'is_ai_generated' => 'boolean',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function predictedWinner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'predicted_winner_id', 'team_id');
    }
}
