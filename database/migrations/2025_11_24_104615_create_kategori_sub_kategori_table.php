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
        Schema::create('kategori_sub_kategori', function (Blueprint $table) {
            $table->id(); // Primary key auto increment

            // PENTING: Pakai INT biasa, bukan unsignedInteger
            // Karena di tabel kategoris dan sub_kategoris pakai INT(11)
            $table->integer('id_kategori');
            $table->integer('id_sub');

            $table->timestamps(); // created_at, updated_at

            // Foreign key dengan engine InnoDB
            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategoris')
                ->onDelete('cascade');

            $table->foreign('id_sub')
                ->references('id_sub')
                ->on('sub_kategoris')
                ->onDelete('cascade');

            // Unique constraint biar gak ada duplikat kombinasi
            $table->unique(['id_kategori', 'id_sub']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_sub_kategori');
    }
};
