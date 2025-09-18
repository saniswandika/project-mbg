<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logpendidikan extends Model
{
    use HasFactory;
    protected $table = 'log_bantuan_pendidikan';
    protected $fillable = [
        'id_trx_bantuan_pendidikan', 'id_alur_bantuan_pendidikan','tujuan_bantuan_pendidikan','petugas_bantuan_pendidikan','catatan_bantuan_pendidikan','file_pendukung_bantuan_pendidikan','updated_at','created_by_log_bantuan_pendidikans','updated_by_log_bantuan_pendidikans','created_at'
    ];
}
