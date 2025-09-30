<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dari nama model
    protected $table = 'keranjang';

    // Tentukan kolom yang bisa diisi
    protected $fillable = [
        'id_barang',
        'id_master_barang',
        'jumlah_barang',
        'id_user',
    ];
}
