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
        Schema::create('book_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('user_id');
            $table->date('reserved');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->integer('late_days')->nullable();
            $table->decimal('penalty_amount', 8, 2)->nullable();
            $table->string('order_number')->nullable();
        
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_transactions');
    }
};
