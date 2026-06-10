<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // Menentukan nama tabel jika tidak otomatis jamak (attendances)
    protected $table = 'attendances';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'notes'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in'        => 'datetime',
        'check_out'       => 'datetime',
    ];

    /**
     * Relasi ke Employee (Karyawan)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}