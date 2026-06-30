<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Suppliers', function (Blueprint $table) {
            $table->id('IdSupplier');

            // Link to Users table
            $table->unsignedBigInteger('IdUser')->unique();

            // Enterprise information
            $table->string('EntrepriseName', 255);
            $table->string('PlatformName', 255)->nullable();

            // Contact information
            $table->string('Email', 255)->nullable();
            $table->string('Telephone', 50)->nullable();
            $table->string('Address', 255)->nullable();
            $table->string('City', 100)->nullable();
            $table->string('Country', 100)->nullable();

            // Banking information
            $table->string('RIB', 100)->nullable();

            // Business information
            $table->string('TaxNumber', 100)->nullable();
            $table->string('CommercialRegister', 100)->nullable();

            // Status
            $table->boolean('Active')->default(true);

            // Statistics
            $table->decimal('AverageRating', 3, 2)->default(0);
            $table->integer('TotalReviews')->default(0);
            $table->integer('TotalProducts')->default(0);

            // Dates
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->nullable();

            // Foreign Key
            $table->foreign('IdUser')
                ->references('IdUser')
                ->on('Users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Suppliers');
    }
};
