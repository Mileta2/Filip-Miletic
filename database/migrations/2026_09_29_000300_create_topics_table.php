<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->text('description');
            $table->string('type')->index();
            $table->string('status')->default('available')->index();
            $table->foreignId('mentor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('student_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $table->string('pdf_path')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->date('defended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
