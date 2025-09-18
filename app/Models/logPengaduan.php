<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logPengaduan extends Model
{
    use HasFactory;
    protected $table = 'log_pengaduan';
    protected $fillable = [
        'id_trx_pengaduan', 'id_alur','tujuan','petugas','catatan','file_pendukung','updated_at','created_by','updated_by'
    ];
}
