<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pelapor extends Model
{
    use HasFactory;
    protected $table = 'pelapor';
    protected $primaryKey = 'id_pelapor';
    protected $fillable = [
        'id_menu',
        'id_form',
        'jenis_peelaporan',
        'nama_pelapor',
        'ada_nik_pelapor',
        'nik_pelapor',
        'status_dtks_pelapor',
        'tempat_lahir_pelapor',
        'tanggal_lahir_pelapor',
        'jenis_kelamin',
        'telepon_pelapor',
        'alamat_pelapor',
        'createdby_pelapor',
        'updatedby_pelapor',
        'created_at',
        'updated_at'
    ];
}
