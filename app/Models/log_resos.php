<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_resos extends Model
{
    use HasFactory;
    protected $table = 'log_resos';
    protected $fillable = [
        'id_trx_resos', 'id_alur_resos','tujuan_resos','petugas_resos','catatan_resos','file_pendukung_resos','updated_at','created_at','updated_by_resos','created_by_resos'
    ];
}
