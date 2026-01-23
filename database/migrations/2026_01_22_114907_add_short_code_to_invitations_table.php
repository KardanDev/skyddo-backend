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
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('short_code', 8)->nullable()->unique()->after('token');
        });

        // Generate short codes for existing invitations
        $invitations = \DB::table('invitations')->whereNull('short_code')->get();
        foreach ($invitations as $invitation) {
            \DB::table('invitations')
                ->where('id', $invitation->id)
                ->update(['short_code' => \Illuminate\Support\Str::random(8)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('short_code');
        });
    }
};
