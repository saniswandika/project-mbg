<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nik',
        'no_kk',
        'foto_ktp',
        'no_bpjs',
        'no_rekening',
        'bank',
        'atas_nama',
        'alamat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
