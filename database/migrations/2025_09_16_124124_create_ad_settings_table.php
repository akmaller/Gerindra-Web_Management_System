<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ad_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('header_enabled')->default(false);
            $table->longText('header_html')->nullable();

            $table->boolean('sidebar_enabled')->default(false);
            $table->longText('sidebar_html')->nullable();

            $table->boolean('below_post_enabled')->default(false);
            $table->longText('below_post_html')->nullable();

            $table->boolean('footer_enabled')->default(false);
            $table->longText('footer_html')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_settings');
    }
};
