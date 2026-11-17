<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One real market value for one day (daily close, or a monthly index value).
 */
class PricePoint extends Model
{
    protected $table = 'price_points';

    protected $fillable = ['series', 'date', 'value'];

    protected $casts = [
        'value' => 'float',
    ];
}
