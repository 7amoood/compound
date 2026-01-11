<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'icon',
        'delivery_methods',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delivery_methods' => 'array',
            'is_active'        => 'boolean',
        ];
    }

    /**
     * Get the providers for this service category
     */
    public function providers()
    {
        return $this->hasMany(User::class, 'service_type_id')
            ->where('role', 'provider')
            ->where('is_active', true);
    }

    /**
     * Get the requests for this category
     */
    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'service_category_id');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
