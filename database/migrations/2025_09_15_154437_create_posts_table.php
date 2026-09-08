<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // author
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();

            $table->string('status')->default('draft'); // draft|scheduled|published
            $table->timestamp('published_at')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);

            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();

            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
