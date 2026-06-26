<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_ads', function (Blueprint $table) {
            $table->id('IdWish');
            $table->foreignId('IdUser')->constrained('users')->onDelete('cascade');
            $table->foreignId('IdAd')->constrained('ads', 'IdAd')->onDelete('cascade');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->unique(['IdUser', 'IdAd']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_ads');
    }
};