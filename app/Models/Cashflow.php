<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_date',
        'type',
        'category',
        'description',
        'amount',
        'reference_type',
        'reference_id',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function transaction()
    {
        return $this->belongsTo(
            Transaction::class,
            'reference_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeIncome($query)
    {
        return $query->where(
            'type',
            'income'
        );
    }

    public function scopeExpense($query)
    {
        return $query->where(
            'type',
            'expense'
        );
    }

    public function scopeByType(
        $query,
        $type
    ) {
        return $query->where(
            'type',
            $type
        );
    }

    public function scopeByCategory(
        $query,
        $category
    ) {
        return $query->where(
            'category',
            $category
        );
    }

    public function scopeBetween(
        $query,
        $startDate,
        $endDate
    ) {
        return $query->whereDate(
            'transaction_date',
            '>=',
            $startDate
        )
        ->whereDate(
            'transaction_date',
            '<=',
            $endDate
        );
    }
}