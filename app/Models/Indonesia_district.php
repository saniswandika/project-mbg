<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indonesia_district extends Model
{
    use HasFactory;
    protected $table = 'indonesia_districts';

    public function villages()
    {
        return $this->hasMany(IndonesiaVillage::class, 'district_code', 'id');
    }
}
