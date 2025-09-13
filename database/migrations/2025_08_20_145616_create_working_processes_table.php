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
        Schema::create('working_processes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->mediumText('description')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['Enabled', 'Disabled'])->default('Enabled')->nullable();
            $table->unsignedInteger('position')->nullable();
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
        Schema::dropIfExists('working_processes');
    }
};
