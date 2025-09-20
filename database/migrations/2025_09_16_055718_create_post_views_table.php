<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            // Info pengunjung (opsional tapi berguna untuk dedup)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 64)->nullable()->index();
            $table->string('ip', 45)->nullable()->index(); // ipv6 safe
            $table->string('user_agent', 255)->nullable();

            $table->timestamp('viewed_at')->index(); // waktu view (untuk range query)
            $table->timestamps();

            // Index gabungan agar agregasi cepat
            $table->index(['post_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};
