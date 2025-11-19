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
        Schema::create('inventory_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_inventory')->nullable();
            $table->foreign('id_inventory')->references('id')->on('inventories')->onDelete('cascade');
            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_employee')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_employees');
    }
};
