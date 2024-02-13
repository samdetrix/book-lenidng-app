<?php

use App\Models\BookCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->date('year');
            $table->string('author');
            $table->enum('availability_status', ['available', 'booked', 'not_in_stock'])->default('available');
            $table->enum('status', ['returned', 'borrowed', 'reserved'])->default('returned');
            $table->string('ISBN')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
    
            $table->foreign('category_id')->references('id')->on('book_categories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
