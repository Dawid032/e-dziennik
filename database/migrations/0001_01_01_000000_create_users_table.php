<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolumna class_id dochodzi osobną migracją, bo users i school_classes wskazują na siebie nawzajem.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 60);
            $table->string('last_name', 80);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'teacher', 'student', 'parent']);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
