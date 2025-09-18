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
            $table->string('telp_pbb')->change(); // Jika ingin membalikkan perubahan
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
