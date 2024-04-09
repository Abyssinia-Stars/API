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
        Schema::create('txn_histories', function (Blueprint $table) {
            $table->id();
            $table->string('tx_ref');
            $table->double('amount', 15, 4);
            $table->unsignedBigInteger('from');
            $table->foreign('from')->references('id')->on('users');
            $table->unsignedBigInteger('to');
            $table->foreign('to')->references('id')->on('users');
            $table->string('reason')->nullable();
            $table->enum('type', ['deposit', 'withdrawal', 'payment']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('txn_histories');
    }
};
