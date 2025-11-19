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
        Schema::create('closures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_seller');
            $table->foreign('id_seller')->references('id')->on('users')->onDelete('cascade');
            $table->integer('id_bill_first');
            $table->integer('id_bill_last');
            $table->integer('id_bill_payment_first');
            $table->integer('id_bill_payment_last');
            $table->integer('id_repayment_first');
            $table->integer('id_repayment_last');
            $table->decimal('bill_amount', 30, 2);
            $table->decimal('payment_amount', 30, 2);
            $table->decimal('repayment_amount', 30, 2);
            $table->decimal('small_box_amount', 30, 2);
            $table->string('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closures');
    }
};
