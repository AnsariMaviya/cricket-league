<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreboardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $matchId;
    public $data;

    public function __construct($matchId, $data)
    {
        $this->matchId = $matchId;
        $this->data = $data;
        
        \Illuminate\Support\Facades\Log::info("🔥 ScoreboardUpdated event created", [
            'matchId' => $matchId,
            'channel' => 'match.' . $matchId,
            'event' => 'scoreboard.updated',
            'data_keys' => array_keys($data)
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('match.' . $this->matchId);
    }

    public function broadcastAs()
    {
        return 'scoreboard.updated';
    }

    public function broadcastWith()
    {
        \Illuminate\Support\Facades\Log::info("🚀 Broadcasting to WebSocket", [
            'matchId' => $this->matchId,
            'channel' => 'match.' . $this->matchId,
            'data' => $this->data
        ]);
        
        return $this->data;
    }
}
