<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economic_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('indicator_type', 100)->index();
            $table->string('indicator_name')->index();
            $table->decimal('value', 12, 4)->nullable();
            $table->string('unit', 50)->nullable();
            $table->date('date')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['indicator_type', 'indicator_name', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_indicators');
    }
};
