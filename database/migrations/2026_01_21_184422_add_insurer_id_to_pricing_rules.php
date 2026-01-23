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
        Schema::table('pricing_rules', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['insurance_type_id', 'vehicle_type_id']);

            // Add insurer_id field
            $table->foreignId('insurer_id')->nullable()->after('insurance_type_id')
                ->constrained()->onDelete('cascade');

            // Add price_multiplier field
            $table->decimal('price_multiplier', 5, 2)->default(1.00)->after('rate');

            // Add new unique constraint including insurer_id and priority
            $table->unique(['insurance_type_id', 'vehicle_type_id', 'insurer_id', 'priority'], 'pricing_rules_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('pricing_rules_unique');

            // Drop new columns
            $table->dropForeign(['insurer_id']);
            $table->dropColumn(['insurer_id', 'price_multiplier']);

            // Restore original unique constraint
            $table->unique(['insurance_type_id', 'vehicle_type_id']);
        });
    }
};
