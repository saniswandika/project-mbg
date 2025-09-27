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
        Schema::create('bahan_olahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bahan');              // lebih tepat integer
            $table->string('merk_bahan');
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
        Schema::dropIfExists('bahan_olahans');
    }
};
