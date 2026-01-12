<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'name',
        'quantity',
        'is_picked',
    ];

    protected $casts = [
        'is_picked' => 'boolean',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }
}
