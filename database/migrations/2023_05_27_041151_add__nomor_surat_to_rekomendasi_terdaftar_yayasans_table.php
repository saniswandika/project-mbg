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
        Schema::table('rekomendasi_terdaftar_yayasans', function (Blueprint $table) {
            $table->String('Nomor_Surat')->nullable();
            $table->boolean('validasi_surat')->nullable()->default(false);
            $table->String('jenis_kesos')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rekomendasi_terdaftar_yayasans', function (Blueprint $table) {
            //
        });
    }
};
