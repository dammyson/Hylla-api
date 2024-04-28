<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.  docker-compose run --rm artisan migrate:refresh --path=/database/migrations/2024_04_28_161919_create_products_table.php
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('barcode_number')->unique();
            $table->text('barcode_formats')->nullable();
            $table->string('mpn')->nullable();
            $table->string('model')->nullable();
            $table->string('asin')->nullable();
            $table->string('title');
            $table->string('category');
            $table->string('manufacturer');
            $table->string('brand');
            $table->text('ingredients')->nullable();
            $table->text('nutrition_facts')->nullable();
            $table->string('size')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('last_update');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
