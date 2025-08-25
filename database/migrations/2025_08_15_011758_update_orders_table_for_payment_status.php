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
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending')->after('status');
            $table->enum('payment_method', ['vnpay', 'cod', 'mock_card', 'mock_quick', 'cash'])->nullable()->after('payment_status');
            $table->string('vnpay_transaction_id')->nullable()->after('payment_method');
            $table->string('mock_transaction_id')->nullable()->after('vnpay_transaction_id');
            $table->string('mock_card_last4', 4)->nullable()->after('mock_transaction_id');
            $table->timestamp('payment_date')->nullable()->after('mock_card_last4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'vnpay_transaction_id', 'mock_transaction_id', 'mock_card_last4', 'payment_date']);
        });
    }
};
