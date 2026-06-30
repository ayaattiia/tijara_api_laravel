<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Invoices', function (Blueprint $table) {
            $table->id('IdInvoice');
            $table->string('Number', 50)->unique();
            $table->unsignedBigInteger('IdOrder');
            $table->unsignedBigInteger('IdUser');   // buyer
            $table->unsignedBigInteger('IdVendor'); // seller
            $table->decimal('Subtotal', 18, 3)->default(0);
            $table->decimal('Tax', 18, 3)->default(0);
            $table->decimal('DeliveryFee', 18, 3)->default(0);
            $table->decimal('Total', 18, 3)->default(0);
            $table->string('Status', 50)->default('unpaid'); // unpaid, paid, cancelled
            $table->timestamp('IssuedAt')->useCurrent();
            $table->timestamp('PaidAt')->nullable();

            $table->foreign('IdOrder')->references('IdOrder')->on('Orders')->onDelete('cascade');
            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            $table->foreign('IdVendor')->references('IdUser')->on('Users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Invoices');
    }
};
