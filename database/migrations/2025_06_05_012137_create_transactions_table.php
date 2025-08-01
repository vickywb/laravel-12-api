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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade'); // User who made the transaction
            $table->string('invoice_number')->unique()->index(); // Unique invoice number for the transaction
            $table->string('invoice_url')->nullable(); // URL to the invoice PDF or page
            $table->string('payment_method');
            $table->decimal('total_price', 16, 2);
            $table->string('payment_status')->default('unpaid')->index(); // unpaid, paid, refunded
            $table->string('transaction_status')->nullable();
            $table->string('fraud_status')->nullable();
            $table->string('va_number')->nullable();
            $table->string('bank')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable()->index(); // Nullable for unpaid transactions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};