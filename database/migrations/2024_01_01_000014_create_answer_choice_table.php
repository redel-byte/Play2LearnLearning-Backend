<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answer_choice', function (Blueprint $table) {
            $table->uuid('answer_id');
            $table->uuid('choice_id');

            $table->primary(['answer_id', 'choice_id']);
            $table->foreign('answer_id')->references('id')->on('answers')->cascadeOnDelete();
            $table->foreign('choice_id')->references('id')->on('choices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_choice');
    }
};
