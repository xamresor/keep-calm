<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_news', function (Blueprint $table) {
            $table->id();
            $table->string('article_id')->unique();
            $table->string('publisher')->nullable();
            $table->string('title');
            $table->string('author')->nullable();
            $table->timestamp('published_utc')->index();
            $table->text('article_url')->nullable();
            $table->text('image_url')->nullable();
            $table->text('description')->nullable();
            $table->json('keywords')->nullable();
            $table->json('tickers')->nullable();
            $table->json('insights')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_news');
    }
};
