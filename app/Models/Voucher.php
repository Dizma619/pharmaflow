<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'type',
        'discount_value',
        'minimum_purchase',
        'max_usage',
        'current_usage',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->whereDate('start_date', '<=', now())
                     ->whereDate('end_date', '>=', now());
    }

    public function scopeValid($query)
    {
        return $query->active()
                     ->where(function ($q) {
                         $q->whereNull('max_usage')
                           ->orWhereRaw('current_usage < max_usage');
                     });
    }
}