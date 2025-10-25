<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Buat tabel member_profiles
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // satu user = satu profile
            $table->string('nama_lengkap')->nullable();
            $table->string('profesi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('ktp_path')->nullable(); // foto KTP
            $table->string('student_card_path')->nullable(); // kartu pelajar opsional
            $table->enum('request_status', ['none', 'pending', 'approved', 'rejected'])->default('none');
            $table->string('verification_code', 20)->nullable();
            $table->timestamp('code_sent_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // relasi ke users.id_user (bukan id default)
            $table->foreign('user_id')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id_user')->on('users')->onDelete('set null');
        });

        // Tambahkan kolom bantu di tabel users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_verified_member')) {
                $table->boolean('is_verified_member')->default(false)->after('role');
            }
            if (!Schema::hasColumn('users', 'is_member')) {
                $table->boolean('is_member')->default(false)->after('is_verified_member');
            }
        });
    }

    public function down(): void
    {
        // Hapus kolom dari users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_member')) {
                $table->dropColumn('is_member');
            }
            if (Schema::hasColumn('users', 'is_verified_member')) {
                $table->dropColumn('is_verified_member');
            }
        });

        // Hapus tabel member_profiles
        Schema::dropIfExists('member_profiles');
    }
};
