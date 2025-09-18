<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logYayasan extends Model
{
    use HasFactory;
    protected $table = 'log_yayasan';
    protected $fillable = [
        'id_trx_yayasan', 'id_alur', 'validasi_surat','tujuan','petugas','catatan','file_pendukung','updated_at','created_by','updated_by'
    ];
}
