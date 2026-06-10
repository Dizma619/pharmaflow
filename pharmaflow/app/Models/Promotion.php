<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'discount_value',
        'medicine_id',
        'start_date',
        'end_date',
        'max_quantity',
        'is_active',
        'usage_count'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'usage_count' => 'integer'
    ];

    // Relationships
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->whereDate('start_date', '<=', now())
                     ->whereDate('end_date', '>=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}