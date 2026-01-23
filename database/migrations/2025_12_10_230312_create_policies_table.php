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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurer_id')->constrained();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_number')->unique();
            $table->string('insurance_type');
            $table->text('description')->nullable();
            $table->decimal('sum_insured', 15, 2);
            $table->decimal('premium', 15, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending_renewal'])->default('active');
            $table->string('zoho_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
