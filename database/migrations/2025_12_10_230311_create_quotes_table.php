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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('quote_number')->unique();
            $table->string('insurance_type');
            $table->text('description')->nullable();
            $table->decimal('sum_insured', 15, 2)->nullable();
            $table->decimal('premium', 15, 2)->nullable();
            $table->enum('status', ['pending', 'sent_to_insurer', 'received', 'approved', 'rejected', 'expired'])->default('pending');
            $table->date('valid_until')->nullable();
            $table->string('zoho_quote_id')->nullable()->index();
            $table->json('comparison_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
