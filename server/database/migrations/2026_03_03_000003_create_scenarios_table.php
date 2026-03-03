<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('description')->nullable();
            $table->string('when_visible')->nullable();
            $table->string('earliest_date')->nullable();
            $table->unsignedTinyInteger('probability_percent')->nullable();
            $table->timestamps();

            $table->index('snapshot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};
