<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_points', function (Blueprint $table) {
            $table->id();
            $table->string('series', 32);
            $table->date('date');
            $table->float('value');
            $table->timestamps();
            $table->unique(['series', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_points');
    }
};
