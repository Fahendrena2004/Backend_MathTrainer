<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->integer('display_order')->unique();
            $table->text('description')->nullable();
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->text('description');
            $table->integer('display_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_level_id')->nullable()->constrained();
            $table->string('role')->default('student');
            $table->integer('points_total')->default(0);
        });

        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_level_id')->constrained()->restrictOnDelete();
            $table->string('title', 120);
            $table->text('statement');
            $table->string('exercise_type', 20);
            $table->text('options')->nullable();
            $table->text('expected_answer');
            $table->text('correction');
            $table->integer('points_max');
            $table->integer('difficulty')->default(1);
            $table->string('chapter', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('learning_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->integer('score_total')->default(0);
        });

        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_session_id')->nullable()->constrained()->nullOnDelete();
            $table->text('answer');
            $table->integer('score')->default(0);
            $table->boolean('success')->default(false);
            $table->integer('time_spent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->text('unlock_condition')->nullable();
            $table->integer('required_success_count');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at')->useCurrent();
            $table->unique(['user_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('learning_sessions');
        Schema::dropIfExists('exercises');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_level_id']);
            $table->dropColumn(['school_level_id', 'role', 'points_total']);
        });

        Schema::dropIfExists('topics');
        Schema::dropIfExists('school_levels');
    }
};
