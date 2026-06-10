<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'medicine_id',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    /*
    |---------------------------------
    | RELATIONSHIPS
    |---------------------------------
    */

    public function transaction()
    {
        return $this->belongsTo(
            Transaction::class
        );
    }

    public function medicine()
    {
        return $this->belongsTo(
            Medicine::class
        );
    }

    /*
    |---------------------------------
    | ACCESSOR
    |---------------------------------
    */

    public function getFormattedSubtotalAttribute()
    {
        return number_format(
            $this->subtotal,
            0,
            ',',
            '.'
        );
    }
}
