<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketIndex extends Model
{
    protected $table = 'market_indices';

    protected $fillable = [
        'ticker',
        'name',
        'market_status',
        'value',
        'change',
        'change_percent',
        'open',
        'high',
        'low',
        'close',
        'previous_close',
        'volume',
        'updated_at_source',
        'snapshot_date',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'change' => 'decimal:2',
        'change_percent' => 'decimal:2',
        'open' => 'decimal:2',
        'high' => 'decimal:2',
        'low' => 'decimal:2',
        'close' => 'decimal:2',
        'previous_close' => 'decimal:2',
        'volume' => 'integer',
        'updated_at_source' => 'datetime',
        'snapshot_date' => 'date',
    ];
}
