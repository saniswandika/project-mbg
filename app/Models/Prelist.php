<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prelist extends Model
{
    use HasFactory;
    public $table = 'prelist';

    public $fillable = [
        'id_provinsi',
        'id_kabkot',
        'id_kecamatan',
        'id_kelurahan',
        'nik',
        'no_kk',
        'nama',
        'tgl_lahir',
        'telp',
        'email'
    ];
}
