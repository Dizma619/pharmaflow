<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
    'po_number',
    'supplier_id',
    'subtotal',
    'tax_amount',
    'shipping_cost',
    'total_amount',
    'status',
    'purchase_date',
    'expected_delivery_date',
    'received_date',
    'notes',
    'items_received',
    'items_total',
    'received_at',
    'received_by'
];
    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function cashflow(): BelongsTo
    {
        return $this->belongsTo(Cashflow::class);
    }
}