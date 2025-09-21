<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('module_enabled')->default(false);
            $table->string('endpoint')->nullable();
            $table->string('default_title')->nullable();
            $table->string('chat_button_text')->nullable();
            $table->enum('chat_button_position', ['bottom-right', 'bottom-left'])->default('bottom-right');
            $table->unsignedInteger('request_timeout')->default(180);
            $table->string('auth_type')->default('custom_header');
            $table->string('auth_header_key')->nullable();
            $table->text('auth_header_value')->nullable();
            $table->boolean('auth_header_as_bearer')->default(false);
            $table->string('default_avatar_path')->nullable();
            $table->string('history_storage')->default('ttl');
            $table->unsignedInteger('history_ttl_minutes')->nullable();
            $table->boolean('auto_inject_enabled')->default(false);
            $table->boolean('auto_inject_sitewide')->default(false);
            $table->string('auto_inject_position')->default('below_content');
            $table->json('auto_inject_pages')->nullable();
            $table->json('auto_inject_posts')->nullable();
            $table->json('extra_options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
    }
};
