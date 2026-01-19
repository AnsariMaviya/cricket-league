<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchCommentary extends Model
{
    protected $table = 'match_commentary';
    protected $primaryKey = 'commentary_id';

    protected $fillable = [
        'match_id',
        'ball_id',
        'commentary_text',
        'over_number',
        'type',
    ];

    protected $casts = [
        'over_number' => 'decimal:1',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function ball(): BelongsTo
    {
        return $this->belongsTo(BallByBall::class, 'ball_id', 'ball_id');
    }
}
