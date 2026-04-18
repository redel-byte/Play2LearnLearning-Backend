<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('quiz_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('host_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('title')->nullable();
            $table->string('join_code', 12)->nullable()->unique();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();

            $table->index(['quiz_id', 'status']);
            $table->index(['host_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_sessions');
    }
};
