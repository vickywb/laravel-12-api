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
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('discount_value', 16, 2); // Discount value for the product
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed'); // Type of discount
            $table->boolean('is_active')->default(true); // Whether the discount is currently active
            $table->timestamp('start_at'); // Start time of the discount
            $table->timestamp('end_at'); // End time of the discount
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_discounts');
    }
};
