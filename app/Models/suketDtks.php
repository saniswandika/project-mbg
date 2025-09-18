<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class suketDtks extends Model
{
    public $table = 'suket_dtks';

    public $fillable = [
        'nama_suket',
        'no_suket'
    ];

    protected $casts = [
        'nama_suket' => 'string'
    ];

    public static array $rules = [
        'nama_suket' => 'required'
    ];

    
}
