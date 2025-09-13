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
        Schema::create('package_usages_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sale_id')->nullable();
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('package_id')->nullable();
            $table->unsignedInteger('package_item_id')->nullable();
            $table->unsignedInteger('usages_qty')->nullable();
            $table->string('usages_date', 55)->nullable();
            $table->string('usages_time', 55)->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('branch_id')->nullable();
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
        Schema::dropIfExists('package_usages_summaries');
    }
};
