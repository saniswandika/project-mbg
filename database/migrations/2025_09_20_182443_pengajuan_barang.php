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
        Schema::create('pengajuan_barangs', function (Blueprint $table) {
            $table->id();                                  // primary key
            $table->integer('id_barang');
            $table->integer('id_pengaju');              // lebih tepat integer
            $table->integer('id_akutansi');              // lebih tepat integer
            $table->integer('id_admin');              // lebih tepat integer
            $table->integer('id_superadmin');              // lebih tepat integer
            $table->integer('jumlah');              // lebih tepat integer
            $table->string('merk_barang');
            $table->string('harga_barang');
            $table->integer('status');              // lebih tepat integer
            $table->text('deskripsi')->nullable();         // opsional
            $table->text('foto')->nullable();         // opsional
            $table->boolean('is_active')->default(true);   // pengganti published
            $table->timestamps();                          // created_at & updated_at
            $table->softDeletes();                         // deleted_at (soft delete)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengajuan_barangs');
    }
};
