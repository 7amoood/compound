<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'resident_id',
        'service_category_id',
        'status',
        'notes',
        'delivery_method',
        'location',
    ];

    const STATUS_PENDING     = 'pending';
    const STATUS_ACCEPTED    = 'accepted_offer';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_CANCELLED   = 'cancelled';

    /**
     * Get status labels in Arabic
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING     => 'قيد الانتظار',
            self::STATUS_ACCEPTED    => 'تم قبول العرض',
            self::STATUS_IN_PROGRESS => 'جاري التنفيذ',
            self::STATUS_COMPLETED   => 'مكتمل',
            self::STATUS_CANCELLED   => 'ملغي',
        ];
    }

    /**
     * Get the resident who made this request
     */
    public function resident()
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    /**
     * Get the service category
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Get the proposals for this request
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'request_id');
    }

    /**
     * Get the accepted proposal
     */
    public function acceptedProposal()
    {
        return $this->hasOne(Proposal::class, 'request_id')->where('is_accepted', true);
    }

    /**
     * Get the review for this request
     */
    public function review()
    {
        return $this->hasOne(Review::class, 'request_id');
    }

    /**
     * Get Arabic status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for requests by service category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('service_category_id', $categoryId);
    }
}
