<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PreInvoices', function (Blueprint $table) {
            $table->id('IdPreInvoice');
            $table->string('Number', 50)->unique();
            $table->unsignedBigInteger('IdOrder');
            $table->unsignedBigInteger('IdUser');              // buyer
            $table->unsignedBigInteger('IdVendor');            // seller
            $table->string('EntrepriseName', 255)->nullable(); // vendor company name
            $table->string('PlatformName', 255)->nullable();   // platform name
            $table->string('ClientName', 255)->nullable();
            $table->string('ClientEmail', 255)->nullable();
            $table->string('ClientPhone', 50)->nullable();
            $table->string('ClientAddress', 255)->nullable();
            $table->decimal('Subtotal', 18, 3)->default(0);
            $table->decimal('Tax', 18, 3)->default(0);
            $table->decimal('DeliveryFee', 18, 3)->default(0);
            $table->decimal('Discount', 18, 3)->default(0);
            $table->decimal('Total', 18, 3)->default(0);
            // Status: draft | pending | approved | rejected | converted
            $table->string('Status', 50)->default('draft');
            $table->text('Notes')->nullable();
            $table->string('RejectionReason', 500)->nullable();
            $table->unsignedBigInteger('ConvertedToInvoice')->nullable(); // IdInvoice after conversion
            $table->timestamp('IssuedAt')->useCurrent();
            $table->timestamp('ApprovedAt')->nullable();
            $table->timestamp('RejectedAt')->nullable();
            $table->timestamp('ConvertedAt')->nullable();
            $table->timestamp('UpdatedAt')->nullable();

            $table->foreign('IdOrder')->references('IdOrder')->on('Orders')->onDelete('cascade');
            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            $table->foreign('IdVendor')->references('IdUser')->on('Users')->onDelete('cascade');

            $table->index(['IdVendor', 'Status']);
            $table->index(['IdUser', 'Status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PreInvoices');
    }
};
