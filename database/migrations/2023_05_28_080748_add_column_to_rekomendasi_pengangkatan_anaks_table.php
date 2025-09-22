<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rekomendasi_pengangkatan_anaks', function (Blueprint $table) {
            $table->id();
            $table->string('Nomor_Surat')->nullable();
            $table->string('Nama_ibu_angkat')->nullable();
            $table->string('Nama_Bapak_angkat')->nullable();
            $table->boolean('validasi_surat')->nullable()->default(false);
            $table->string('jenis_kesos')->nullable();
            $table->timestamps();  // Timestamps untuk created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('rekomendasi_pengangkatan_anaks');
    }
};
