<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_pengan extends Model
{
    use HasFactory;
    protected $table = 'log_pengan';
    protected $fillable = [
        'id_trx_pengan', 'id_alur_pengan','tujuan_pengan','petugas_pengan','catatan_pengan','file_pendukung_pengan','updated_at','created_at','updated_by_pengan','created_by_pengan'
    ];
}
