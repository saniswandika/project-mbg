<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_yayasanProvinsi extends Model
{
    use HasFactory;
    protected $table = 'log_yayasanProvinsi';
    protected $fillable = [
        'id_trx_yaprov', 'id_alur_yaprov','tujuan_yaprov','petugas_yaprov','catatan_yaprov','file_pendukung_yaprov','updated_at','created_at','created_by_yaprov','updated_by_yaprov'
    ];
}
