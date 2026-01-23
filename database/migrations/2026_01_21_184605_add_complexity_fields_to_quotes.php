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
            $table->enum('complexity_level', ['simple', 'moderate', 'complex'])
                ->default('simple')->after('status');
            $table->json('complexity_factors')->nullable()->after('complexity_level');
            $table->boolean('requires_agent_review')->default(false)->after('complexity_factors');
            $table->timestamp('agent_assigned_at')->nullable()->after('requires_agent_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'complexity_level',
                'complexity_factors',
                'requires_agent_review',
                'agent_assigned_at',
            ]);
        });
    }
};
