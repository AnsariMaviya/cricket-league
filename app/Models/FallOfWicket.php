<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FallOfWicket extends Model
{
    protected $primaryKey = 'fow_id';
    
    protected $fillable = [
        'match_id',
        'innings_number',
        'player_id',
        'dismissal_type',
        'runs_at_dismissal',
        'wicket_number',
        'over_number'
    ];

    protected $casts = [
        'over_number' => 'decimal:1',
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }
}
