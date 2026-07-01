<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PRIORITÉ 1 — CORRIGÉE
     *
     * La migration originale utilisait le schéma Laravel par défaut (id, name, email).
     * Or TOUTES les autres tables du projet (Orders, Deals, Invoices, Payments,
     * Notifications, Reviews, Deliveries, Suppliers, PreInvoices) déclarent une
     * clé étrangère vers Users.IdUser qui n'existait pas → php artisan migrate
     * plantait immédiatement sur la première contrainte FK rencontrée.
     *
     * Cette migration fournit le vrai schéma attendu par tout le projet.
     */
    public function up(): void
    {
        Schema::create('Users', function (Blueprint $table) {
            $table->id('IdUser');
            $table->string('FirstName', 100);
            $table->string('LastName', 100);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('Telephone', 50)->nullable();
            $table->string('Address', 255)->nullable();
            // Role: 'admin' | 'vendor' | 'user'
            $table->string('Role', 20)->default('user');
            $table->tinyInteger('Active')->default(1);
            $table->rememberToken();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->nullable();

            $table->index(['Role', 'Active']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
