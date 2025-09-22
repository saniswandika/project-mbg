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
        Schema::create('master_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');              // lebih tepat integer
            $table->string('merk_barang');
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
        Schema::dropIfExists('master_barangs');
    }
};
