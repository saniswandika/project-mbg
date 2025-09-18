<?php

namespace App\Repositories;

use App\Models\rekomendasi_pelaporan_pub;
use App\Repositories\BaseRepository;

class rekomendasi_pelaporan_pubRepository extends BaseRepository
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
        return rekomendasi_pelaporan_pub::class;
    }
}
