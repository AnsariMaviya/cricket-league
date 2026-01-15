<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $primaryKey = 'country_id';

    protected $fillable = [
        'name',
        'short_name',
    ];

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'country_id', 'country_id');
    }
}
