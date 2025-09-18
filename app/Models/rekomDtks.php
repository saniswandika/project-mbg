<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rekomDtks extends Model
{
    public $table = 'rekom_dtks';

    public $fillable = [
        'nama_Rekom',
        'Keterangan'
    ];

    protected $casts = [
        'nama_Rekom' => 'string',
        'Keterangan' => 'string'
    ];

    public static array $rules = [
        'nama_Rekom' => 'required',
        'Keterangan' => 'required'
    ];

    
}
