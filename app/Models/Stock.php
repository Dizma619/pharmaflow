<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    protected $fillable = [
        'medicine_id',
        'warehouse_id',
        'shelf_id',
        'quantity',
        'expired_date',
        'batch_number',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'quantity' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(
            Medicine::class
        );
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(
            Shelf::class
        );
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(
            StockAdjustment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeLowStock($query)
    {
        return $query->whereHas(
            'medicine',
            function ($q) {
                $q->whereColumn(
                    'stocks.quantity',
                    '<=',
                    'medicines.stock_minimum'
                );
            }
        );
    }

    public function scopeExpired($query)
    {
        return $query
            ->whereNotNull(
                'expired_date'
            )
            ->whereDate(
                'expired_date',
                '<=',
                now()
            );
    }

    public function scopeExpiringSoon($query)
    {
        return $query
            ->whereNotNull(
                'expired_date'
            )
            ->whereDate(
                'expired_date',
                '>',
                now()
            )
            ->whereDate(
                'expired_date',
                '<=',
                now()->addDays(30)
            );
    }

    public function scopeAvailable($query)
    {
        return $query->where(
            'quantity',
            '>',
            0
        );
    }

    public function scopeFIFO($query)
    {
        return $query
            ->orderBy(
                'expired_date',
                'asc'
            )
            ->orderBy(
                'created_at',
                'asc'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getIsExpiredAttribute()
    {
        return $this->expired_date
            ? $this->expired_date
                ->isPast()
            : false;
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->expired_date
            ? $this->expired_date
                ->between(
                    now(),
                    now()->addDays(30)
                )
            : false;
    }

    public function getDaysToExpireAttribute()
    {
        return $this->expired_date
            ? now()->diffInDays(
                $this->expired_date,
                false
            )
            : null;
    }
}