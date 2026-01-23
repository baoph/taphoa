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
            $table->integer('so_luong')->nullable()->default(0)->after('gia_ban_le')->comment('Số lượng');
            $table->integer('ti_so_chuyen_doi')->nullable()->default(1)->after('so_luong')->comment('Tỉ số chuyển đổi');
            $table->integer('so_luong_don_vi')->nullable()->default(0)->after('ti_so_chuyen_doi')->comment('Số lượng đơn vị (tự động tính)');
            $table->text('ghi_chu')->nullable()->after('so_luong_don_vi')->comment('Ghi chú');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->dropColumn(['so_luong', 'ti_so_chuyen_doi', 'so_luong_don_vi', 'ghi_chu']);
        });
    }
};
