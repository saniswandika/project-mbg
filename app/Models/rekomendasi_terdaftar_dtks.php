<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rekomendasi_terdaftar_dtks  extends Model
{
    public $table = 'rekomendasi_terdaftar_dtks';

    public $fillable = [
        'Nomor_Surat',
        'no_pendaftaran_sudtks',
        'id_provinsi_sudtks',
        'id_kabkot_sudtks',
        'id_kecamatan_sudtks',
        'id_kelurahan_sudtks',
        'jenis_pelapor_sudtks',
        'ada_nik_sudtks',
        'no_kk_sudtks',
        'nama_sudtks',
        'tempat_lahir_sudtks',
        'tgl_lahir_sudtks',
        'jenis_kelamin_sudtks',
        'telp_sudtks',
        'email_sudtks',
        'alamat_sudtks',
        'status_dtks_sudtks',
        'file_ktp_terlapor_sudtks',
        'file_kk_terlapor_sudtks',
        'file_keterangan_dtks_sudtks',
        'file_pendukung_sudtks',
        'tujuan_sudtks',
        'status_aksi_sudtks',
        'petugas_sudtks',
        'createdby_sudtks',
        'updatedby_sudtks',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'no_pendaftaran_sudtks'=> 'string',
        'id_provinsi_sudtks'=> 'string',
        'id_kabkot_sudtks'=> 'string',
        'id_kecamatan_sudtks'=> 'string',
        'id_kelurahan_sudtks'=> 'string',
        'jenis_pelapor_sudtks'=> 'string',
        'ada_nik_sudtks'=> 'string',
        'nik_sudtks'=> 'string',
        'no_kk_sudtks'=> 'string',
        'nama_sudtks'=> 'string',
        'tempat_lahir_sudtks'=> 'string',
        'tgl_lahir_sudtks'=> 'string',
        'jenis_kelamin_sudtks'=> 'string',
        'telp_sudtks'=> 'string',
        'email_sudtks'=>'string',
        'alamat_sudtks'=> 'string',
        'status_dtks_sudtks'=>'string',
        'file_ktp_terlapor_sudtks'=> 'string',
        'file_kk_terlapor_sudtks'=> 'string',
        'file_keterangan_dtks_sudtks'=> 'string',
        'file_pendukung_sudtks'=> 'string',
        'tujuan_sudtks'=> 'string',
        'status_aksi_sudtks'=> 'string',
        'petugas_sudtks'=> 'string',
        'createdby_sudtks'=> 'string',
        'updatedby_sudtks'=> 'string',
        'created_at'=> 'string',
        'updated_at'=> 'string'
    ];

    public static array $rules = [
        'no_pendaftaran_sudtks'=>'required',
        'id_provinsi_sudtks'=>'required',
        'id_kabkot_sudtks'=>'required',
        'id_kecamatan_sudtks'=>'required',
        'id_kelurahan_sudtks'=>'required',
        'jenis_pelapor_sudtks'=>'required',
        'ada_nik_sudtks'=>'required',
        'nik_sudtks'=>'required',
        'no_kk_sudtks'=>'required',
        'nama_sudtks'=>'required',
        'tempat_lahir_sudtks'=>'required',
        'tgl_lahir_sudtks'=>'required',
        'jenis_kelamin_sudtks'=>'required',
        'telp_sudtks'=>'required',
        'email_sudtks'=>'required',
        'alamat_sudtks'=>'required',
        'status_dtks_sudtks'=>'required',
        'file_ktp_terlapor_sudtks'=>'required',
        'file_kk_terlapor_sudtks'=>'required',
        'file_keterangan_dtks_sudtks'=>'required',
        'file_pendukung_sudtks'=>'required',
        'tujuan_sudtks'=>'required',
        'status_aksi_sudtks'=>'required',
        'petugas_sudtks'=>'required',
        'createdby_sudtks'=>'required',
        'updatedby_sudtks'=>'required',
        'created_at'=>'required',
        'updated_at'=>'required'
    ];
}
