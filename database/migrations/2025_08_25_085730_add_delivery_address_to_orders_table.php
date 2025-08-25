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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_name')->nullable()->after('payment_method');
            $table->string('delivery_phone')->nullable()->after('delivery_name');
            $table->text('delivery_address')->nullable()->after('delivery_phone');
            $table->string('delivery_ward')->nullable()->after('delivery_address'); // Phường/Xã
            $table->string('delivery_district')->nullable()->after('delivery_ward'); // Quận/Huyện
            $table->string('delivery_province')->nullable()->after('delivery_district'); // Tỉnh/Thành phố
            $table->text('delivery_note')->nullable()->after('delivery_province'); // Ghi chú giao hàng
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_name',
                'delivery_phone', 
                'delivery_address',
                'delivery_ward',
                'delivery_district',
                'delivery_province',
                'delivery_note'
            ]);
        });
    }
};
