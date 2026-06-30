<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Transports', function (Blueprint $table) {
            $table->id('IdTransport');
            $table->string('Name', 255);
            $table->string('Logo', 500)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('Email', 255)->nullable();
            $table->decimal('DeliveryFee', 18, 3)->default(0);
            $table->decimal('FreeFrom', 18, 3)->default(0); // free delivery threshold
            $table->string('Zones', 500)->nullable();        // e.g. "Tunis, Sfax, Sousse"
            $table->tinyInteger('Active')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Transports');
    }
};
