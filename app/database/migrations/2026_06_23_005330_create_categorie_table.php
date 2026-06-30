<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id('IdCateg');
            $table->string('TitleFr');
            $table->string('TitleEn')->nullable();
            $table->string('TitleAr')->nullable();
            $table->text('Description')->nullable();
            $table->string('Image')->nullable();
            $table->unsignedBigInteger('idtypecat')->nullable();
            $table->boolean('Active')->default(1);
            $table->timestamps();

            $table->foreign('idtypecat')
                ->references('Idtypecat')
                ->on('type_categorie')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};