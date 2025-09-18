<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class jadwal extends Model
{
    public $table = 'jadwals';

    public $fillable = [
        'Nama_Acara',
        'jenis_acara',
        'tanggal_acara',
        'lokasi'
    ];

    protected $casts = [
        'Nama_Acara' => 'string',
        'jenis_acara' => 'string',
        'tanggal_acara' => 'date',
        'lokasi' => 'string'
    ];

    public static array $rules = [
        'Nama_Acara' => 'required'
    ];

    
}
