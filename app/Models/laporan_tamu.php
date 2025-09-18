<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class laporan_tamu extends Model
{
    use HasFactory;
    protected $table = 'laporan_tamus';
    protected $fillable = [
        'nama_tamu', 'telepon','kantor_instansi','bidang','pegawai','keperluan','penilaian_tamu'
    ];
}
