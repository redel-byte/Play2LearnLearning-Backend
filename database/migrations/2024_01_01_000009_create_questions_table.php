<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('quiz_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('type', 32);
            $table->text('prompt');
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['quiz_id', 'position']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
