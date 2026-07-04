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
        // Image store karne ke liye nullable string column
        $table->string('image')->nullable()->after('message');
        $table->text('message')->nullable()->change(); // Message empty bhi ho sake agar sirf photo bhejni ho
    });
}

public function down(): void
{
    Schema::table('messages', function (Blueprint $table) {
        $table->dropColumn('image');
    });
}
};
