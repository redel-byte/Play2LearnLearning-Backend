<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('question_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->text('label');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['question_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
