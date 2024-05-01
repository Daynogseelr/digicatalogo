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
        Schema::create('add_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_company');
            $table->foreign('id_company')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_category');
            $table->foreign('id_category')->references('id')->on('categories')->onDelete('cascade');
            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_categories');
    }
};
