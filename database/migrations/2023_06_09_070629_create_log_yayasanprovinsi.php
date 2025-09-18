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
        Schema::create('log_yayasanprovinsi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_trx_yaprov');
            $table->string('id_alur_yaprov');
            $table->string('tujuan_yaprov');
            $table->string('petugas_yaprov')->nullable();
            $table->string('catatan_yaprov')->nullable();
            $table->string('file_pendukung_yaprov')->nullable();
            $table->string('created_by_yaprov')->nullable();
            $table->string('updated_by_yaprov')->nullable();
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
        Schema::dropIfExists('log_yayasanprovinsi');
    }
};
