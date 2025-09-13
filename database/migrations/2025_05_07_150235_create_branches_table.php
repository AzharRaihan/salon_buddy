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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name', 55)->nullable();
            $table->string('branch_code', 10)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 55)->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('open_day_start', 25)->nullable();
            $table->string('open_day_end', 25)->nullable();
            $table->string('open_day_start_time', 25)->nullable();
            $table->string('open_day_end_time', 25)->nullable();
            $table->enum('active_status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('branches');
    }
};
