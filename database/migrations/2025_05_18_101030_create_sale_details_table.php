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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sale_id')->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedInteger('employee_id')->nullable();
            $table->decimal('unit_price', 10, 3)->nullable();
            $table->decimal('quantity', 10, 3)->nullable();
            $table->decimal('subtotal', 10, 3)->nullable();
            $table->decimal('promotion_discount', 10, 3)->nullable();
            $table->decimal('total_tax', 10, 3)->nullable();
            $table->text('tax_breakdown')->nullable();
            $table->decimal('total_payable', 10, 3)->nullable();
            $table->enum('is_free', ['Yes', 'No'])->default('No');
            $table->unsignedInteger('promotion_id')->nullable();
            $table->decimal('tips', 10, 3)->nullable();
            $table->decimal('loyalty_point_earn', 10, 3)->nullable();
            $table->unsignedInteger('branch_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->enum('del_status', ['Live', 'Deleted'])->default('Live');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
