<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    // ─── LISTE (pagination + filtres) ─────────────────────
    public function index(Request $request)
    {
        $query = Ad::with(['category', 'user'])->where('Active', 1);

        // Filtre par catégorie
        if ($request->has('categorie')) {
            $query->where('IdCateg', $request->categorie);
        }

        // Filtre par type (annonce / product)
        if ($request->has('type')) {
            $query->where('Type', $request->type);
        }

        // Pagination (20 par défaut)
        $perPage = $request->input('limit', 20);
        $ads = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Ajouter l'URL complète de l'image
        $ads->getCollection()->transform(function ($ad) {
            if ($ad->ImageAd) {
                $ad->image_url = asset('storage/ads/' . $ad->ImageAd);
            }
            return $ad;
        });

        return response()->json($ads);
    }

    // ─── DÉTAIL ────────────────────────────────────────────
    public function show($id)
    {
        $ad = Ad::with(['category', 'user'])->where('IdAd', $id)->firstOrFail();

        // Incrémenter le compteur de vues
        $ad->increment('views');

        if ($ad->ImageAd) {
            $ad->image_url = asset('storage/ads/' . $ad->ImageAd);
        }

        return response()->json($ad);
    }

    // ─── CRÉER ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'TitleAd'   => 'required|string|max:250',
            'PriceAd'   => 'nullable|string|max:250',
            'IdCateg'   => 'nullable|exists:categories,IdCateg',
            'image'     => 'nullable|image|max:2048',
            'Type'      => 'nullable|string|in:annonce,product',
        ]);

        $data = $request->only([
            'TitleAd', 'DescriptionAd', 'DetailsAd', 'PriceAd',
            'IdCateg', 'Type', 'LocationAd', 'Color', 'Brand',
            'Telephone', 'Email'
        ]);

        $data['IdUser'] = Auth::id();
        $data['Active'] = 1;

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('ads', 'public');
            $data['ImageAd'] = basename($path);
        }

        $ad = Ad::create($data);

        return response()->json($ad, 201);
    }

    // ─── MODIFIER ──────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $ad = Ad::where('IdAd', $id)->where('IdUser', Auth::id())->firstOrFail();

        $request->validate([
            'TitleAd' => 'nullable|string|max:250',
            'image'   => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'TitleAd', 'DescriptionAd', 'DetailsAd', 'PriceAd',
            'IdCateg', 'Type', 'LocationAd', 'Color', 'Brand',
            'Telephone', 'Email'
        ]);

        // Gestion de la nouvelle image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($ad->ImageAd) {
                Storage::disk('public')->delete('ads/' . $ad->ImageAd);
            }
            $path = $request->file('image')->store('ads', 'public');
            $data['ImageAd'] = basename($path);
        }

        $ad->update($data);

        return response()->json($ad);
    }

    // ─── SUPPRIMER (soft delete) ──────────────────────────
    public function destroy($id)
    {
        $ad = Ad::where('IdAd', $id)->where('IdUser', Auth::id())->firstOrFail();
        $ad->update(['Active' => 0]);

        return response()->json(['message' => 'Annonce désactivée.']);
    }

    // ─── LIKE / UNLIKE ─────────────────────────────────────
    public function like($id)
    {
        $ad = Ad::where('IdAd', $id)->firstOrFail();
        $user = Auth::user();

        $existing = $ad->likes()->where('IdUser', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $ad->likes()->create(['IdUser' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $ad->likes()->count(),
        ]);
    }

    // ─── COMMENTAIRE (optionnel) ──────────────────────────
    public function comment(Request $request, $id)
    {
        // À implémenter si besoin
        return response()->json(['message' => 'Commentaire à venir']);
    }

    // ─── INCRÉMENTER LES VUES ─────────────────────────────
    public function incrementView($id)
    {
        $ad = Ad::where('IdAd', $id)->firstOrFail();
        $ad->increment('views');

        return response()->json(['message' => 'Vue incrémentée', 'views' => $ad->views]);
    }

    // ─── MES ANNONCES ──────────────────────────────────────
    public function mine()
    {
        $ads = Ad::with(['category'])
            ->where('IdUser', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ads);
    }
}