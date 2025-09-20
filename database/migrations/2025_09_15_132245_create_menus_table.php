<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->nullOnDelete(); // submenu
            $table->string('label');
            $table->string('location')->default('header'); // header|footer (boleh tambah posisi lain nanti)
            $table->string('item_type'); // category|page|url
            $table->unsignedBigInteger('category_id')->nullable(); // opsional: jika tabel categories ada
            $table->unsignedBigInteger('page_id')->nullable();     // opsional: ke tabel pages
            $table->string('url')->nullable();                     // untuk item_type=url
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            // index untuk performa
            $table->index(['location', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
