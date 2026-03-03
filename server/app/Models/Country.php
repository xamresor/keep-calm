<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = [
        'snapshot_id',
        'name',
        'liquidity',
        'logistics',
        'legitimacy',
        'overall',
        'liquidity_history',
        'logistics_history',
        'legitimacy_history',
        'overall_history',
        'family_safety_note',
    ];

    protected $casts = [
        'liquidity_history' => 'array',
        'logistics_history' => 'array',
        'legitimacy_history' => 'array',
        'overall_history' => 'array',
        'family_safety_note' => 'array',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }
}
