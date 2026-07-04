<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Snapshot extends Model
{
    protected $table = 'snapshots';

    protected $fillable = [
        'last_updated',
        'global_chaos',
        'key_indicators',
        'shipping_chokepoint',
        'last_updated_news_titles',
    ];

    protected $casts = [
        'last_updated' => 'date',
        'global_chaos' => 'array',
        'key_indicators' => 'array',
        'shipping_chokepoint' => 'array',
        'last_updated_news_titles' => 'array',
    ];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(Scenario::class);
    }
}
