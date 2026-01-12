<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'phone',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the providers associated with the market.
     */
    public function providers()
    {
        return $this->hasMany(User::class, 'market_id');
    }

    /**
     * Get the requests for this market.
     */
    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'market_id');
    }

    /**
     * Scope for active markets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Alias for providers relationship
     */
    public function users()
    {
        return $this->providers();
    }
}
