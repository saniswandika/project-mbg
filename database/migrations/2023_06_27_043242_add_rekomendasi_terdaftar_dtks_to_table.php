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
        Schema::table('rekomendasi_terdaftar_dtks', function (Blueprint $table) {
            $table->boolean('validasi_surat')->nullable();
            $table->string('Nomor_Surat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rekomendasi_terdaftar_dtks', function (Blueprint $table) {
            //
        });
    }
};
