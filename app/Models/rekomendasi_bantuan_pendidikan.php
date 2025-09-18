<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rekomendasi_bantuan_pendidikan extends Model
{
    public $table = 'rekomendasi_bantuan_pendidikans';

    public $fillable = [
        'validasi_surat',
        'Nomor_Surat',
        'no_pendaftaran_bantuan_pendidikans',
        'id_provinsi_bantuan_pendidikans',
        'id_kabkot_bantuan_pendidikans',
        'id_kecamatan_bantuan_pendidikans',
        'id_kelurahan_bantuan_pendidikans',
        'jenis_pelapor_bantuan_pendidikans',
        'ada_nik_bantuan_pendidikans',
        'nik_bantuan_pendidikans',
        'nama_sekolah',
        'nama_bantuan_pendidikans',
        'tempat_lahir_bantuan_pendidikans',
        'jenis_kelamin_bantuan_pendidikans',
        'telp_bantuan_pendidikans',
        'alamat_bantuan_pendidikans',
        'file_ktp_terlapor_bantuan_pendidikans',
        'file_kk_terlapor_bantuan_pendidikans',
        'file_keterangan_dtks_bantuan_pendidikans',
        'file_pendukung_bantuan_pendidikans',
        'tujuan_bantuan_pendidikans',
        'status_alur_bantuan_pendidikans',
        'petugas_bantuan_pendidikans',
        'createdby_bantuan_pendidikans',
        'updatedby_bantuan_pendidikans',
        'created_at',
        'created_at',
    ];
    // protected $casts = [
    //     'nama' => 'string',
    //     'no_kk' => 'integer',
    //     'nik' => 'integer'
    // ];

    // public static array $rules = [
    //     'nama' => 'required',
    //     'no_kk' => 'required',
    //     'nik' => 'required'
    // ];
}
