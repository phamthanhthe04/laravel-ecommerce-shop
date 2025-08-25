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
            // Tăng độ chính xác cho cột total từ decimal(10,2) thành decimal(15,2)
            $table->decimal('total', 15, 2)->change();
            
            // Thêm các cột payment nếu chưa có
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending')->after('status');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'payment_date')) {
                $table->timestamp('payment_date')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'vnpay_transaction_id')) {
                $table->string('vnpay_transaction_id')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('orders', 'mock_transaction_id')) {
                $table->string('mock_transaction_id')->nullable()->after('vnpay_transaction_id');
            }
            if (!Schema::hasColumn('orders', 'mock_card_last4')) {
                $table->string('mock_card_last4')->nullable()->after('mock_transaction_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Rollback về decimal(10,2)
            $table->decimal('total', 10, 2)->change();
            
            // Xóa các cột đã thêm
            $table->dropColumn([
                'payment_status', 
                'payment_method', 
                'payment_date', 
                'vnpay_transaction_id',
                'mock_transaction_id',
                'mock_card_last4'
            ]);
        });
    }
};
