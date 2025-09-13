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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 55)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 55)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('photo', 55)->nullable();
            $table->enum('same_or_diff_state', ['Same', 'Different'])->default('Same');
            $table->string('gst_number', 55)->nullable();
            $table->string('date_of_birth', 25)->nullable();
            $table->string('date_of_anniversary', 25)->nullable();
            $table->decimal('loyalty_points', 10, 3)->default(0.000);
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
        Schema::dropIfExists('customers');
    }
};
