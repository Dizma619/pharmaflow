<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $table = 'stock_opname';

    protected $fillable = [
        'reference_number',
        'warehouse_id',
        'medicine_id',
        'system_quantity',
        'physical_quantity',
        'difference',
        'status',
        'created_by',
        'approved_by',
        'notes',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }

    public function medicine()
    {
        return $this->belongsTo(
            Medicine::class
        );
    }
}