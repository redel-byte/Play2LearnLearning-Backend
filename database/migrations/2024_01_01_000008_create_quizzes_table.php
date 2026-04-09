<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('creator_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('access_code', 12)->nullable()->unique();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->unsignedTinyInteger('pass_percentage')->default(70);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['creator_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
