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
        Schema::create('pur_fgpos_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fgpo_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id');
            $table->string('sku');
            $table->unsignedBigInteger('qty');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fgpo_id')->references('id')->on('pur_fgpos')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('product_variations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_fgpos_details');
    }
};
