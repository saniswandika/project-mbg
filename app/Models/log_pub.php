<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_pub extends Model
{
    use HasFactory;
    protected $table = 'log_pub';
    protected $fillable = [
        'id_trx_pub', 'id_alur_pub','tujuan_pub','petugas_pub','catatan_pub','file_pendukung_pub','updated_at','created_at','updated_by_pub','created_by_pub'
    ];
}
