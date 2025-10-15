<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            // tambahkan kolom pengatur status request
            // kita akan menambahkan enum 'pending','approved','rejected'
            // MySQL enum update via raw statement supaya kompatibel
            DB::statement("ALTER TABLE peminjaman MODIFY `status` ENUM('dipinjam','tersedia','diperpanjang','pending') NOT NULL");
            $table->enum('request_status',['pending','approved','rejected'])->default('pending')->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('request_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('request_note')->nullable()->after('alamat');
        });

        // optional: foreign key untuk approved_by jika users.id_user ada
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['request_status','approved_by','approved_at','request_note']);
            // revert enum back (kembalikan ke semula tanpa 'pending')
            DB::statement("ALTER TABLE peminjaman MODIFY `status` ENUM('dipinjam','tersedia','diperpanjang') NOT NULL");
        });
    }
};
