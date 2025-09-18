<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekomendasiYayasansProvinsi extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_yayasans_provinsi';

    protected $fillable = [
        'id_alur',
        'no_pendaftaran',
        'id_provinsi',
        'id_kabkot',
        'id_kecamatan',
        'id_kelurahan',
        'jenis_pelapor',
        'nama_pel',
        'nik_pel',
        'telp_pel',
        'status_kepengurusan',
        'alamat_pel',
        'akta_notaris',
        'nama_lembaga',
        'alamat_lembaga',
        'nama_notaris',
        'notgl_akta',
        'nama_ketua',
        'status',
        'tipe',
        'no_ahu',
        'tgl_mulai',
        'tgl_selesai',
        'akta_notarispendirian',
        'adart',
        'struktur_organisasi',
        'foto_ktp_pengurus',
        'no_wajibpajak',
        'data_terimalayanan',
        'laporan_keuangan',
        'laporan_kegiatan',
        'foto_plang',
        'visi_misi',
        'proker_yayasan',
        'data_aset',
        'data_sdm',
        'kelengkapan_sarpras',
        'form_kelengkapanberkas',
        'file_permohonan',
        'tgl_sk_sebelumnya',
        'no_sk_sebelumnya',
        'sertifikat_akreditasi',
        'no_sk_provinsi',
        'keterangan_daftar_ulang',
        'keterangan_yayasan_provinsi',
        'validasi_surat',
        'Nomor_Surat',
        'catatan',
        'status_alur',
        'tujuan',
        'petugas',
        'createdby',
        'updatedby',
        'created_at',
        'updated_at',
        'draft_rekomendasi',
        'ttd_kepala_dinas',
    ];
}
