<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate existing data
        DB::statement('INSERT INTO category_item (item_id, category_id, created_at, updated_at) SELECT id, category_id, NOW(), NOW() FROM items WHERE category_id IS NOT NULL');

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
        });

        // Restore some data (just picks one category if multiple existed)
        DB::statement('UPDATE items i JOIN category_item ci ON i.id = ci.item_id SET i.category_id = ci.category_id');

        Schema::dropIfExists('category_item');
    }
};
