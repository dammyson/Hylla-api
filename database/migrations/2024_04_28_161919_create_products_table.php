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
            $table->unsignedBigInteger('user_id');
            $table->boolean('archived')->default(false);
            $table->boolean('favorite')->default(false);
            $table->string('code');
            $table->string('barcode_number');
            $table->text('barcode_formats')->nullable();
            $table->string('mpn')->nullable();
            $table->string('model')->nullable();
            $table->string('asin')->nullable();
            $table->string('title');
            $table->string('category');
            $table->string('manufacturer');
            $table->string('serial_number')->nullable();
            $table->string('weight')->nullable();
            $table->string('dimension')->nullable();
            $table->string('warranty_length')->nullable();
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
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_product_id_foreign');
        });

        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign('images_product_id_foreign');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign('stores_product_id_foreign');
        });

        Schema::table('category_product', function (Blueprint $table) {
            $table->dropForeign('category_product_product_id_foreign');
        });
        Schema::dropIfExists('products');
    }
};
