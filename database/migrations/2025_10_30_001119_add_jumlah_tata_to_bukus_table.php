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
        Schema::table('bukus', function (Blueprint $table) {
            $table->integer('jumlah_tata')->default(0)->after('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bukus', function (Blueprint $table) {
            if (Schema::hasColumn('bukus', 'jumlah_tata')) {
                $table->dropColumn('jumlah_tata');
            }
        });
    }
};
