<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'position',
        'hire_date',
        'birth_date',
        'id_number',
        'bank_account',
        'base_salary',
        'status',
        'notes'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'base_salary' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (!$employee->employee_id) {
                $employee->employee_id = 'EMP-' . strtoupper(date('YmdHis'));
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function attendance()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    // Methods
    public function getAttendanceThisMonth()
    {
        return $this->attendance()
            ->whereYear('attendance_date', now()->year)
            ->whereMonth('attendance_date', now()->month)
            ->count();
    }
}

