<?php

namespace App\Repositories;

use App\Models\Pengaduan;
use App\Repositories\BaseRepository;

class PengaduanRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'no_pendaftaran',
        'id_alur',
        'id_provinsi',
        'id_kabkot',
        'id_kecamatan',
        'id_kelurahan',
        'jenis_pelapor',
        'ada_nik',
        'nik',
        'no_kk',
        'no_kis',
        'nama',
        'tgl_lahir',
        'alamat',
        'telp',
        'email',
        'hubungan_terlapor',
        'file_penunjang',
        'keluhan_tipe',
        'keluhan_id_program',
        'keluhan_detail',
        'keluhan_foto',
        'tl_catatan',
        'tl_file',
        'createdby',
        'updatedby',
        'ada_dtks'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Pengaduan::class;
    }
}
