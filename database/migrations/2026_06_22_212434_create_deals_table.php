<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Deals', function (Blueprint $table) {
            $table->id('IdDeal');
            $table->unsignedBigInteger('IdUser');
            $table->unsignedBigInteger('IdCategory');
            $table->string('titleDeal');
            $table->text('descriptionDeal')->nullable();
            $table->decimal('priceDeal', 18, 3); // Matches 'decimal:3' from your casts
            $table->string('imageDeal')->nullable();
            $table->string('EntrepriseName')->nullable();
            $table->integer('Stock')->default(0);
            $table->string('SKU', 100)->nullable();
            $table->string('Barcode', 100)->nullable();
            $table->boolean('active')->default(true);

            // Your model has $timestamps = false but defines manual CreatedAt/UpdatedAt
            $table->timestamp('CreatedAt')->nullable();
            $table->timestamp('UpdatedAt')->nullable();

            // Foreign Key Constraints based on your model's relationships
            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            $table->foreign('IdCategory')->references('IdCateg')->on('Category')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Deals');
    }
};
