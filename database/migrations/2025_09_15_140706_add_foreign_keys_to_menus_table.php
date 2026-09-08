<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'category_id')) {
                $table->foreign('category_id', 'fk_menus_category')
                    ->references('id')->on('categories')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('menus', 'page_id')) {
                $table->foreign('page_id', 'fk_menus_page')
                    ->references('id')->on('pages')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign('fk_menus_category');
            $table->dropForeign('fk_menus_page');
        });
    }
};
