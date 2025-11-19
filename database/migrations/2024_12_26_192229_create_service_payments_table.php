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
        Schema::create('service_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_seller');
            $table->foreign('id_seller')->references('id')->on('users');
            $table->unsignedBigInteger('id_technician');
            $table->foreign('id_technician')->references('id')->on('users');
            $table->string('dateStart')->nullable();
            $table->string('dateEnd')->nullable();
            $table->decimal('percent')->nullable();
            $table->decimal('amount')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_payments');
    }
};
