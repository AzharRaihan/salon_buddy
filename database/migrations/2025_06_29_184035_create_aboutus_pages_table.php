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
        Schema::create('aboutus_pages', function (Blueprint $table) {
            $table->id();

            // Section 1
            $table->string('section_1_heading', 100)->nullable();
            $table->text('section_1_description')->nullable();
            $table->string('section_1_btn_link', 250)->nullable();
            $table->integer('total_services_count')->default(25);
            $table->integer('total_staff_count')->default(100);
            $table->integer('total_customers_count')->default(5000);
            $table->integer('total_done_services_count')->default(10000);
            $table->string('section_1_image', 100)->nullable();
            $table->string('section_1_image_2', 100)->nullable();
            $table->string('section_1_experience', 100)->nullable();
            
            // Section 2
            $table->string('section_play_title', 100)->nullable();
            $table->string('section_play_link', 250)->nullable();
            $table->string('section_play_image', 100)->nullable();

            // Section 3
            $table->string('section_discover_heading', 100)->nullable();
            $table->string('section_discover_description', 250)->nullable();
            $table->string('section_discover_bg_image', 100)->nullable();
            $table->string('section_discover_front_image', 100)->nullable();

            // Section Discover 1
            $table->string('section_discover_item_1_heading', 100)->nullable();
            $table->string('section_discover_item_1_description', 250)->nullable();
            $table->string('section_discover_item_1_image', 100)->nullable();
            
            // Section Discover 2
            $table->string('section_discover_item_2_heading', 100)->nullable();
            $table->string('section_discover_item_2_description', 250)->nullable();
            $table->string('section_discover_item_2_image', 100)->nullable();
            
            // Section Discover 3
            $table->string('section_discover_item_3_heading', 100)->nullable();
            $table->string('section_discover_item_3_description', 250)->nullable();
            $table->string('section_discover_item_3_image', 100)->nullable();

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
        Schema::dropIfExists('aboutus_pages');
    }
};
