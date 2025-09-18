<?php

namespace App\Repositories;

use App\Models\rekomendasi_pengumpulan_undian_berhadiah;
use App\Repositories\BaseRepository;

class rekomendasi_pengumpulan_undian_berhadiahRepository extends BaseRepository
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
        return rekomendasi_pengumpulan_undian_berhadiah::class;
    }
}
