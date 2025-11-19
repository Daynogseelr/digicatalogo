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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->integer('code2')->nullable();
            $table->integer('code3')->nullable();
            $table->string('code4')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('url1')->nullable();
            $table->string('url2')->nullable();
            $table->string('url3')->nullable();
            $table->decimal('cost',20,2);
            $table->decimal('utility',20,2);
            $table->decimal('price',20,2);
            $table->integer('stock_min')->nullable();
            $table->integer('serial');
            $table->integer('stock');
            $table->string('type');
            $table->string('code_fraction')->nullable();
            $table->string('name_fraction')->nullable();
            $table->decimal('equivalence_fraction',20,2)->nullable();
            $table->decimal('price_fraction',20,2)->nullable();
            $table->integer('status');
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
