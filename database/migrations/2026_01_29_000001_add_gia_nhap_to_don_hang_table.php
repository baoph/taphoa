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
            // Thêm trường giá nhập (giá vốn) tại thời điểm bán
            $table->decimal('gia_nhap', 15, 2)
                ->default(0)
                ->after('gia')
                ->comment('Giá vốn tại thời điểm bán hàng');
            
            // Thêm index để tăng tốc query báo cáo
            $table->index('ngay_ban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropIndex(['ngay_ban']);
            $table->dropColumn('gia_nhap');
        });
    }
};
