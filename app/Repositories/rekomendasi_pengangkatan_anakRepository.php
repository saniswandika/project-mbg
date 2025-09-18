<?php

namespace App\Repositories;

use App\Models\rekomendasi_pengangkatan_anak;
use App\Repositories\BaseRepository;

class rekomendasi_pengangkatan_anakRepository extends BaseRepository
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
        return rekomendasi_pengangkatan_anak::class;
    }
}
