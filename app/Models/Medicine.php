<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category_id',
        'supplier_id',
        'base_price',
        'markup_percentage',
        'selling_price',
        'unit',
        'photo',
        'stock_minimum',
        'stock_maximum',
        'expired_date',
        'status'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expired_date' => 'date'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($medicine) {

            if (!$medicine->code) {
                $medicine->code = 'MED-' . strtoupper(uniqid());
            }

            $medicine->selling_price =
                $medicine->base_price +
                ($medicine->base_price * $medicine->markup_percentage / 100);

        });

        static::updating(function ($medicine) {

            $medicine->selling_price =
                $medicine->base_price +
                ($medicine->base_price * $medicine->markup_percentage / 100);

        });

    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function inventoryAdjustments()
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function stockOpname()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(quantity),0)
            FROM stocks
            WHERE medicine_id=medicines.id)
            <= stock_minimum'
        );
    }

    public function scopeExpired($query)
    {
        return $query->whereDate('expired_date', '<=', now());
    }

    public function getTotalStockAttribute()
    {
        return $this->stocks()->sum('quantity');
    }

    public function getIsLowStockAttribute()
    {
        return $this->total_stock <= $this->stock_minimum;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expired_date &&
               $this->expired_date->isPast();
    }
}