<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Thêm cột mới
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage')->after('description');
            $table->decimal('discount_value', 10, 2)->after('discount_type');
            
            // Copy dữ liệu từ discount_percentage sang discount_value
            // Và set discount_type = 'percentage'
        });
        
        // Update existing data
        DB::statement("UPDATE promotions SET discount_value = discount_percentage, discount_type = 'percentage'");
        
        Schema::table('promotions', function (Blueprint $table) {
            // Xóa cột cũ
            $table->dropColumn('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Thêm lại cột cũ
            $table->decimal('discount_percentage', 5, 2)->after('description');
        });
        
        // Copy dữ liệu về
        DB::statement("UPDATE promotions SET discount_percentage = discount_value WHERE discount_type = 'percentage'");
        
        Schema::table('promotions', function (Blueprint $table) {
            // Xóa cột mới
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
