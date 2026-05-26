<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();
        });

        Schema::create('provider_logos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::create('question_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_set_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->text('explanation')->nullable();
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['question_set_id', 'is_active', 'sort_order']);
        });

        Schema::create('answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('label', 1);
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->text('explanation')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['question_id', 'label']);
            $table->index(['question_id', 'is_correct']);
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('institution_role')->nullable();
            $table->boolean('consent_accepted')->default(false);
            $table->timestamps();

            $table->index('email');
            $table->index('phone');
        });

        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_set_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('correct_answers_count')->default(0);
            $table->integer('total_time_seconds')->nullable();
            $table->boolean('duplicate_flag')->default(false);
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['participant_id', 'question_set_id']);
            $table->index('question_set_id');
            $table->index('correct_answers_count');
            $table->index('total_time_seconds');
        });

        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('answer_option_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_correct');
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('answer_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_sets');
        Schema::dropIfExists('provider_logos');
        Schema::dropIfExists('settings');
    }
};
