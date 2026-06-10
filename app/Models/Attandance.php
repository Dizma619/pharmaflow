<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'status',
        'notes'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'latitude_in' => 'decimal:8',
        'longitude_in' => 'decimal:8',
        'latitude_out' => 'decimal:8',
        'longitude_out' => 'decimal:8'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('attendance_date', now()->year)
                     ->whereMonth('attendance_date', now()->month);
    }

    // Methods
    public function getWorkDuration()
    {
        if ($this->check_in && $this->check_out) {
            return $this->check_out->diffInHours($this->check_in);
        }
        return null;
    }
}