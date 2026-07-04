<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EconomicIndicator extends Model
{
    protected $table = 'economic_indicators';

    protected $fillable = [
        'indicator_type',
        'indicator_name',
        'value',
        'unit',
        'date',
        'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'date' => 'date',
        'metadata' => 'array',
    ];
}
