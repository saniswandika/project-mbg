<?php

namespace App\Repositories;

use App\Models\rekomendasi_admin_kependudukan;
use App\Repositories\BaseRepository;

class rekomendasi_admin_kependudukanRepository extends BaseRepository
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
        return rekomendasi_admin_kependudukan::class;
    }
}
