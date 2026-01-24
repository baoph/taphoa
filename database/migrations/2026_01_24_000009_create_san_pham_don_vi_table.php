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
        Schema::create('san_pham_don_vi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('san_pham_id')->constrained('san_pham')->onDelete('cascade');
            $table->foreignId('don_vi_ban_id')->constrained('don_vi_ban')->onDelete('cascade');
            $table->decimal('ti_le_quy_doi', 10, 2)->default(1); // Tỉ lệ quy đổi về đơn vị cơ bản
            $table->decimal('gia_ban', 15, 2);
            $table->timestamps();
            
            // Index để tăng tốc truy vấn
            $table->index('san_pham_id');
            $table->index('don_vi_ban_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_pham_don_vi');
    }
};
