<?php

namespace App\Models;

/**
 * PRIORITÉ 1 — CATALOGUE UNIFIÉ
 *
 * Décision : "Deals" est le catalogue réel du projet (c'est ce que Orders,
 * Invoices et Deliveries référencent via IdDeal). "Products" était un stub vide
 * jamais utilisé par aucune commande ni facture.
 *
 * Solution retenue : Product est maintenant un alias de Deal pour éviter de
 * casser les routes /products déjà câblées dans api.php. ProductController
 * continue de fonctionner mais opère sur la table Deals. Les deux APIs
 * (/products et /deals) pointent désormais sur la même vraie table.
 */
class Product extends Deal
{
    // Hérite de tout : table Deals, primaryKey IdDeal, fillable, relations, casts.
    // Aucun doublon de table, aucune migration supplémentaire nécessaire.
}
