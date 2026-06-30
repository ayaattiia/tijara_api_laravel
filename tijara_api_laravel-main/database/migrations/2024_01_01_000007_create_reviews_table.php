<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Reviews', function (Blueprint $table) {
            $table->id('IdReview');
            $table->unsignedBigInteger('IdUser');
            $table->string('TargetType', 50);  // deal, vendor, etc.
            $table->unsignedBigInteger('TargetId');
            $table->tinyInteger('Rating');     // 1–5
            $table->text('Comment')->nullable();
            $table->tinyInteger('Active')->default(1);
            $table->timestamp('CreatedAt')->useCurrent();

            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            // Unique review per user per target
            $table->unique(['IdUser', 'TargetType', 'TargetId']);
            $table->index(['TargetType', 'TargetId', 'Active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Reviews');
    }
};
