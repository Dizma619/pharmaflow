<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'kasir_id',
        'customer_id',
        'total_amount',
        'discount_amount',
        'final_amount',
        'cash_received',
        'change_amount',
        'payment_method',
        'payment_status',
        'due_date',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTO REFERENCE NUMBER
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {

            if (!$transaction->reference_number) {

                $prefix =
                    'TRX-' .
                    now()->format('Ymd');

                $last =
                    self::latest()
                    ->first();

                $number = 1;

                if ($last) {

                    $lastNumber =
                        intval(
                            substr(
                                $last->reference_number,
                                -4
                            )
                        );

                    $number =
                        $lastNumber + 1;
                }

                $transaction->reference_number =
                    $prefix .
                    '-' .
                    str_pad(
                        $number,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function kasir()
    {
        return $this->belongsTo(
            User::class,
            'kasir_id'
        );
    }

    public function customer()
    {
        return $this->belongsTo(
            User::class,
            'customer_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            TransactionItem::class
        );
    }
}