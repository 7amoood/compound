<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'role',
        'compound_id',
        'block_no',
        'floor',
        'apt_no',
        'service_type_id',
        'photo',
        'is_active',
        'rating_average',
        'rating_count',
        'total_earnings',
        'fcm_token',
        'remember_token',
        'market_id',
        'webauthn_credential_id',
        'webauthn_public_key',
        'biometric_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'webauthn_credential_id',
        'webauthn_public_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is resident
     */
    public function isResident(): bool
    {
        return $this->role === 'resident';
    }

    /**
     * Check if user is provider
     */
    public function isProvider(): bool
    {
        return $this->role === 'provider';
    }

    /**
     * Check if user is market staff
     */
    public function isMarketStaff(): bool
    {
        return $this->role === 'provider' && ! is_null($this->market_id);
    }

    /**
     * Get the service category for providers
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_type_id');
    }

    /**
     * Get the market for market staff
     */
    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Get the compound for residents
     */
    public function compound()
    {
        return $this->belongsTo(Compound::class);
    }

    /**
     * Get the requests made by this resident
     */
    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'resident_id');
    }

    /**
     * Get the proposals made by this provider
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'provider_id');
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if ($this->block_no) {
            $parts[] = "مبنى {$this->block_no}";
        }

        if ($this->floor) {
            $parts[] = "الطابق {$this->floor}";
        }

        if ($this->apt_no) {
            $parts[] = "شقة {$this->apt_no}";
        }

        return implode('، ', $parts);
    }

    /**
     * Update provider's rating (denormalized)
     * Called after a new review is submitted
     */
    public function updateRating(int $newRating): void
    {
        if ($this->role !== 'provider') {
            return;
        }

        // Calculate new average: ((old_avg * old_count) + new_rating) / (old_count + 1)
        $oldTotal   = $this->rating_average * $this->rating_count;
        $newCount   = $this->rating_count + 1;
        $newAverage = ($oldTotal + $newRating) / $newCount;

        $this->update([
            'rating_average' => round($newAverage, 1),
            'rating_count'   => $newCount,
        ]);
    }

    /**
     * Add to provider's earnings (denormalized)
     */
    public function addEarnings(float $amount): void
    {
        if ($this->role !== 'provider') {
            return;
        }

        $this->increment('total_earnings', $amount);
    }
}
