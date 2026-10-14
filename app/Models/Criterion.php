<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criterion extends Model
{
    use HasFactory;

    protected $table = 'criteria';

    protected $fillable = [
        'code',
        'name',
        'unit',
        'type',
        'weight',
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    /**
     * Get the scores associated with this criterion.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Get the alternatives associated with this criterion through scores.
     */
    public function alternatives(): BelongsToMany
    {
        return $this->belongsToMany(Alternative::class, 'scores')
            ->withPivot('value')
            ->withTimestamps();
    }
}
