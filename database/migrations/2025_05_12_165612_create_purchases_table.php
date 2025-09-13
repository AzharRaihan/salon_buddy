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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 55)->nullable();
            $table->string('supplier_invoice_no', 55)->nullable();
            $table->unsignedInteger('supplier_id')->nullable();
            $table->string('date', 25)->nullable(); 
            $table->decimal('grand_total', 10, 3)->nullable();
            $table->decimal('paid_amount', 10, 3)->nullable();
            $table->decimal('due_amount', 10, 3)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->string('note', 255)->nullable();
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
        Schema::dropIfExists('purchases');
    }
};
