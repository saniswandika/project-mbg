<?php

namespace App\Repositories;

use App\Models\rekomDtks;
use App\Repositories\BaseRepository;

class rekomDtksRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'nama_Rekom',
        'Keterangan'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return rekomDtks::class;
    }
}
