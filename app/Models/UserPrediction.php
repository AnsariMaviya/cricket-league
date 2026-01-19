<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrediction extends Model
{
    protected $fillable = [
        'user_id',
        'match_id',
        'predicted_winner_id',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function predictedWinner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'predicted_winner_id', 'team_id');
    }
}
