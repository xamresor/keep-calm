<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 50)->index();
            $table->string('name')->nullable();
            $table->string('market', 50)->nullable();
            $table->string('locale', 10)->nullable();
            $table->string('primary_exchange', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('currency_name', 50)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('change', 12, 2)->nullable();
            $table->decimal('change_percent', 8, 2)->nullable();
            $table->decimal('open', 12, 2)->nullable();
            $table->decimal('high', 12, 2)->nullable();
            $table->decimal('low', 12, 2)->nullable();
            $table->decimal('close', 12, 2)->nullable();
            $table->decimal('previous_close', 12, 2)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('vwap', 12, 2)->nullable();
            $table->timestamp('updated_at_source')->nullable();
            $table->date('snapshot_date')->index();
            $table->timestamps();

            $table->unique(['ticker', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_stocks');
    }
};
