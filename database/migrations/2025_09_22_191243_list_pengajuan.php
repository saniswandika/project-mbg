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
        Schema::create('list_pengajuans', function (Blueprint $table) {
            $table->id();
            $table->string('id_barang');              // lebih tepat integer
            $table->integer('status');              // lebih tepat integer
            $table->integer('id_pengaju');              // lebih tepat integer
            $table->text('deskripsi')->nullable();         // opsional
            $table->text('foto')->nullable();         // opsional
            $table->text('payment_proof')->nullable();         // opsional
            $table->text('receipt_proof')->nullable();         // opsional
            $table->text('item_photo')->nullable();         // opsional
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
        Schema::dropIfExists('list_pengajuans');
    }
};
