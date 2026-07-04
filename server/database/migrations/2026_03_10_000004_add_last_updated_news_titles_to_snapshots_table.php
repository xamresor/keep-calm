<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('snapshots', function (Blueprint $table) {
            $table->json('last_updated_news_titles')->after('shipping_chokepoint')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('snapshots', function (Blueprint $table) {
            $table->dropColumn('last_updated_news_titles');
        });
    }
};
