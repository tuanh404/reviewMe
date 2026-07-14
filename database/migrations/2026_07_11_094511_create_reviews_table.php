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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->string('reviewer_name')->nullable();

            $table->text('content');

            $table->integer('rating')->default(5);

            $table->integer('likes_count')->default(0);

            $table->string('session_id')->nullable();

            $table->boolean('is_approved')->default(false)->index();;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
