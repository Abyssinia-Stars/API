<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('max_users'); // Maximum number of users (default 1)
            $table->enum('role', ['artist', 'manager']); // Role of the user
            $table->enum('type', ['free_trail', 'basic', 'premium'])->default('free_trail'); // Type of plan (free or paid)
            $table->integer('duration'); // Duration in months
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
