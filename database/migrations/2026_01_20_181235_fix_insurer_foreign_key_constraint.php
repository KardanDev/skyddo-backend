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
        Schema::table('policies', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['insurer_id']);

            // Recreate the foreign key with restrictOnDelete
            $table->foreign('insurer_id')
                ->references('id')
                ->on('insurers')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            // Drop the restrictOnDelete foreign key
            $table->dropForeign(['insurer_id']);

            // Restore the original cascadeOnDelete behavior
            $table->foreign('insurer_id')
                ->references('id')
                ->on('insurers')
                ->cascadeOnDelete();
        });
    }
};
