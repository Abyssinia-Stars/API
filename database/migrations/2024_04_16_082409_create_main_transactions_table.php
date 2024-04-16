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
        Schema::create('main_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('artist_id')->constrained('users');
            $table->decimal('full_amount', 8, 2);
            $table->decimal('our_amount', 8, 2);
            $table->decimal('after_tax', 8, 2);
            $table->decimal('tax_percentage');
            $table->decimal('net_amount', 8, 2);
            $table->decimal('percentage');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_transactions');
    }
};
