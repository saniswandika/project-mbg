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
        Schema::create('rekomendasi_keringanan_pbbs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('no_pendaftaran_pbb');
            $table->string('id_provinsi_pbb');
            $table->string('id_kabkot_pbb');
            $table->string('id_kecamatan_pbb')->nullable();
            $table->string('id_kelurahan_pbb')->nullable();
            $table->string('jenis_pelapor_pbb')->nullable();
            $table->string('ada_nik_pbb')->nullable();
            $table->integer('nik_pbb')->nullable();
            $table->integer('no_kk_pbb')->nullable();
            $table->string('nama_pbb')->nullable();
            $table->string('tempat_lahir_pbb')->nullable();
            $table->date('tgl_lahir_pbb')->nullable();
            $table->string('jenis_kelamin_pbb')->nullable();
            $table->string('email_pbb')->nullable();
            $table->integer('telp_pbb')->nullable();
            $table->string('alamat_pbb')->nullable();
            $table->string('status_dtks_pbb')->nullable();
            $table->string('file_ktp_terlapor_pbb')->nullable();
            $table->string('file_keterangan_dtks_pbb')->nullable();
            $table->string('file_pendukung_pbb')->nullable();
            $table->string('file_kk_terlapor_pbb')->nullable();
            $table->string('status_aksi_pbb')->nullable();
            $table->string('tujuan_pbb')->nullable();
            $table->string('petugas_pbb')->nullable();
            $table->string('createdby_pbb')->nullable();
            $table->string('updatedby_pbb')->nullable();
            $table->string('catatan_pbb')->nullable();
            $table->boolean('validasi_surat')->nullable();
            $table->string('Nomor_Surat')->nullable();
            $table->string('nama_wajib_pajak_pbb')->nullable();
            $table->string('created_by_pbb')->nullable();
            $table->string('updated_by_pbb')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekomendasi_keringanan_pbbs');
    }
};
