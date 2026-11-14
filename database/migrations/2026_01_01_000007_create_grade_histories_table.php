<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->unsignedTinyInteger('old_value')->nullable();
            $table->unsignedTinyInteger('new_value')->nullable();
            $table->unsignedTinyInteger('old_weight')->nullable();
            $table->unsignedTinyInteger('new_weight')->nullable();
            $table->string('old_category', 30)->nullable();
            $table->string('new_category', 30)->nullable();
            $table->string('old_description')->nullable();
            $table->string('new_description')->nullable();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_histories');
    }
};
