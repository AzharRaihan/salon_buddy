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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 55);
            $table->string('email', 55)->unique();
            $table->string('phone', 30)->unique()->nullable();
            $table->integer('role')->default(2)->comment('1: Admin, 2: User');
            $table->decimal('salary', 10, 3)->nullable();
            $table->string('commission', 25)->nullable();
            $table->string('branch_id', 255)->nullable();
            $table->string('service_id', 255)->nullable();
            $table->decimal('overtime_hour_rate', 10, 3)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('photo')->nullable();
            $table->mediumText('question')->nullable();
            $table->mediumText('answer')->nullable();
            $table->string('status')->default('Active')->comment('Active, Inactive');
            $table->boolean('is_first_login')->default(false);
            $table->string('language')->default('en');

            // Social Media
            $table->string('designation', 55)->nullable();
            $table->json('social_media')->nullable();

            // Team details
            $table->string('age', 10)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('qualification', 255)->nullable();
            $table->string('experience', 25)->nullable();
            $table->mediumText('description')->nullable();

            // Socialite info
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('github_id')->nullable();
            $table->string('avatar')->nullable();

            $table->integer('company_id')->default(1);
            $table->enum('will_login', ['Yes', 'No'])->default('Yes');
            $table->enum('del_status', ['Live', 'Deleted'])->default('Live');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
