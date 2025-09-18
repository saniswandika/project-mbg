<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_pelaporanPub extends Model
{
    use HasFactory;
    protected $table = 'log_pelaporanpub';
    protected $fillable = [
        'id_trx_ubar', 'id_alur_ubar','tujuan_ubar','petugas_ubar','catatan_ubar','file_permohonan_ubar','updated_at','created_at','updated_by_ubar','created_by_ubar'
    ];
}
