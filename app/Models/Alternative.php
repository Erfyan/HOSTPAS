<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alternative extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'provider',
        'name',
        'category',
        'spec_raw',
    ];

    /**
     * Get the scores for this alternative.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Get the criteria associated with this alternative through scores.
     */
    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(Criterion::class, 'scores')
            ->withPivot('value')
            ->withTimestamps();
    }
}
