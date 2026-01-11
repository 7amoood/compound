<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
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
     * Get the provider through the accepted proposal
     */
    public function getProviderAttribute()
    {
        return $this->request->acceptedProposal?->provider;
    }
}
