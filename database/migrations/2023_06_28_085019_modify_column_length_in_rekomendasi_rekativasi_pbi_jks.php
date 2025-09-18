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
        Schema::table('rekomendasi_rekativasi_pbi_jks', function (Blueprint $table) {
            $table->string('no_kk_pbijk')->change(); // Jika ingin membalikkan perubahan
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rekomendasi_rekativasi_pbi_jks', function (Blueprint $table) {
            //
        });
    }
};
