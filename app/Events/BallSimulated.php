<?php

namespace App\Events;

use App\Models\BallByBall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BallSimulated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ball;
    public $matchId;

    public function __construct(BallByBall $ball)
    {
        $this->ball = $ball;
        $this->matchId = $ball->match_id;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('match.' . $this->matchId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ball.simulated';
    }

    public function broadcastWith(): array
    {
        return [
            'ball_id' => $this->ball->ball_id,
            'over_number' => $this->ball->over_number,
            'ball_number' => $this->ball->ball_number,
            'runs_scored' => $this->ball->runs_scored,
            'is_wicket' => $this->ball->is_wicket,
            'is_four' => $this->ball->is_four,
            'is_six' => $this->ball->is_six,
            'commentary' => $this->ball->commentary,
            'batsman' => $this->ball->batsman->name,
            'bowler' => $this->ball->bowler->name,
        ];
    }
}
