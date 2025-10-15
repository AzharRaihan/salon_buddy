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
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('email', 55)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->json('languages')->nullable();
            $table->json('social_media')->nullable();

            // Testimonial
            $table->string('testimonial_title', 30)->nullable();
            $table->string('testimonial_heading', 100)->nullable();
            $table->string('testimonial_image', 55)->nullable();

            // Common Banner
            $table->string('common_banner_image', 55)->nullable();

            // Login Image
            $table->string('login_image', 55)->nullable();
            
            // Google Map
            $table->text('google_map_url')->nullable();

            // Opening Closing Day, Hour
            $table->string('open_day_start', 25)->nullable();
            $table->string('open_day_end', 25)->nullable();
            $table->string('open_day_start_time', 25)->nullable();
            $table->string('open_day_end_time', 25)->nullable();

            // White Label website
            $table->string('footer_copyright', 255)->nullable();
            $table->string('footer_mini_description', 255)->nullable();
            $table->string('header_logo', 100)->nullable();
            $table->string('footer_logo', 100)->nullable();

            // Website Title and Favicon
            $table->string('website_title', 255)->nullable();
            $table->string('favicon', 100)->nullable();

            // Privacy Policy and Terms & Conditions
            $table->text('privacy_policy')->nullable();
            $table->text('terms_and_conditions')->nullable();
            

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
        Schema::dropIfExists('website_settings');
    }
};
