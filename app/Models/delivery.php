<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code',
        'order_id',
        'transaction_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id_gateway',
        'notes',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_code) {
                $payment->payment_code = 'PAY-' . strtoupper(date('YmdHis'));
            }
        });
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Scopes
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeBetween($query, $startDate, $endDate)
    {
        return $query->whereDate('created_at', '>=', $startDate)
                     ->whereDate('created_at', '<=', $endDate);
    }
}