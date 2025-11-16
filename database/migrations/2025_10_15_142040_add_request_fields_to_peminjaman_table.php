<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1️⃣ Pastikan kolom status sama seperti di database aslimu (SQLyog)
        DB::statement("
            ALTER TABLE peminjaman
            MODIFY COLUMN status ENUM('dipinjam','diperpanjang','kembali')
            NULL DEFAULT NULL
        ");

        // 2️⃣ Tambahkan kolom request_status, approved_by, approved_at, nama_peminjam
        Schema::table('peminjaman', function (Blueprint $table) {
            // Tambah request_status jika belum ada
            if (!Schema::hasColumn('peminjaman', 'request_status')) {
                $table->enum('request_status', ['pending', 'approved', 'rejected', 'returned'])
                    ->default('pending')
                    ->after('alamat');
            }

            // Hapus approved_by lama (kalau ada) biar aman
            if (Schema::hasColumn('peminjaman', 'approved_by')) {
                $table->dropColumn('approved_by');
            }

            // Tambah approved_by baru dengan tipe UNSIGNED BIGINT
            $table->unsignedBigInteger('approved_by')->nullable()->after('request_status');

            // Tambah approved_at & nama_peminjam jika belum ada
            if (!Schema::hasColumn('peminjaman', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('peminjaman', 'nama_peminjam')) {
                $table->string('nama_peminjam')->default('')->after('approved_at');
            }

            // 3️⃣ Tambahkan foreign key dengan tipe sama persis
            $table->foreign('approved_by')
                ->references('id_user')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            if (Schema::hasColumn('peminjaman', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            foreach (['request_status', 'approved_at', 'nama_peminjam'] as $col) {
                if (Schema::hasColumn('peminjaman', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
