<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'provider_id',
        'price',
        'notes',
        'is_accepted',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_accepted' => 'boolean',
        ];
    }

    /**
     * Get the service request
     */
    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    /**
     * Get the provider who made this proposal
     */
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Scope for accepted proposals
     */
    public function scopeAccepted($query)
    {
        return $query->where('is_accepted', true);
    }
}
