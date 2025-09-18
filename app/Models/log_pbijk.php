<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_pbijk extends Model
{
    use HasFactory;
    protected $table = 'log_pbijk';
    protected $fillable = [
        'id_trx_pbijk', 'id_alur_pbijk','tujuan_pbijk','petugas_pbijk','catatan_pbijk','file_pendukung_pbijk','updated_at','created_at','updated_by_pbijk','created_by_pbijk'
    ];
}
