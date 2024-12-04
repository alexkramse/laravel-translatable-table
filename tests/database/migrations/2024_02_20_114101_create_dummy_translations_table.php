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
        Schema::create('dummy_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Article::class);
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('locale', 5)->nullable(false)->index();
            $table->timestamps();
            $table->unique(['dummy_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dummy_translations');
    }
};
