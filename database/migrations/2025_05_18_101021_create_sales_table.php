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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 25)->nullable();
            $table->string('order_date', 25)->nullable();
            $table->string('order_update_date', 25)->nullable();
            $table->enum('order_from', ['Website', 'POS'])->nullable();
            $table->enum('order_status', ['Pending', 'Confirmed', 'Cancelled', 'Completed'])->nullable();
            $table->decimal('subtotal_without_tax_discount', 10, 3)->nullable();
            $table->decimal('grandtotal_with_tax_discount', 10, 3)->nullable();
            $table->decimal('discount', 10, 3)->nullable();
            $table->decimal('promotion_discount', 10, 3)->nullable();
            $table->decimal('total_tax', 10, 3)->nullable();
            $table->text('tax_breakdown')->nullable();
            $table->decimal('total_payable', 10, 3)->nullable();
            $table->decimal('total_paid', 10, 3)->nullable();
            $table->decimal('total_due', 10, 3)->nullable();
            $table->decimal('loyalty_points_earned', 10, 3)->default(0)->nullable();
            $table->decimal('loyalty_points_redeemed', 10, 3)->default(0)->nullable();
            $table->decimal('loyalty_points_value', 10, 3)->default(0)->nullable();
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
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
        Schema::dropIfExists('sales');
    }
};
