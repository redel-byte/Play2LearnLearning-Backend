<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_user', function (Blueprint $table) {
            $table->uuid('badge_id');
            $table->uuid('user_id');
            $table->timestamp('earned_at')->useCurrent();

            $table->primary(['badge_id', 'user_id']);
            $table->foreign('badge_id')->references('id')->on('badges')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_user');
    }
};
