<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketNews extends Model
{
    protected $table = 'market_news';

    protected $fillable = [
        'article_id',
        'publisher',
        'title',
        'author',
        'published_utc',
        'article_url',
        'image_url',
        'description',
        'keywords',
        'tickers',
        'insights',
    ];

    protected $casts = [
        'published_utc' => 'datetime',
        'keywords' => 'array',
        'tickers' => 'array',
        'insights' => 'array',
    ];
}
