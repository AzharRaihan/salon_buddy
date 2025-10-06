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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 55)->nullable();
            $table->string('email', 55)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('date_format', 55)->nullable();
            $table->string('default_payment', 25)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('currency_position', 25)->nullable();
            $table->integer('precision')->default(2);
            $table->integer('minimum_point_to_redeem')->nullable();
            $table->decimal('loyalty_rate', 10, 2)->nullable();
            $table->enum('thousand_separator', ['.', ',', ' '])->default('.');
            $table->enum('decimal_separator', ['.', ',', ' '])->default('.');
            $table->string('item_code_start_from', 25)->nullable();
            $table->string('white_label')->nullable();
            $table->boolean('white_label_status')->default(false);
            $table->enum('collect_tax', ['Yes', 'No'])->default('No');
            $table->enum('tax_type', ['Inclusive', 'Exclusive'])->default('Exclusive');
            $table->string('tax_title', 55)->nullable();
            $table->string('tax_registration_no')->nullable();
            $table->enum('tax_is_gst', ['Yes', 'No'])->default('No');
            $table->string('state_code')->nullable();
            $table->json('tax_setting')->nullable();
            $table->string('tax_string')->nullable();
            $table->enum('print_formate', ['56mm', '80mm'])->default('56mm');
            $table->enum('over_sale', ['Yes', 'No'])->default('No');
            $table->enum('use_website', ['Yes', 'No'])->default('Yes');
            $table->string('timezone', 55)->nullable();
            $table->string('logo')->nullable();
            $table->enum('del_status', ['Live', 'Deleted'])->default('Live');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
