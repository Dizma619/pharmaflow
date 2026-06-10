<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    protected $fillable = [
        // Customer - Auth atau Guest
        'customer_id',
        'customer_name',
        'customer_phone',

        // Order Details
        'order_number',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'total_amount',

        // Shipping
        'shipping_method',
        'shipping_address',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_city',
        'shipping_province',
        'shipping_postal_code',

        // Payment
        'payment_method',
        'payment_status',

        // Status & Tracking
        'status',
        'notes',
        'internal_notes',
        'tracking_number',
        'delivered_at',
        'shipped_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'delivered_at' => 'datetime',
        'shipped_at' => 'datetime',
        'subtotal' => 'float',
        'shipping_cost' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'delivery_latitude' => 'float',
        'delivery_longitude' => 'float',
    ];

    // ===================================
    // RELATIONSHIPS
    // ===================================

    /**
     * Relationship: User (Customer)
     * Nullable untuk guest checkout
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id'
        );
    }

    /**
     * Alias untuk backward compatibility
     */
    public function customer(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Relationship: Order Items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: Payment
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Relationship: Cashflows
     */
    public function cashflows(): MorphMany
    {
        return $this->morphMany(
            Cashflow::class,
            'reference'
        );
    }

    // ===================================
    // HELPER METHODS
    // ===================================

    /**
     * Check if order is guest checkout
     */
    public function isGuest(): bool
    {
        return $this->customer_id === null;
    }

    /**
     * Check if order from authenticated customer
     */
    public function isAuthenticated(): bool
    {
        return $this->customer_id !== null;
    }

    /**
     * Get customer identifier
     * Untuk tracking & reporting
     */
    public function getCustomerIdentifier(): string
    {
        return $this->isGuest()
            ? "guest_{$this->customer_phone}"
            : "customer_{$this->customer_id}";
    }

    /**
     * Get customer display name
     */
    public function getCustomerName(): string
    {
        if ($this->isGuest()) {
            return $this->customer_name
                ?? 'Unknown Guest';
        }

        return $this->user?->name
            ?? 'Unknown Customer';
    }

    /**
     * Get customer phone
     */
    public function getCustomerPhone(): string
    {
        return $this->customer_phone
            ?? ($this->user?->phone ?? '-');
    }

    /**
     * Get full shipping address
     */
    public function getFullAddress(): string
    {
        $address = $this->shipping_address
            ?? $this->delivery_address
            ?? '';

        $city = $this->delivery_city ?? '';
        $province = $this->shipping_province ?? '';
        $postal = $this->shipping_postal_code ?? '';

        return trim(
            "{$address}, {$city}, {$province} {$postal}"
        );
    }

    /**
     * Get total income cashflow
     */
    public function getTotalCashflow(): float
    {
        return (float) $this->cashflows()
            ->where('type', 'income')
            ->sum('amount');
    }

    // ===================================
    // QUERY SCOPES
    // ===================================

    /**
     * Scope: Guest orders
     */
    public function scopeGuest(
        Builder $query
    ): Builder {
        return $query->whereNull(
            'customer_id'
        );
    }

    /**
     * Scope: Authenticated customer orders
     */
    public function scopeAuthenticated(
        Builder $query
    ): Builder {
        return $query->whereNotNull(
            'customer_id'
        );
    }

    /**
     * Scope: Get guest order by phone
     */
    public function scopeByGuestPhone(
        Builder $query,
        string $phone
    ): Builder {
        return $query
            ->whereNull('customer_id')
            ->where(
                'customer_phone',
                $phone
            );
    }

    /**
     * Scope: Pending orders
     */
    public function scopePending(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            'pending'
        );
    }

    /**
     * Scope: Active orders
     */
    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                'pending',
                'diproses',
                'dikirim'
            ]
        );
    }

    /**
     * Scope: Completed orders
     */
    public function scopeCompleted(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            'selesai'
        );
    }

    /**
     * Scope: Cancelled orders
     */
    public function scopeCancelled(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            'dibatalkan'
        );
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange(
        Builder $query,
        $startDate,
        $endDate
    ): Builder {
        return $query->whereBetween(
            'created_at',
            [
                $startDate,
                $endDate
            ]
        );
    }
}