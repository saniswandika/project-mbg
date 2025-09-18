<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rekomendasi_pengumpulan_undian_berhadiah  extends Model
{
    public $table = 'rekomendasi_pengumpulan_undian_berhadiahs';

    public $fillable = [
        // 'file_ktp_terlapor_pub',
        'no_pendaftaran_pub',
        'id_provinsi_pub',
        'id_kabkot_pub',
        'id_kecamatan_pub',
        'id_kelurahan_pub',
        'jenis_pelapor_pub',
        'ada_nik_pub',
        'no_kk_pub',
        'nama_pub',
        'tempat_lahir_pub',
        'tgl_lahir_pub',
        'jenis_kelamin_pub',
        'telp_pub',
        'email_pub',
        'alamat_pub',
        'status_dtks_pub',
        'file_ktp_terlapor_pub',
        'file_kk_terlapor_pub',
        'file_keterangan_dtks_pub',
        'file_pendukung_pub',
        'tujuan_pub',
        'status_aksi_pub',
        'petugas_pub',
        'createdby_pub',
        'updatedby_pub',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'no_pendaftaran_pub'=> 'string',
        'id_provinsi_pub'=> 'string',
        'id_kabkot_pub'=> 'string',
        'id_kecamatan_pub'=> 'string',
        'id_kelurahan_pub'=> 'string',
        'jenis_pelapor_pub'=> 'string',
        'ada_nik_pub'=> 'string',
        'nik_pub'=> 'string',
        'no_kk_pub'=> 'string',
        'nama_pub'=> 'string',
        'tempat_lahir_pub'=> 'string',
        'tgl_lahir_pub'=> 'string',
        'jenis_kelamin_pub'=> 'string',
        'telp_pub'=> 'string',
        'email_pub'=>'string',
        'alamat_pub'=> 'string',
        'status_dtks_pub'=>'string',
        'file_ktp_terlapor_pub'=> 'string',
        'file_kk_terlapor_pub'=> 'string',
        'file_keterangan_dtks_pub'=> 'string',
        'file_pendukung_pub'=> 'string',
        'tujuan_pub'=> 'string',
        'status_aksi_pub'=> 'string',
        'petugas_pub'=> 'string',
        'createdby_pub'=> 'string',
        'updatedby_pub'=> 'string',
        'created_at'=> 'string',
        'updated_at'=> 'string'
    ];

    public static array $rules = [
        'no_pendaftaran_pub'=>'required',
        'id_provinsi_pub'=>'required',
        'id_kabkot_pub'=>'required',
        'id_kecamatan_pub'=>'required',
        'id_kelurahan_pub'=>'required',
        'jenis_pelapor_pub'=>'required',
        'ada_nik_pub'=>'required',
        'nik_pub'=>'required',
        'no_kk_pub'=>'required',
        'nama_pub'=>'required',
        'tempat_lahir_pub'=>'required',
        'tgl_lahir_pub'=>'required',
        'jenis_kelamin_pub'=>'required',
        'telp_pub'=>'required',
        'email_pub'=>'required',
        'alamat_pub'=>'required',
        'status_dtks_pub'=>'required',
        'file_ktp_terlapor_pub'=>'required',
        'file_kk_terlapor_pub'=>'required',
        'file_keterangan_dtks_pub'=>'required',
        'file_pendukung_pub'=>'required',
        'tujuan_pub'=>'required',
        'status_aksi_pub'=>'required',
        'petugas_pub'=>'required',
        'createdby_pub'=>'required',
        'updatedby_pub'=>'required',
        'created_at'=>'required',
        'updated_at'=>'required'
    ];
}
