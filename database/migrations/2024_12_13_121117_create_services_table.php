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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_seller');
            $table->foreign('id_seller')->references('id')->on('users');
            $table->unsignedBigInteger('id_client');
            $table->foreign('id_client')->references('id')->on('users');
            $table->unsignedBigInteger('id_technician')->nullable();
            $table->foreign('id_technician')->references('id')->on('users');
            $table->unsignedBigInteger('id_category');
            $table->foreign('id_category')->references('id')->on('service_categories');
            $table->unsignedBigInteger('id_currency')->nullable();
            $table->foreign('id_currency')->references('id')->on('currencies');
            $table->string('code')->nullable();
            $table->string('ticker');
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->string('serial')->nullable();
            $table->string('description');
            $table->string('solution')->nullable();
            $table->decimal('price',30,2)->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
