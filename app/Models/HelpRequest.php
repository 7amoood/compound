<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpRequest extends Model
{
    protected $fillable = [
        'requester_id',
        'description',
        'status',
        'helper_id',
        'picked_at',
    ];

    protected $casts = [
        'picked_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_OPEN      = 'open';
    const STATUS_PICKED    = 'picked';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the requester (resident who asked for help)
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the helper (resident who offered help)
     */
    public function helper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'helper_id');
    }

    /**
     * Get comments for this help request
     */
    public function comments(): HasMany
    {
        return $this->hasMany(HelpComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Scope: Open requests
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope: Picked requests
     */
    public function scopePicked($query)
    {
        return $query->where('status', self::STATUS_PICKED);
    }

    /**
     * Scope: Cancelled requests
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }
}
