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
        Schema::table('buku_items', function (Blueprint $table) {
            $table->date('extended_at')->nullable()->after('status'); // NULL = belum pernah diperpanjang
        });
    }

    public function down()
    {
        Schema::table('buku_items', function (Blueprint $table) {
            $table->dropColumn('extended_at');
        });
    }
};
