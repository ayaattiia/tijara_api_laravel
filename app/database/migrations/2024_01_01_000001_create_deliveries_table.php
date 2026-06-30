<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Deliveries', function (Blueprint $table) {
            $table->id('IdDelivery');
            $table->unsignedBigInteger('IdOrder');
            $table->unsignedBigInteger('IdTransport')->nullable();
            $table->string('TrackingNumber', 100)->nullable();
            $table->string('Status', 50)->default('pending'); // pending, shipped, delivered, returned
            $table->string('AddressLine', 255)->nullable();
            $table->string('City', 100)->nullable();
            $table->string('PostalCode', 20)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->decimal('DeliveryFee', 18, 3)->default(0);
            $table->text('Note')->nullable();
            $table->timestamp('EstimatedAt')->nullable();
            $table->timestamp('DeliveredAt')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->nullable();

            $table->foreign('IdOrder')->references('IdOrder')->on('Orders')->onDelete('cascade');
            $table->foreign('IdTransport')->references('IdTransport')->on('Transports')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Deliveries');
    }
};
