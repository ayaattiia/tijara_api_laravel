<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Payments', function (Blueprint $table) {
            $table->id('IdPayment');
            $table->unsignedBigInteger('IdUser');
            $table->unsignedBigInteger('IdOrder')->nullable();
            $table->decimal('Amount', 18, 3);
            $table->string('Method', 50);           // cash, card, virement, etc.
            $table->string('Status', 50)->default('pending'); // pending, paid, refunded, failed
            $table->string('Reference', 50)->nullable()->unique();
            $table->string('TransactionId', 50)->nullable()->unique();
            $table->timestamp('PaidAt')->nullable();

            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            $table->foreign('IdOrder')->references('IdOrder')->on('Orders')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Payments');
    }
};
