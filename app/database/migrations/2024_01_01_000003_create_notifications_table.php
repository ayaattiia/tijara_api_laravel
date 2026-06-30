<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Notifications', function (Blueprint $table) {
            $table->id('IdNotification');
            $table->unsignedBigInteger('IdUser');
            $table->string('Type', 50)->nullable();       // new_product, order_update, follow, new_order, etc.
            $table->string('Title', 255)->nullable();
            $table->text('Message')->nullable();
            $table->string('Link', 500)->nullable();
            $table->tinyInteger('IsRead')->default(0);
            $table->unsignedBigInteger('IdReference')->nullable(); // polymorphic reference ID
            $table->timestamp('CreatedAt')->useCurrent();

            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            $table->index(['IdUser', 'IsRead']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Notifications');
    }
};
