<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            // ✅ CEK DULU SEBELUM TAMBAH KOLOM
            if (!Schema::hasColumn('peminjaman', 'pengembalian')) {
                $table->date('pengembalian')->nullable()->after('pinjam');
            }

            if (!Schema::hasColumn('peminjaman', 'kondisi_buku_saat_kembali')) {
                $table->string('kondisi_buku_saat_kembali')->nullable()->after('kondisi');
            }
        });
    }

    public function down()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            if (Schema::hasColumn('peminjaman', 'pengembalian')) {
                $table->dropColumn('pengembalian');
            }

            if (Schema::hasColumn('peminjaman', 'kondisi_buku_saat_kembali')) {
                $table->dropColumn('kondisi_buku_saat_kembali');
            }
        });
    }
};
