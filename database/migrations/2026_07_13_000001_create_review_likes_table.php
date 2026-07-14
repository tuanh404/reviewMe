<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('review_id')
                ->constrained('reviews')
                ->cascadeOnDelete();

            $table->string('session_id');

            $table->timestamps();

            $table->unique(['review_id', 'session_id']);

            // Index để tối ưu truy vấn xoá/kiểm tra
            $table->index(['session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_likes');
    }
};

