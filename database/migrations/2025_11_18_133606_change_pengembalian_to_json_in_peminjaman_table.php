<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->json('due_dates')->nullable()->after('pengembalian'); // baru: tanggal per buku
            $table->dropColumn('pengembalian'); // hapus yang lama
        });
    }

    public function down()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->date('pengembalian')->after('pinjam');
            $table->dropColumn('due_dates');
        });
    }
};
