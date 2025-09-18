<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_sudtks extends Model
{
    use HasFactory;
    protected $table = 'log_sudtks';
    protected $fillable = [
        'id_trx_sudtks', 'id_alur_sudtks','tujuan_sudtks','petugas_sudtks','catatan_sudtks','file_pendukung_sudtks','updated_at','created_at','updated_by_sudtks','created_by_sudtks'
    ];
}
