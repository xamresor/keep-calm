<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('liquidity');
            $table->unsignedTinyInteger('logistics');
            $table->unsignedTinyInteger('legitimacy');
            $table->decimal('overall', 5, 1);
            $table->json('liquidity_history')->nullable();
            $table->json('logistics_history')->nullable();
            $table->json('legitimacy_history')->nullable();
            $table->json('overall_history')->nullable();
            $table->json('family_safety_note')->nullable();
            $table->timestamps();

            $table->index(['snapshot_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
