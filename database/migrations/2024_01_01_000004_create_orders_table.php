<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Orders', function (Blueprint $table) {
            $table->id('IdOrder');
            $table->unsignedBigInteger('IdUser');
            $table->unsignedBigInteger('IdDeal');
            $table->timestamp('DateTimeCommand')->useCurrent();
            // Active encodes status: 1=pending, 3=confirmed, 2=delivered, 0=cancelled
            $table->tinyInteger('Active')->default(1);
            $table->string('PaymentStatus', 50)->default('unpaid'); // unpaid, paid

            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            $table->foreign('IdDeal')->references('IdDeal')->on('Deals')->onDelete('cascade');
            $table->index(['IdUser', 'Active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Orders');
    }
};
