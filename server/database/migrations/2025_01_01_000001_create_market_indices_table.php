<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_indices', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 50)->index();
            $table->string('name')->nullable();
            $table->string('market_status', 50)->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->decimal('change', 12, 2)->nullable();
            $table->decimal('change_percent', 8, 2)->nullable();
            $table->decimal('open', 12, 2)->nullable();
            $table->decimal('high', 12, 2)->nullable();
            $table->decimal('low', 12, 2)->nullable();
            $table->decimal('close', 12, 2)->nullable();
            $table->decimal('previous_close', 12, 2)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->timestamp('updated_at_source')->nullable();
            $table->date('snapshot_date')->index();
            $table->timestamps();

            $table->unique(['ticker', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_indices');
    }
};
