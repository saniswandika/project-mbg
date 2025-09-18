<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    public $table = 'pengaduans';

    public $fillable = [
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
        'telp',
        'email',
        'hubungan_terlapor',
        'no_peserta',
        'id_program_sosial',
        'file_penunjang',
        'kepesertaan_program',
        'kategori_pengaduan',
        'level_program',
        'sektor_program',
        'no_kartu_program',
        'ringkasan_pengaduan',
        'detail_pengaduan',
        'tl_catatan',
        'tl_file',
        'createdby',
        'updatedby',
        'ada_dtks',
        'status_dtks',
        'tempat_lahir',
        'status_aksi',
        'petugas',
        'tujuan'
    ];

    protected $casts = [
        'no_pendaftaran' => 'string',
        'id_alur' => 'integer',
        'id_provinsi' => 'string',
        'id_kabkot' => 'string',
        'id_kecamatan' => 'string',
        'id_kelurahan' => 'string',
        'jenis_pelapor' => 'string',
        'ada_nik' => 'string',
        'nik' => 'string',
        'no_kk' => 'string',
        'no_kis' => 'string',
        'nama' => 'string',
        'tgl_lahir' => 'date',
        'alamat' => 'string',
        'telp' => 'string',
        'email' => 'string',
        'hubungan_terlapor' => 'string',
        'file_penunjang' => 'string',
        'keluhan_tipe' => 'string',
        'keluhan_id_program' => 'Text',
        'keluhan_detail' => 'string',
        'keluhan_foto' => 'string',
        'tl_catatan' => 'string',
        'tl_file' => 'string',
        'createdby' => 'string',
        'updatedby' => 'string',
        'ada_dtks' => 'string',
        'prelist_dtks' => 'string',
        'no_dtks' => 'string',
        'tempat_lahir' => 'string',
        'diteruskan' => 'string'
    ];

    public static array $rules = [
        'no_pendaftaran' => 'required',
        'id_alur' => 'required',
        'id_provinsi' => 'required',
        'id_kabkot' => 'required',
        'id_kecamatan' => 'required',
        'id_kelurahan' => 'required',
        'jenis_pelapor' => 'required',
        'ada_nik' => 'required',
        // 'nik' => 'required',
        // 'no_kk' => 'required',
        'no_kis' => 'required',
        'nama' => 'required',
        'tgl_lahir' => 'required',
        'alamat' => 'required',
        'telp' => 'required',
        'email' => 'required',
        'hubungan_terlapor' => 'required',
        'file_penunjang' => 'required',
        'keluhan_tipe' => 'required',
        'keluhan_id_program' => 'required',
        'keluhan_detail' => 'required',
        'keluhan_foto' => 'required',
        'tl_catatan' => 'required',
        'tl_file' => 'required',
        'createdby' => 'required',
        'updatedby' => 'required',
        'ada_dtks' => 'required',
    ];

    
}
