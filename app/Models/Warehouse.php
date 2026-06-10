<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    // Mass assignment agar data dari Vue diizinkan masuk ke MySQL
    protected $fillable = [
        'name',
        'city',
        'province',
        'address',
        'capacity',
        'status'
    ];

    /**
     * Relasi ke model Shelf / Rak
     * (Satu gudang mempunyai banyak rak)
     */
    public function shelves()
    {
        // Catatan: Jika nama file model rak lu adalah 'Rak.php', ubah 'Shelf::class' menjadi 'Rak::class'
        return $this->hasMany(Shelf::class);
    }
}