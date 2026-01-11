<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compound extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'location_url',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active compounds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get residents in this compound
     */
    public function residents(): HasMany
    {
        return $this->hasMany(User::class, 'compound_id');
    }

    /**
     * Get resident count
     */
    public function getResidentCountAttribute(): int
    {
        return $this->residents()->count();
    }
}
