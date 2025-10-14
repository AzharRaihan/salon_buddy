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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('saturday_start', 25)->nullable();
            $table->string('saturday_end', 25)->nullable();
            $table->enum('saturday_is_holiday', ['Yes', 'No'])->default('No');
            $table->string('sunday_start', 25)->nullable();
            $table->string('sunday_end', 25)->nullable();
            $table->enum('sunday_is_holiday', ['Yes', 'No'])->default('No');
            $table->string('monday_start', 25)->nullable();
            $table->string('monday_end', 25)->nullable();
            $table->enum('monday_is_holiday', ['Yes', 'No'])->default('No');
            $table->string('tuesday_start', 25)->nullable();
            $table->string('tuesday_end', 25)->nullable();
            $table->enum('tuesday_is_holiday', ['Yes', 'No'])->default('No');
            $table->string('wednesday_start', 25)->nullable();
            $table->string('wednesday_end', 25)->nullable();
            $table->enum('wednesday_is_holiday', ['Yes', 'No'])->default('No');
            $table->string('thursday_start', 25)->nullable();
            $table->string('thursday_end', 25)->nullable();
            $table->enum('thursday_is_holiday', ['Yes', 'No'])->default('No');
            $table->string('friday_start', 25)->nullable();
            $table->string('friday_end', 25)->nullable();
            $table->enum('friday_is_holiday', ['Yes', 'No'])->default('No');
            $table->text('holiday_message')->nullable();
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
        Schema::dropIfExists('holidays');
    }
};
