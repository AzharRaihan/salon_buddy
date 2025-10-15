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
        Schema::create('product_usage_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_usage_id')->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->decimal('quantity', 10, 3)->nullable();
            $table->decimal('unit_price', 10, 3)->nullable();
            $table->decimal('total_price', 10, 3)->nullable();
            $table->unsignedInteger('employee_id')->nullable();
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
        Schema::dropIfExists('product_usage_details');
    }
};
