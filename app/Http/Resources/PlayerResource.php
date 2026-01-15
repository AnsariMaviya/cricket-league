<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'player_id' => $this->player_id,
            'name' => $this->name,
            'team' => $this->when($this->team, function () {
                return [
                    'team_id' => $this->team->team_id,
                    'team_name' => $this->team->team_name,
                    'country' => $this->team->country ? [
                        'country_id' => $this->team->country->country_id,
                        'name' => $this->team->country->name,
                        'short_name' => $this->team->country->short_name
                    ] : null
                ];
            }),
            'team_id' => $this->team_id,
            'dob' => $this->dob,
            'role' => $this->role,
            'batting_style' => $this->batting_style,
            'bowling_style' => $this->bowling_style,
            'profile_image' => $this->profile_image,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
