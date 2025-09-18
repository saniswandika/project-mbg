<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_pbbs extends Model
{
    use HasFactory;
    protected $table = 'log_pbbs';
    protected $fillable = [
        'id_trx_pbbs', 'id_alur_pbbs','tujuan_pbbs','petugas_pbbs','catatan_pbbs','file_pendukung_pbbs','updated_at','created_at','updated_by_pbbs','created_by_pbbs'
    ];
}
