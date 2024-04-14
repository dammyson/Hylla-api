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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('user_id');
            $table->text('title');
            $table->text('subtitle');
            $table->string('description');
            $table->integer('price');
            $table->boolean('favorite')->default(false);
            $table->text('product_name')->nullable();
            $table->integer('product_number')->nullable();
            $table->integer('lot_number')->nullable();
            $table->integer('barcode')->nullable();
            $table->integer('weight')->nullable();
            $table->string('dimension')->nullable();
            $table->integer('warranty_length')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
