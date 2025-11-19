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
        Schema::create('product_integrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_productI')->nullable();
            $table->foreign('id_productI')->references('id')->on('products');
            $table->unsignedBigInteger('id_product')->nullable();
            $table->foreign('id_product')->references('id')->on('products');
            $table->integer('quantity');
            $table->integer('is_fraction')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_integrals');
    }
};
