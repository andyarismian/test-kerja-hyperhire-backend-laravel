<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('person_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liker_id')->constrained('people')->cascadeOnDelete(); // Person who likes
            $table->foreignId('liked_id')->constrained('people')->cascadeOnDelete(); // Person being liked
            $table->boolean('is_like')->default(true); // true = like, false = dislike
            $table->timestamps();
            $table->unique(['liker_id', 'liked_id']); // Prevent duplicate likes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_likes');
    }
};
