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
        Schema::table('quotes', function (Blueprint $table) {
            // Add foreign keys to new tables
            $table->foreignId('insurance_type_id')->nullable()->after('insurer_id')->constrained()->nullOnDelete();
            $table->foreignId('vehicle_type_id')->nullable()->after('insurance_type_id')->constrained()->nullOnDelete();

            // Add vehicle/asset value field
            $table->decimal('asset_value', 15, 2)->nullable()->after('vehicle_type_id');

            // Add calculated insurance cost (replaces premium)
            $table->decimal('calculated_cost', 15, 2)->nullable()->after('asset_value');

            // Make insurance_type string nullable (we'll use insurance_type_id instead)
            $table->string('insurance_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['insurance_type_id']);
            $table->dropForeign(['vehicle_type_id']);
            $table->dropColumn(['insurance_type_id', 'vehicle_type_id', 'asset_value', 'calculated_cost']);
            $table->string('insurance_type')->nullable(false)->change();
        });
    }
};
