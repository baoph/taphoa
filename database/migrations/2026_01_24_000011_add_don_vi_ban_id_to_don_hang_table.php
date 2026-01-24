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
        Schema::table('don_hang', function (Blueprint $table) {
            $table->foreignId('don_vi_ban_id')->nullable()->after('san_pham_id')->constrained('don_vi_ban')->onDelete('set null');
            $table->decimal('so_luong_quy_doi', 10, 2)->default(0)->after('don_vi_ban_id')->comment('Số lượng quy đổi về đơn vị cơ bản');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropForeign(['don_vi_ban_id']);
            $table->dropColumn(['don_vi_ban_id', 'so_luong_quy_doi']);
        });
    }
};
