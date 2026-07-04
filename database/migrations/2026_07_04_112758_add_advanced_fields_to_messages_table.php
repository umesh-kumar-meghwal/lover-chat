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
    Schema::table('messages', function (Blueprint $table) {
        $table->foreignId('parent_id')->nullable()->constrained('messages')->onDelete('cascade'); // Reply mapping
        $table->string('reaction')->nullable(); // Emoji reactions
        $table->boolean('is_pinned')->default(false); // Pin state
    });
}

public function down(): void
{
    Schema::table('messages', function (Blueprint $table) {
        $table->dropColumn(['parent_id', 'reaction', 'is_pinned']);
    });
}
};
