<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artist_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('bio')->nullable();
            $table->json('category');
            $table->json('youtube_links')->nullable();
            $table->json('attachments')->nullable();
            $table->string('price_rate')->nullable();
            $table->string('offfer_point')->default(50);
            $table->string('location')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('managers')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_profiles');
    }
};
