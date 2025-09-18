<?php

namespace App\Repositories;

use App\Models\rekomendasi_terdaftar_yayasan;
use App\Repositories\BaseRepository;

class rekomendasi_terdaftar_yayasanRepository extends BaseRepository
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
        return rekomendasi_terdaftar_yayasan::class;
    }
}
