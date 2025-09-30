<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap', 100);
            $table->string('nik', 20)->unique();
            $table->string('no_kk', 20);
            $table->text('alamat')->nullable();   
            $table->string('foto_ktp', 255)->nullable();
            $table->string('no_bpjs', 30)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->string('atas_nama_rekening', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
