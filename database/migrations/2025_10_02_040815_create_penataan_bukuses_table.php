<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ Tambahkan pengecekan biar nggak error kalau tabel sudah ada
        if (!Schema::hasTable('penataan_bukus')) {
            Schema::create('penataan_bukus', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penataan_bukuses');
    }
};
