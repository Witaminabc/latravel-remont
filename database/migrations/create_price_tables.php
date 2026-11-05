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
        // database/migrations/xxxx_create_price_tables.php
        Schema::create('price_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('📋');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('price_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_section_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('price_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('unit');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_sections');
        Schema::dropIfExists('price_categories');
        Schema::dropIfExists('price_items');
    }
};
