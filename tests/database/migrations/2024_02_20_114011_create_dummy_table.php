<?php

use Alexkramse\LaravelTranslatableTable\Tests\Models\Dummy;
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
        Schema::create('dummies', function (Blueprint $table) {
            $table->id();
            $table->string('user_data');
            $table->timestamps();
        });

        Schema::create('dummy_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Dummy::class);
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
        Schema::dropIfExists('dummies');
        Schema::dropIfExists('dummy_translations');
    }
};
