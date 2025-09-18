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
        Schema::table('rekomendasi_keringanan_pbbs', function (Blueprint $table) {
            $table->string('nik_pbb', 16)->change(); // Jika ingin membalikkan perubahan
            $table->string('no_kk_pbb', 16)->change(); // Jika ingin membalikkan perubahan
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rekomendasi_keringanan_pbbs', function (Blueprint $table) {
            //
        });
    }
};
