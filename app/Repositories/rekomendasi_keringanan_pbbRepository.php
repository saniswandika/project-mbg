<?php

namespace App\Repositories;

use App\Models\rekomendasi_keringanan_pbb;
use App\Repositories\BaseRepository;

class rekomendasi_keringanan_pbbRepository extends BaseRepository
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
        return rekomendasi_keringanan_pbb::class;
    }
}
