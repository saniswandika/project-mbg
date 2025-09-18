<?php

namespace App\Repositories;

use App\Models\suketDtks;
use App\Repositories\BaseRepository;

class suketDtksRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'nama_suket',
        'no_suket'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return suketDtks::class;
    }
}
