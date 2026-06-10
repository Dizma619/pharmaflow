<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'warehouse_id',
        'quantity_before',
        'quantity_adjustment',
        'quantity_after',
        'type',
        'reason',
        'adjusted_by',
        'notes'
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_adjustment' => 'integer',
        'quantity_after' => 'integer'
    ];

    // Relationships
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeBetween($query, $startDate, $endDate)
    {
        return $query->whereDate('created_at', '>=', $startDate)
                     ->whereDate('created_at', '<=', $endDate);
    }
}