<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rekomendasi_keringanan_pbb  extends Model
{
    public $table = 'rekomendasi_keringanan_pbbs';

    public $fillable = [
        // 'file_ktp_terlapor_pbb',
        'no_pendaftaran_pbb',
        'id_provinsi_pbb',
        'id_kabkot_pbb',
        'id_kecamatan_pbb',
        'id_kelurahan_pbb',
        'jenis_pelapor_pbb',
        'ada_nik_pbb',
        'no_kk_pbb',
        'nama_pbb',
        'tempat_lahir_pbb',
        'tgl_lahir_pbb',
        'jenis_kelamin_pbb',
        'telp_pbb',
        'email_pbb',
        'alamat_pbb',
        'status_dtks_pbb',
        'file_ktp_terlapor_pbb',
        'file_kk_terlapor_pbb',
        'file_keterangan_dtks_pbb',
        'file_pendukung_pbb',
        'tujuan_pbb',
        'status_aksi_pbb',
        'petugas_pbb',
        'createdby_pbb',
        'updatedby_pbb',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'no_pendaftaran_pbb'=> 'string',
        'id_provinsi_pbb'=> 'string',
        'id_kabkot_pbb'=> 'string',
        'id_kecamatan_pbb'=> 'string',
        'id_kelurahan_pbb'=> 'string',
        'jenis_pelapor_pbb'=> 'string',
        'ada_nik_pbb'=> 'string',
        'nik_pbb'=> 'string',
        'no_kk_pbb'=> 'string',
        'nama_pbb'=> 'string',
        'tempat_lahir_pbb'=> 'string',
        'tgl_lahir_pbb'=> 'string',
        'jenis_kelamin_pbb'=> 'string',
        'telp_pbb'=> 'string',
        'email_pbb'=>'string',
        'alamat_pbb'=> 'string',
        'status_dtks_pbb'=>'string',
        'file_ktp_terlapor_pbb'=> 'string',
        'file_kk_terlapor_pbb'=> 'string',
        'file_keterangan_dtks_pbb'=> 'string',
        'file_pendukung_pbb'=> 'string',
        'tujuan_pbb'=> 'string',
        'status_aksi_pbb'=> 'string',
        'petugas_pbb'=> 'string',
        'createdby_pbb'=> 'string',
        'updatedby_pbb'=> 'string',
        'created_at'=> 'string',
        'updated_at'=> 'string'
    ];

    public static array $rules = [
        'no_pendaftaran_pbb'=>'required',
        'id_provinsi_pbb'=>'required',
        'id_kabkot_pbb'=>'required',
        'id_kecamatan_pbb'=>'required',
        'id_kelurahan_pbb'=>'required',
        'jenis_pelapor_pbb'=>'required',
        'ada_nik_pbb'=>'required',
        'nik_pbb'=>'required',
        'no_kk_pbb'=>'required',
        'nama_pbb'=>'required',
        'tempat_lahir_pbb'=>'required',
        'tgl_lahir_pbb'=>'required',
        'jenis_kelamin_pbb'=>'required',
        'telp_pbb'=>'required',
        'email_pbb'=>'required',
        'alamat_pbb'=>'required',
        'status_dtks_pbb'=>'required',
        'file_ktp_terlapor_pbb'=>'required',
        'file_kk_terlapor_pbb'=>'required',
        'file_keterangan_dtks_pbb'=>'required',
        'file_pendukung_pbb'=>'required',
        'tujuan_pbb'=>'required',
        'status_aksi_pbb'=>'required',
        'petugas_pbb'=>'required',
        'createdby_pbb'=>'required',
        'updatedby_pbb'=>'required',
        'created_at'=>'required',
        'updated_at'=>'required'
    ];
}
