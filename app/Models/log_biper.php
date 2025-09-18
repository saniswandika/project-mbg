<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_biper extends Model
{
    use HasFactory;
    protected $table = 'log_biper';
    protected $fillable = [
        'id_trx_biper', 'id_alur_biper','tujuan_biper','petugas_biper','catatan_biper','file_pendukung_biper','updated_at','created_at','updated_by_biper','created_by_biper'
    ];
}
