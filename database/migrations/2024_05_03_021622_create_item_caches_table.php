<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. docker-compose run --rm artisan migrate:refresh --path=/database/migrations/2024_05_03_021622_create_item_caches_table.php
     */
    public function up(): void
    {
        Schema::create('item_caches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->json('details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_caches');
    }
};
