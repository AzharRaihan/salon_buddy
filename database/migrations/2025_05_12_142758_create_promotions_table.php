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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->string('start_date', 25)->nullable();
            $table->string('end_date', 25)->nullable(); 
            $table->enum('type', ['Discount','Free Item'])->default('Discount');
            $table->unsignedInteger('buy_item_id')->nullable();
            $table->integer('buy_qty')->nullable();
            $table->unsignedInteger('get_item_id')->nullable();
            $table->integer('get_qty')->nullable();
            $table->unsignedInteger('discount_item_id')->nullable();
            $table->decimal('discount', 10, 3)->nullable();
            $table->enum('discount_type', ['Percentage', 'Fixed'])->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('promotions');
    }
};
