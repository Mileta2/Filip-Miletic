<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defense_committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('professor_id')->constrained('users')->restrictOnDelete();
            $table->string('role');
            $table->timestamps();

            $table->unique(['topic_id', 'professor_id']);
            $table->index(['topic_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defense_committee_members');
    }
};
