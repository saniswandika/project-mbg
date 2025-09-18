<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_minkep extends Model
{
    use HasFactory;
    protected $table = 'log_minkep';
    protected $fillable = [
        'id_trx_minkep', 'id_alur_minkep','tujuan_minkep','petugas_minkep','catatan_minkep','file_permohonan_minkep','updated_at','created_at','updated_by_minkep','created_by_minkep'
    ];
}
