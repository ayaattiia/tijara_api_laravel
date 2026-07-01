<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Deal;
use App\Models\Category;
use App\Models\TypeCategorie;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * CORRIGÉ v2 — colonnes alignées sur les vraies migrations :
 *   TypeCategorie : Title (pas TypeName)
 *   Category      : TitleFr, TitleEn, idtypecat (pas NomCateg, IdType)
 *   Deal          : idtypecat via category (pas IdCategory direct)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Admin ─────────────────────────────────────────────
        $admin = User::create([
            'FirstName' => 'Admin',
            'LastName'  => 'Tijara',
            'email'     => 'admin@tijara.ma',
            'password'  => Hash::make('Admin@1234'),
            'Role'      => 'admin',
            'Active'    => 1,
            'CreatedAt' => now(),
        ]);

        // ── 2. Vendors ───────────────────────────────────────────
        $vendor1 = User::create([
            'FirstName' => 'Yassine',
            'LastName'  => 'Benali',
            'email'     => 'vendor1@tijara.ma',
            'password'  => Hash::make('Vendor@1234'),
            'Role'      => 'vendor',
            'Telephone' => '0661000001',
            'Active'    => 1,
            'CreatedAt' => now(),
        ]);

        $vendor2 = User::create([
            'FirstName' => 'Fatima',
            'LastName'  => 'Alaoui',
            'email'     => 'vendor2@tijara.ma',
            'password'  => Hash::make('Vendor@1234'),
            'Role'      => 'vendor',
            'Telephone' => '0661000002',
            'Active'    => 1,
            'CreatedAt' => now(),
        ]);

        // ── 3. Profils Supplier ──────────────────────────────────
        Supplier::create([
            'IdUser'             => $vendor1->IdUser,
            'EntrepriseName'     => 'Benali Import Export SARL',
            'PlatformName'       => 'Tijara',
            'Email'              => $vendor1->email,
            'Telephone'          => $vendor1->Telephone,
            'City'               => 'Casablanca',
            'Country'            => 'Maroc',
            'TaxNumber'          => 'MA-12345678',
            'CommercialRegister' => 'RC-CAS-001',
            'Active'             => true,
            'CreatedAt'          => now(),
        ]);

        Supplier::create([
            'IdUser'         => $vendor2->IdUser,
            'EntrepriseName' => 'Alaoui Tech Distribution',
            'PlatformName'   => 'Tijara',
            'Email'          => $vendor2->email,
            'Telephone'      => $vendor2->Telephone,
            'City'           => 'Rabat',
            'Country'        => 'Maroc',
            'TaxNumber'      => 'MA-87654321',
            'Active'         => true,
            'CreatedAt'      => now(),
        ]);

        // ── 4. Clients ───────────────────────────────────────────
        $client1 = User::create([
            'FirstName' => 'Mohammed',
            'LastName'  => 'Khalid',
            'email'     => 'client1@example.com',
            'password'  => Hash::make('Client@1234'),
            'Role'      => 'user',
            'Active'    => 1,
            'CreatedAt' => now(),
        ]);

        $client2 = User::create([
            'FirstName' => 'Aicha',
            'LastName'  => 'Rami',
            'email'     => 'client2@example.com',
            'password'  => Hash::make('Client@1234'),
            'Role'      => 'user',
            'Active'    => 1,
            'CreatedAt' => now(),
        ]);

        // ── 5. TypeCategorie ─────────────────────────────────────
        // Colonne réelle : Title (pas TypeName)
        $typeElec = TypeCategorie::create([
            'Title'       => 'Électronique',
            'Description' => 'Produits électroniques et high-tech',
            'Active'      => 1,
        ]);

        $typeMode = TypeCategorie::create([
            'Title'       => 'Mode & Vêtements',
            'Description' => 'Vêtements, chaussures et accessoires',
            'Active'      => 1,
        ]);

        // ── 6. Categories ────────────────────────────────────────
        // Colonnes réelles : TitleFr, TitleEn, idtypecat (pas NomCateg, IdType)
        $catSmartphones = Category::create([
            'TitleFr'    => 'Smartphones',
            'TitleEn'    => 'Smartphones',
            'TitleAr'    => 'الهواتف الذكية',
            'Description' => 'Téléphones intelligents et accessoires',
            'idtypecat'  => $typeElec->Idtypecat,
            'Active'     => 1,
        ]);

        $catLaptops = Category::create([
            'TitleFr'    => 'Ordinateurs portables',
            'TitleEn'    => 'Laptops',
            'TitleAr'    => 'الحواسيب المحمولة',
            'Description' => 'Laptops et ordinateurs portables',
            'idtypecat'  => $typeElec->Idtypecat,
            'Active'     => 1,
        ]);

        // ── 7. Deals (catalogue produits) ────────────────────────
        $deal1 = Deal::create([
            'IdUser'          => $vendor1->IdUser,
            'IdCategory'      => $catSmartphones->IdCateg,
            'titleDeal'       => 'Samsung Galaxy A55 128Go',
            'descriptionDeal' => 'Smartphone Samsung Galaxy A55 — écran AMOLED 6.6", 8Go RAM, 128Go',
            'priceDeal'       => 3499.000,
            'EntrepriseName'  => 'Benali Import Export SARL',
            'Stock'           => 25,
            'SKU'             => 'SAM-A55-128',
            'Barcode'         => '8806095076423',
            'active'          => true,
            'CreatedAt'       => now(),
        ]);

        $deal2 = Deal::create([
            'IdUser'          => $vendor1->IdUser,
            'IdCategory'      => $catSmartphones->IdCateg,
            'titleDeal'       => 'iPhone 15 256Go',
            'descriptionDeal' => 'Apple iPhone 15 — puce A16, Dynamic Island, USB-C',
            'priceDeal'       => 10999.000,
            'EntrepriseName'  => 'Benali Import Export SARL',
            'Stock'           => 10,
            'SKU'             => 'APL-IP15-256',
            'Barcode'         => '0194253716266',
            'active'          => true,
            'CreatedAt'       => now(),
        ]);

        $deal3 = Deal::create([
            'IdUser'          => $vendor2->IdUser,
            'IdCategory'      => $catLaptops->IdCateg,
            'titleDeal'       => 'Lenovo ThinkPad E14 Gen5',
            'descriptionDeal' => 'Laptop professionnel — Intel Core i5, 16Go RAM, 512Go SSD',
            'priceDeal'       => 8750.000,
            'EntrepriseName'  => 'Alaoui Tech Distribution',
            'Stock'           => 8,
            'SKU'             => 'LNV-TP-E14G5',
            'Barcode'         => '0195892056540',
            'active'          => true,
            'CreatedAt'       => now(),
        ]);

        // ── 8. Commandes sample ──────────────────────────────────
        Order::create([
            'IdUser'          => $client1->IdUser,
            'IdDeal'          => $deal1->IdDeal,
            'DateTimeCommand' => now()->subDays(3),
            'Active'          => Order::STATUS_CONFIRMED,
            'PaymentStatus'   => 'paid',
        ]);

        Order::create([
            'IdUser'          => $client2->IdUser,
            'IdDeal'          => $deal2->IdDeal,
            'DateTimeCommand' => now()->subDays(1),
            'Active'          => Order::STATUS_PENDING,
            'PaymentStatus'   => 'unpaid',
        ]);

        Order::create([
            'IdUser'          => $client1->IdUser,
            'IdDeal'          => $deal3->IdDeal,
            'DateTimeCommand' => now(),
            'Active'          => Order::STATUS_PENDING,
            'PaymentStatus'   => 'unpaid',
        ]);

        $this->command->info('');
        $this->command->info('✅ Seeder terminé avec succès !');
        $this->command->info('   Admin   → admin@tijara.ma    / Admin@1234');
        $this->command->info('   Vendor1 → vendor1@tijara.ma  / Vendor@1234');
        $this->command->info('   Vendor2 → vendor2@tijara.ma  / Vendor@1234');
        $this->command->info('   Client1 → client1@example.com / Client@1234');
    }
}
