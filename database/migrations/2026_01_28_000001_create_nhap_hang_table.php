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
        Schema::create('nhap_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('san_pham_id')->nullable();
            $table->string('ten_san_pham');
            $table->decimal('so_luong', 10, 2)->default(1);
            $table->decimal('gia_nhap', 12, 0)->default(0);
            $table->date('ngay_nhap');
            $table->unsignedBigInteger('don_vi_ban_id')->nullable();
            $table->decimal('so_luong_quy_doi', 10, 2)->default(0)->comment('Số lượng quy đổi về đơn vị cơ bản');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->foreign('san_pham_id')->references('id')->on('san_pham')->onDelete('set null');
            $table->foreign('don_vi_ban_id')->references('id')->on('don_vi_ban')->onDelete('set null');
            $table->index('ngay_nhap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nhap_hang');
    }
};
