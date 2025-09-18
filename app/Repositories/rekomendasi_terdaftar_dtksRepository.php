<?php

namespace App\Repositories;

use App\Models\rekomendasi_terdaftar_dtks;
use App\Repositories\BaseRepository;

class rekomendasi_terdaftar_dtksRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'nama',
        'no_kk',
        'nik'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return rekomendasi_terdaftar_dtks::class;
    }
}
