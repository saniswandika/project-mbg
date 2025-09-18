<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wilayah extends Model
{
    use HasFactory;
    protected $table = 'wilayahs';
    protected $fillable = [
        'province_id', 'kota_id','kecamatan_id','kelurahan_id','status_wilayah','createdby'
    ];
}
