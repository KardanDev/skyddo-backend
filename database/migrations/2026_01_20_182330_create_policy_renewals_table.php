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
        Schema::create('policy_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_policy_id')->constrained('policies')->cascadeOnDelete();
            $table->foreignId('renewed_policy_id')->constrained('policies')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('original_policy_id');
            $table->index('renewed_policy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_renewals');
    }
};
