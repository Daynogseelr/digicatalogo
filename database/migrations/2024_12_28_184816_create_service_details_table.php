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
        Schema::create('service_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_service');
            $table->foreign('id_service')->references('id')->on('services')->onDelete('cascade');
            $table->unsignedBigInteger('id_product')->nullable();
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedBigInteger('id_servicePayment')->nullable();
            $table->foreign('id_servicePayment')->references('id')->on('service_payments')->onDelete('cascade');
            $table->string('procedure')->nullable();
            $table->decimal('priceU');
            $table->integer('quantity')->default(1);
            $table->decimal('price');
            $table->string('type')->nullable();
            $table->string('mode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_details');
    }
};
