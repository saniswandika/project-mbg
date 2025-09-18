<?php

namespace App\Repositories;

use App\Models\rekomendasi_bantuan_pendidikan;
use App\Repositories\BaseRepository;

class rekomendasi_bantuan_pendidikanRepository extends BaseRepository
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
        return rekomendasi_bantuan_pendidikan::class;
    }
}
