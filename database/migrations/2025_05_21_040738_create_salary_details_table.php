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
        Schema::create('salary_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('salary_id')->nullable();
            $table->unsignedInteger('employee_id')->nullable();
            $table->decimal('salary_amount', 10, 3);
            $table->decimal('overtime_rate', 10, 3);
            $table->decimal('overtime_hour', 10, 3);
            $table->decimal('additional_amount', 10, 3);
            $table->decimal('deduction_amount', 10, 3);
            $table->decimal('absent_day', 10, 3);
            $table->decimal('absent_day_amount', 10, 3);
            $table->decimal('tips', 10, 3);
            $table->decimal('advance_taken', 10, 3);
            $table->decimal('net_salary', 10, 3);
            $table->string('note', 255)->nullable();
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
        Schema::dropIfExists('salary_details');
    }
};
