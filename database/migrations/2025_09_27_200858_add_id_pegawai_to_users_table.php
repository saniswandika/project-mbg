<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 🆕 Tambahkan kolom id_pegawai
            $table->foreignId('id_pegawai')
                  ->nullable()
                  ->constrained('pegawai')
                  ->onDelete('set null'); 
                  // Jika data pegawai dihapus, kolom id_pegawai di users akan menjadi null
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key & kolom saat rollback
            $table->dropForeign(['id_pegawai']);
            $table->dropColumn('id_pegawai');
        });
    }
};
