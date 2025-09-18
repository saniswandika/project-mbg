<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rekomendasi_yayasans_provinsi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('id_alur')->nullable();
            $table->string('no_pendaftaran');
            $table->string('id_provinsi');
            $table->string('id_kabkot');
            $table->string('id_kecamatan')->nullable();
            $table->string('id_kelurahan')->nullable();
            $table->string('jenis_pelapor')->nullable();
            $table->string('nama_pel')->nullable();
            $table->string('nik_pel')->nullable();
            $table->string('telp_pel')->nullable();
            $table->string('status_kepengurusan')->nullable();
            $table->string('alamat_pel')->nullable();
            $table->string('akta_notaris')->nullable();
            $table->string('nama_lembaga')->nullable();
            $table->string('alamat_lembaga')->nullable();
            $table->string('nama_notaris')->nullable();
            $table->string('notgl_akta')->nullable();
            $table->string('nama_ketua')->nullable();
            $table->string('status')->nullable();
            $table->string('tipe')->nullable();
            $table->string('no_ahu')->nullable();
            $table->string('tgl_mulai')->nullable();
            $table->string('tgl_selesai')->nullable();
            $table->string('akta_notarispendirian')->nullable();
            $table->string('adart')->nullable();
            $table->string('struktur_organisasi')->nullable();
            $table->string('foto_ktp_pengurus')->nullable();
            $table->string('no_wajibpajak')->nullable();
            $table->string('data_terimalayanan')->nullable();
            $table->string('laporan_keuangan')->nullable();
            $table->string('laporan_kegiatan')->nullable();
            $table->string('foto_plang')->nullable();
            $table->string('visi_misi')->nullable();
            $table->string('proker_yayasan')->nullable();
            $table->string('data_aset')->nullable();
            $table->string('data_sdm')->nullable();
            $table->string('kelengkapan_sarpras')->nullable();
            $table->string('form_kelengkapanberkas')->nullable();
            $table->string('file_permohonan')->nullable();
            $table->dateTime('tgl_sk_sebelumnya')->nullable();
            $table->string('no_sk_sebelumnya')->nullable();
            $table->string('sertifikat_akreditasi')->nullable();
            $table->string('no_sk_provinsi')->nullable();
            $table->string('keterangan_daftar_ulang')->nullable();
            $table->string('keterangan_yayasan_provinsi')->nullable();
            $table->string('catatan')->nullable();
            $table->string('status_alur')->nullable();
            $table->string('tujuan')->nullable();
            $table->string('petugas')->nullable();
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('draft_rekomendasi')->nullable();
            $table->string('ttd_kepala_dinas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekomendasi_yayasans_provinsi');
    }
};
