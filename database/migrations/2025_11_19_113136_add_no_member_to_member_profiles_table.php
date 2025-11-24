<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->string('no_member', 25)->nullable()->after('id');
            $table->string('foto_3x4', 255)->nullable()->after('student_card_path');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn(['no_member', 'foto_3x4']);
        });
    }
};
