<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class absenPegawai extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'id',
        'user_id',
        'waktu_absen',
        'status',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
