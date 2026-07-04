<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketStock extends Model
{
    protected $table = 'market_stocks';

    protected $fillable = [
        'ticker',
        'name',
        'market',
        'locale',
        'primary_exchange',
        'type',
        'currency_name',
        'price',
        'change',
        'change_percent',
        'open',
        'high',
        'low',
        'close',
        'previous_close',
        'volume',
        'vwap',
        'updated_at_source',
        'snapshot_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'change' => 'decimal:2',
        'change_percent' => 'decimal:2',
        'open' => 'decimal:2',
        'high' => 'decimal:2',
        'low' => 'decimal:2',
        'close' => 'decimal:2',
        'previous_close' => 'decimal:2',
        'volume' => 'integer',
        'vwap' => 'decimal:2',
        'updated_at_source' => 'datetime',
        'snapshot_date' => 'date',
    ];
}
