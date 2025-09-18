<?php

namespace App\Repositories;

use App\Models\rekomendasi_biaya_perawatan;
use App\Repositories\BaseRepository;

class rekomendasi_biaya_perawatanRepository extends BaseRepository
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
        return rekomendasi_biaya_perawatan::class;
    }
}
