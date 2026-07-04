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
        'family_safety_note',
    ];

    protected $casts = [
        'family_safety_note' => 'array',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }
}
