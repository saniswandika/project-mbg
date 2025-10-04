<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeranjangBahan extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dari nama model
    protected $table = 'keranjang_bahans';

    // Tentukan kolom yang bisa diisi
    protected $fillable = [
        'id_bahan',
        'id_master_bahan',
        'jumlah_bahan',
        'id_user',
    ];
}
