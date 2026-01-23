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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // For grouping conversations
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete(); // Link to authenticated client
            $table->string('sender')->default('client'); // 'client' or 'bot'
            $table->text('message');
            $table->json('metadata')->nullable(); // Store additional data (intent, entities, action results)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
