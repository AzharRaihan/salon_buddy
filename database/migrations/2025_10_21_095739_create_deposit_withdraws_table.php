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
        Schema::create('deposit_withdraws', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 25)->nullable();
            $table->enum('type', ['Deposit', 'Withdraw'])->nullable();
            $table->string('date', 25)->nullable();
            $table->decimal('amount', 10, 2);
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
        Schema::dropIfExists('deposit_withdraws');
    }
};
