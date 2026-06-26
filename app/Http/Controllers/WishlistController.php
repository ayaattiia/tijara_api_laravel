<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\WishlistAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // ─── LISTE DES FAVORIS ──────────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        $favorites = WishlistAd::with('ad')
            ->where('IdUser', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favorites);
    }

    // ─── AJOUTER AUX FAVORIS ───────────────────────────────
    public function add($adId)
    {
        $userId = Auth::id();

        // Vérifier que l'annonce existe
        $ad = Ad::where('IdAd', $adId)->first();
        if (!$ad) {
            return response()->json(['message' => 'Annonce introuvable.'], 404);
        }

        // Vérifier si déjà en favori
        $exists = WishlistAd::where('IdUser', $userId)
            ->where('IdAd', $adId)
            ->first();

        if ($exists) {
            return response()->json(['message' => 'Déjà dans les favoris.'], 409);
        }

        $wishlist = WishlistAd::create([
            'IdUser' => $userId,
            'IdAd'   => $adId,
        ]);

        return response()->json([
            'message' => 'Annonce ajoutée aux favoris.',
            'data'    => $wishlist
        ], 201);
    }

    // ─── RETIRER DES FAVORIS ───────────────────────────────
    public function remove($adId)
    {
        $userId = Auth::id();

        $wishlist = WishlistAd::where('IdUser', $userId)
            ->where('IdAd', $adId)
            ->first();

        if (!$wishlist) {
            return response()->json(['message' => 'Annonce non trouvée dans les favoris.'], 404);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Annonce retirée des favoris.']);
    }

    // ─── VÉRIFIER SI EN FAVORI ─────────────────────────────
    public function check(Request $request)
    {
        $userId = Auth::id();
        $adId = $request->query('adId');

        if (!$adId) {
            return response()->json(['message' => 'Paramètre adId requis.'], 400);
        }

        $exists = WishlistAd::where('IdUser', $userId)
            ->where('IdAd', $adId)
            ->exists();

        return response()->json(['liked' => $exists]);
    }
}