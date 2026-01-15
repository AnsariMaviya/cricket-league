<?php

namespace App\Events;

use App\Models\CricketMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $match;

    /**
     * Create a new event instance.
     */
    public function __construct(CricketMatch $match)
    {
        $this->match = $match;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('matches'),
            new Channel('match.' . $this->match->match_id),
            new Channel('team.' . $this->match->first_team_id),
            new Channel('team.' . $this->match->second_team_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'match.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'match_id' => $this->match->match_id,
            'first_team' => [
                'id' => $this->match->firstTeam->team_id,
                'name' => $this->match->firstTeam->team_name,
            ],
            'second_team' => [
                'id' => $this->match->secondTeam->team_id,
                'name' => $this->match->secondTeam->team_name,
            ],
            'venue' => [
                'id' => $this->match->venue->venue_id,
                'name' => $this->match->venue->name,
            ],
            'status' => $this->match->status,
            'match_type' => $this->match->match_type,
            'overs' => $this->match->overs,
            'match_date' => $this->match->match_date?->toISOString(),
            'first_team_score' => $this->match->first_team_score,
            'second_team_score' => $this->match->second_team_score,
            'outcome' => $this->match->outcome,
            'updated_at' => $this->match->updated_at->toISOString(),
        ];
    }
}
