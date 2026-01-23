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
            // Make client_id nullable (for bot-collected quotes without client record)
            $table->foreignId('client_id')->nullable()->change();

            // Add client detail fields
            $table->string('client_name')->nullable()->after('client_id');
            $table->string('client_email')->nullable()->after('client_name');
            $table->string('client_phone')->nullable()->after('client_email');

            // Add additional_details field for coverage preferences, add-ons, etc.
            $table->json('additional_details')->nullable()->after('comparison_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Make client_id required again
            $table->foreignId('client_id')->nullable(false)->change();

            // Drop added columns
            $table->dropColumn([
                'client_name',
                'client_email',
                'client_phone',
                'additional_details',
            ]);
        });
    }
};
