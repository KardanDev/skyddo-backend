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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('calculation_type', ['percentage', 'fixed', 'tiered'])->default('percentage');
            $table->decimal('rate', 8, 4)->nullable(); // For percentage (e.g., 0.0250 = 2.5%) or fixed amount
            $table->decimal('minimum_amount', 15, 2)->nullable(); // Minimum insurance cost
            $table->decimal('maximum_amount', 15, 2)->nullable(); // Maximum insurance cost
            $table->json('tiered_rates')->nullable(); // For complex tiered pricing based on value ranges
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority rules are applied first
            $table->timestamps();

            // Unique constraint: one rule per insurance type + vehicle type combination
            $table->unique(['insurance_type_id', 'vehicle_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
