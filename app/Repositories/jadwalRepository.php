<?php

namespace App\Repositories;

use App\Models\jadwal;
use App\Repositories\BaseRepository;

class jadwalRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'Nama_Acara',
        'jenis_acara',
        'tanggal_acara',
        'lokasi'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return jadwal::class;
    }
}
