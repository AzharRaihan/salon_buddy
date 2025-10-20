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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 55)->nullable();
            $table->string('account_type', 25)->nullable();
            $table->text('description', 255)->nullable();
            $table->string('payment_method_icon', 255)->nullable();
            $table->decimal('current_balance', 10, 3)->default(0);
            $table->enum('status', ['Enable', 'Disable'])->default('Enable');
            $table->enum('use_in_website', ['Yes', 'No'])->default('No');
            $table->enum('is_deletable', ['Yes', 'No'])->default('Yes');

            // Bank specific fields
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('branch', 100)->nullable();
            
            // Payment gateway fields
            $table->string('client_id', 255)->nullable();
            $table->string('api_key', 255)->nullable();
            $table->string('secret_key', 255)->nullable();
            $table->enum('mode', ['Sandbox', 'Live'])->nullable();
            
            // Paytm specific fields
            $table->string('merchant_id', 255)->nullable();
            $table->string('merchant_key', 255)->nullable();



            $table->integer('sort_id')->default(0);
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
        Schema::dropIfExists('payment_methods');
    }
};
