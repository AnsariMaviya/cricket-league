<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partnership extends Model
{
    protected $primaryKey = 'partnership_id';
    
    protected $fillable = [
        'match_id',
        'innings_number',
        'batsman1_id',
        'batsman2_id',
        'runs',
        'balls',
        'wicket_number',
        'start_over',
        'end_over'
    ];

    protected $casts = [
        'start_over' => 'decimal:1',
        'end_over' => 'decimal:1',
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'match_id');
    }

    public function batsman1()
    {
        return $this->belongsTo(Player::class, 'batsman1_id', 'player_id');
    }

    public function batsman2()
    {
        return $this->belongsTo(Player::class, 'batsman2_id', 'player_id');
    }
}
