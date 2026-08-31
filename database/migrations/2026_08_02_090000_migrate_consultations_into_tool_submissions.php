<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('consultations')->orderBy('id')->get()->each(function (object $consultation): void {
            DB::table('tool_submissions')->insert([
                'tool_slug' => 'consultor-ia',
                'name' => $consultation->name,
                'email' => $consultation->email,
                'phone' => $consultation->phone,
                'messages' => $consultation->messages,
                'status' => $consultation->status,
                'questions_asked' => $consultation->questions_asked,
                'result' => $consultation->report,
                'ai_model' => $consultation->ai_model,
                'ai_error' => $consultation->ai_error,
                'source_url' => $consultation->source_url,
                'read_at' => $consultation->read_at,
                'created_at' => $consultation->created_at,
                'updated_at' => $consultation->updated_at,
            ]);
        });

        Schema::dropIfExists('consultations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->json('initial_answers')->nullable();
            $table->json('messages')->nullable();
            $table->string('status');
            $table->unsignedInteger('questions_asked')->default(0);
            $table->json('report')->nullable();
            $table->string('ai_model')->nullable();
            $table->text('ai_error')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        DB::table('tool_submissions')->where('tool_slug', 'consultor-ia')->orderBy('id')->get()
            ->each(function (object $submission): void {
                DB::table('consultations')->insert([
                    'name' => $submission->name,
                    'email' => $submission->email,
                    'phone' => $submission->phone,
                    'initial_answers' => null,
                    'messages' => $submission->messages,
                    'status' => $submission->status,
                    'questions_asked' => $submission->questions_asked,
                    'report' => $submission->result,
                    'ai_model' => $submission->ai_model,
                    'ai_error' => $submission->ai_error,
                    'source_url' => $submission->source_url,
                    'read_at' => $submission->read_at,
                    'created_at' => $submission->created_at,
                    'updated_at' => $submission->updated_at,
                ]);
            });

        DB::table('tool_submissions')->where('tool_slug', 'consultor-ia')->delete();
    }
};
