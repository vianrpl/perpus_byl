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
        // ✅ Jangan bikin tabel kalau sudah ada
        if (!Schema::hasTable('peminjaman')) {
            Schema::create('peminjaman', function (Blueprint $table) {
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
        Schema::dropIfExists('peminjaman');
    }
};
