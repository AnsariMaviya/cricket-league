<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'team_id' => $this->team_id,
            'team_name' => $this->team_name,
            'country' => $this->when($this->country, function () {
                return [
                    'country_id' => $this->country->country_id,
                    'name' => $this->country->name,
                    'short_name' => $this->country->short_name
                ];
            }),
            'country_id' => $this->country_id,
            'in_match' => $this->in_match,
            'players_count' => $this->when(isset($this->players_count), $this->players_count),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
