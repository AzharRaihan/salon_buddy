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
        Schema::create('item_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_relation_id')->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->decimal('consumption', 10, 3)->nullable();
            $table->unsignedInteger('unit_id')->nullable();
            $table->decimal('conversion_rate', 10, 3)->nullable();
            $table->decimal('cost_per_unit', 10, 3)->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('price', 10, 3)->nullable();
            $table->string('discount', 55)->nullable();
            $table->decimal('total_price', 10, 3)->nullable();
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
        Schema::dropIfExists('item_details');
    }
};
