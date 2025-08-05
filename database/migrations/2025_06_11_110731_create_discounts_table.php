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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_amount', 16, 2); // Value of the discount
            $table->decimal('minimum_order_total', 16, 2)->default(0); // Minimum order amount to apply the discount
            $table->integer('usage_limit')->nullable(); // Limit on how many times the discount can be used
            $table->boolean('is_active')->default(true); // Whether the discount is currently active
            $table->timestamp('start_at'); // Start date of the discount
            $table->timestamp('end_at'); // End date of the discount
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
