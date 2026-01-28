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
        Schema::table('san_pham', function (Blueprint $table) {
            $table->string('don_vi_co_ban', 50)->default('Cái')->after('gia_ban_le');
            $table->decimal('so_luong', 10, 2)->default(0)->after('don_vi_co_ban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->dropColumn(['don_vi_co_ban', 'so_luong']);
        });
    }
};
