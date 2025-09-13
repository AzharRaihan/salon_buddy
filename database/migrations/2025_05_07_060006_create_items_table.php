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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 55)->nullable();
            $table->string('code', 55)->unique()->nullable();
            $table->enum('type', ['Product', 'Service', 'Package'])->nullable();
            $table->string('duration', 55)->nullable();
            $table->enum('duration_type', ['Day', 'Hour', 'Minute'])->nullable();
            $table->decimal('purchase_price', 10, 3)->nullable();
            $table->decimal('last_purchase_price', 10, 3)->nullable();
            $table->decimal('last_three_purchase_avg', 10, 3)->nullable();
            $table->decimal('sale_price', 10, 3)->nullable();
            $table->decimal('profit_margin', 10, 3)->nullable();
            $table->text('description')->nullable();
            $table->string('photo', 255)->nullable();
            $table->text('tax_information')->nullable();
            $table->enum('status', ['Enable', 'Disable'])->default('Enable');
            $table->string('loyalty_point', 25)->nullable();
            $table->boolean('use_consumption')->default(false);
            $table->unsignedBigInteger('alert_stock_qty')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->enum('del_status', ['Live', 'Deleted'])->default('Live');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
