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
        Schema::create('keranjang_bahans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_bahan');
            $table->unsignedBigInteger('id_master_bahan');
            $table->integer('jumlah_bahan');
            $table->integer('status')->nullable();
            $table->unsignedBigInteger('id_user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keranjang_bahans');
    }
};
