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
            // Thay đổi so_luong từ integer sang decimal(10,2)
            $table->decimal('so_luong', 10, 2)->nullable()->default(0)->change();
            
            // Thay đổi ti_so_chuyen_doi từ integer sang decimal(10,2) để hỗ trợ tỉ số như 0.5
            $table->decimal('ti_so_chuyen_doi', 10, 2)->nullable()->default(1)->change();
            
            // Thay đổi so_luong_don_vi từ integer sang decimal(10,2)
            $table->decimal('so_luong_don_vi', 10, 2)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            // Khôi phục về integer
            $table->integer('so_luong')->nullable()->default(0)->change();
            $table->integer('ti_so_chuyen_doi')->nullable()->default(1)->change();
            $table->integer('so_luong_don_vi')->nullable()->default(0)->change();
        });
    }
};
