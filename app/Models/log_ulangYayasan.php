<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_ulangYayasan extends Model
{
    use HasFactory;
    protected $table = 'log_ulangyayasan';
    protected $fillable = [
        'id_trx_ulangYayasan', 'id_alur_ulangYayasan','tujuan_ulangYayasan','petugas_ulangYayasan','catatan_ulangYayasan','file_pendukung_ulangYayasan','updated_at','created_at','created_by_ulangYayasan','updated_by_ulangYayasan','validasi_surat'
    ];
}
