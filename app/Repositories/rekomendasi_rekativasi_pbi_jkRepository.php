<?php

namespace App\Repositories;

use App\Models\rekomendasi_rekativasi_pbi_jk;
use App\Repositories\BaseRepository;

class rekomendasi_rekativasi_pbi_jkRepository extends BaseRepository
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
        return rekomendasi_rekativasi_pbi_jk::class;
    }
}
