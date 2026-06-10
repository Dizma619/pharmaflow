<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relationships
     */
    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Scope active warehouse
     */
    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }
}
