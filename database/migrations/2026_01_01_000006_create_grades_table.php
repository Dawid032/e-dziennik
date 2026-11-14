<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            // Autor wpisu: nauczyciel albo administrator.
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('value');
            $table->unsignedTinyInteger('weight');
            $table->string('category', 30);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
