<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One real headline from a news feed, kept with its link and time.
 */
class NewsItem extends Model
{
    protected $table = 'news_items';

    protected $fillable = ['source', 'title', 'url', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
