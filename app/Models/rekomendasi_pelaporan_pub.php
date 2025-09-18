<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rekomendasi_pelaporan_pub extends Model
{
    public $table = 'rekomendasi_pelaporan_pubs';

    public $fillable = [
        'Sistem_Pengumpulan',
        'no_pendaftaran_ubar',
        'id_provinsi_ubar',
        'id_kabkot_ubar',
        'id_kecamatan_ubar',
        'id_kelurahan_ubar',
        'nik_ubar',
        'nama_ubar',
        'telp_ubar',
        'alamat_ubar',
        'surat_permohonan_pub',
        'surat_izin_terdaftar',
        'surat_keterangan_domisili',
        'no_pokok_wajib_pajak',
        'bukti_setor_pajak',
        'norek_penampung_pub',
        'ktp_direktur',
        'super_keabsahan_dokumen',
        'super_bermaterai_cukup',
        'catatan_ubar',
        'proposal_pub',
        'tujuan_ubar',
        'status_aksi_ubar',
        'petugas_ubar',
        'Nomor_Surat',
        'validasi_surat',
        'createdby_ubar',
        'updatedby_ubar',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'no_pendaftaran_ubar'=>'string',
        'id_provinsi_ubar'=>'string',
        'id_kabkot_ubar'=>'string',
        'id_kecamatan_ubar'=>'string',
        'id_kelurahan_ubar'=>'string',
        'nik_ubar'=>'string',
        'nama_ubar'=>'string',
        'telp_ubar'=>'string',
        'alamat_ubar'=>'string',
        'surat_permohonan_pub'=>'string',
        'surat_izin_terdaftar'=>'string',
        'surat_keterangan_domisili'=>'string',
        'no_pokok_wajib_pajak'=>'string',
        'bukti_setor_pajak'=>'string',
        'norek_penampung_pub'=>'string',
        'ktp_direktur'=>'string',
        'super_keabsahan_dokumen'=>'string',
        'super_bermaterai_cukup'=>'string',
        'proposal_pub'=>'string',
        'catatan_ubar'=>'string',
        'tujuan_ubar'=>'string',
        'status_aksi_ubar'=>'string',
        'petugas_ubar'=>'string',
        'ttd_kepala_dinas'=>'string',
        'createdby_ubar'=>'string',
        'updatedby_ubar'=>'string',
        'created_at'=>'string',
        'updated_at'=>'string'
    ];

    public static array $rules = [
    'no_pendaftaran_ubar'=>'required',
    'id_provinsi_ubar'=>'required',
    'id_kabkot_ubar'=>'required',
    'id_kecamatan_ubar'=>'required',
    'id_kelurahan_ubar'=>'required',
    'nik_ubar'=>'required',
    'nama_ubar'=>'required',
    'telp_ubar'=>'required',
    'alamat_ubar'=>'required',
    'surat_permohonan_pub'=>'required',
    'surat_izin_terdaftar'=>'required',
    'surat_keterangan_domisili'=>'required',
    'no_pokok_wajib_pajak'=>'required',
    'bukti_setor_pajak'=>'required',
    'norek_penampung_pub'=>'required',
    'ktp_direktur'=>'required',
    'super_keabsahan_dokumen'=>'required',
    'super_bermaterai_cukup'=>'required',
    'proposal_pub'=>'required',
    'catatan_ubar'=>'required',
    'tujuan_ubar'=>'required',
    'status_aksi_ubar'=>'required',
    'petugas_ubar'=>'required',
    'ttd_kepala_dinas'=>'required',
    'createdby_ubar'=>'required',
    'updatedby_ubar'=>'required',
    'created_at'=>'required',
    'updated_at'=>'required'

    ];
}
