<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'match_id' => $this->match_id,
            'first_team_id' => $this->first_team_id,
            'second_team_id' => $this->second_team_id,
            'firstTeam' => $this->when($this->firstTeam, function () {
                return [
                    'team_id' => $this->firstTeam->team_id,
                    'team_name' => $this->firstTeam->team_name,
                    'country' => $this->firstTeam->country ? [
                        'country_id' => $this->firstTeam->country->country_id,
                        'name' => $this->firstTeam->country->name,
                        'short_name' => $this->firstTeam->country->short_name
                    ] : null
                ];
            }),
            'secondTeam' => $this->when($this->secondTeam, function () {
                return [
                    'team_id' => $this->secondTeam->team_id,
                    'team_name' => $this->secondTeam->team_name,
                    'country' => $this->secondTeam->country ? [
                        'country_id' => $this->secondTeam->country->country_id,
                        'name' => $this->secondTeam->country->name,
                        'short_name' => $this->secondTeam->country->short_name
                    ] : null
                ];
            }),
            'venue_id' => $this->venue_id,
            'venue' => $this->when($this->venue, function () {
                return [
                    'venue_id' => $this->venue->venue_id,
                    'name' => $this->venue->name,
                    'city' => $this->venue->city,
                    'country' => $this->venue->country,
                    'capacity' => $this->venue->capacity
                ];
            }),
            'match_type' => $this->match_type,
            'match_date' => $this->match_date,
            'overs' => $this->overs,
            'status' => $this->status,
            'first_team_score' => $this->first_team_score,
            'second_team_score' => $this->second_team_score,
            'toss_winner' => $this->toss_winner,
            'outcome' => $this->outcome,
            'description' => $this->description,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
