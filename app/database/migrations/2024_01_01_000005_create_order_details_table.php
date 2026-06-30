<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('OrderDetails', function (Blueprint $table) {
            $table->id('IdOrderDetail');
            $table->unsignedBigInteger('IdUser');
            $table->unsignedBigInteger('IdOrder');
            $table->string('Address', 255)->nullable();
            $table->string('Email', 255)->nullable();
            $table->string('Telephone', 50)->nullable();
            $table->string('FirstName', 100)->nullable();
            $table->string('LastName', 100)->nullable();
            $table->integer('Quantity')->default(1);
            $table->decimal('TotalAmount', 18, 3)->default(0);
            $table->timestamp('DateTimeCommand')->useCurrent();
            $table->tinyInteger('Active')->default(1);

            $table->foreign('IdUser')->references('IdUser')->on('Users')->onDelete('cascade');
            $table->foreign('IdOrder')->references('IdOrder')->on('Orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('OrderDetails');
    }
};
