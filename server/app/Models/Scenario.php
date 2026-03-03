<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scenario extends Model
{
    protected $table = 'scenarios';

    protected $fillable = [
        'snapshot_id',
        'name',
        'description',
        'when_visible',
        'earliest_date',
        'probability_percent',
    ];

    protected $casts = [
        'description' => 'array',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }
}
