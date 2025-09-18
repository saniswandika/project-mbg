<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indonesia_village extends Model
{
    use HasFactory;

    protected $table = 'indonesia_villages';

    protected $fillable = ['id', 'district_id', 'name', 'village_code'];

    public function district()
    {
        return $this->belongsTo(Indonesia_district::class, 'district_id');
    }
}
