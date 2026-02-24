<?php

use App\Application\Shared\Constants\LevelsConstants;
use App\Application\Shared\Constants\MentorPersonaConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();

            $table->enum('target_level', LevelsConstants::LIST)
                ->default(LevelsConstants::UNSETTED);
            $table->enum('current_level', LevelsConstants::LIST)
                ->default(LevelsConstants::UNSETTED);

            $table->string('how_to_call_me')->nullable();
            $table->boolean('use_name_to_call_me')->default(false);

            $table->enum('mentor_persona', MentorPersonaConstants::LIST)
                ->default(MentorPersonaConstants::NEUTRAL);
            $table->timestamps();

            $table->index(['user_id', 'track_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
