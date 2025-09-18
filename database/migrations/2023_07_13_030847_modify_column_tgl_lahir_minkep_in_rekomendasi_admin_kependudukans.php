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
        Schema::table('rekomendasi_admin_kependudukans', function (Blueprint $table) {
            $table->date('tgl_lahir_minkep')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rekomendasi_admin_kependudukans', function (Blueprint $table) {
            $table->timestamp('tgl_lahir_minkep')->change();
        });
    }
};
