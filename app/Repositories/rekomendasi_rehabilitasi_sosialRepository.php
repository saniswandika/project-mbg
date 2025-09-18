<?php

namespace App\Repositories;

use App\Models\rekomendasi_rehabilitasi_sosial;
use App\Repositories\BaseRepository;

class rekomendasi_rehabilitasi_sosialRepository extends BaseRepository
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
        return rekomendasi_rehabilitasi_sosial::class;
    }
}
