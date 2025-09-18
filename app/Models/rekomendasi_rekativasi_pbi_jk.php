<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rekomendasi_rekativasi_pbi_jk extends Model
{
    public $table = 'rekomendasi_rekativasi_pbi_jks';

    public $fillable = [
        'Nomor_Surat',
        'no_pendaftaran_pbijk',
        'id_provinsi_pbijk',
        'id_kabkot_pbijk',
        'id_kecamatan_pbijk',
        'id_kelurahan_pbijk',
        'jenis_pelapor_pbijk',
        'ada_nik_pbijk',
        'nik_pbijk',
        'no_kk_pbijk',
        'nama_pbijk',
        'tempat_lahir_pbijk',
        'tgl_lahir_pbijk',
        'jenis_kelamin_pbijk',
        'telp_pbijk',
        'alamat_pbijk',
        'file_ktp_terlapor_pbijk',
        'file_kk_terlapor_pbijk',
        'file_keterangan_dtks_pbijk',
        'file_pendukung_pbijk',
        'tujuan_pbijk',
        'catatan_pbijk',
        'status_aksi_pbijk',
        'petugas_pbijk',
        'createdby_pbijk',
        'updatedby_pbijk',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'no_pendaftaran_pbijk'=> 'string',
        'id_provinsi_pbijk'=> 'string',
        'id_kabkot_pbijk'=> 'string',
        'id_kecamatan_pbijk'=> 'string',
        'id_kelurahan_pbijk'=> 'string',
        'jenis_pelapor_pbijk'=> 'string',
        'ada_nik_pbijk'=> 'string',
        'nik_pbijk'=> 'string',
        'no_kk_pbijk'=> 'string',
        'nama_pbijk'=> 'string',
        'tempat_lahir_pbijk'=> 'string',
        'tgl_lahir_pbijk'=> 'string',
        'jenis_kelamin_pbijk'=> 'string',
        'telp_pbijk'=> 'string',
        'alamat_pbijk'=> 'string',
        'file_ktp_terlapor_pbijk'=> 'string',
        'file_kk_terlapor_pbijk'=> 'string',
        'file_keterangan_dtks_pbijk'=> 'string',
        'file_pendukung_pbijk'=> 'string',
        'tujuan_pbijk'=> 'string',
        'status_aksi_pbijk'=> 'string',
        'petugas_pbijk'=> 'string',
        'createdby_pbijk'=> 'string',
        'updatedby_pbijk'=> 'string',
        'created_at'=> 'string',
        'updated_at'=> 'string'
    ];

    public static array $rules = [
        'no_pendaftaran_pbijk'=>'required',
        'id_provinsi_pbijk'=>'required',
        'id_kabkot_pbijk'=>'required',
        'id_kecamatan_pbijk'=>'required',
        'id_kelurahan_pbijk'=>'required',
        'jenis_pelapor_pbijk'=>'required',
        'ada_nik_pbijk'=>'required',
        'nik_pbijk'=>'required',
        'no_kk_pbijk'=>'required',
        'nama_pbijk'=>'required',
        'tempat_lahir_pbijk'=>'required',
        'tgl_lahir_pbijk'=>'required',
        'jenis_kelamin_pbijk'=>'required',
        'telp_pbijk'=>'required',
        'alamat_pbijk'=>'required',
        'file_ktp_terlapor_pbijk'=>'required',
        'file_kk_terlapor_pbijk'=>'required',
        'file_keterangan_dtks_pbijk'=>'required',
        'file_pendukung_pbijk'=>'required',
        'tujuan_pbijk'=>'required',
        'status_aksi_pbijk'=>'required',
        'petugas_pbijk'=>'required',
        'createdby_pbijk'=>'required',
        'updatedby_pbijk'=>'required',
        'created_at'=>'required',
        'updated_at'=>'required'
    ];
}
