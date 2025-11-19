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
        Schema::create('service_technicians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_service');
            $table->foreign('id_service')->references('id')->on('services')->onDelete('cascade');
            $table->unsignedBigInteger('id_technician');
            $table->foreign('id_technician')->references('id')->on('users');
            $table->decimal('percent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_technicians');
    }
};
