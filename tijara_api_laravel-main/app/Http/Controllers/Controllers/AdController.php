<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdController extends Controller
{
    public function index()
    {
        return response()->json(
            DB::table('ads')->orderByDesc('id')->get()
        );
    }

    public function show($id)
    {
        $ad = DB::table('ads')->find($id);

        if (!$ad) {
            return response()->json([
                'message' => 'Annonce introuvable.'
            ], 404);
        }

        return response()->json($ad);
    }

    public function store(Request $request)
    {
        $id = DB::table('ads')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'id' => $id,
            'message' => 'Annonce créée.'
        ], 201);
    }

    public function like($id)
    {
        if (!DB::table('ads')->where('id', $id)->exists()) {
            return response()->json([
                'message' => 'Annonce introuvable.'
            ], 404);
        }

        return response()->json([
            'message' => 'Like enregistré.'
        ]);
    }

    public function comment(Request $request, $id)
    {
        if (!DB::table('ads')->where('id', $id)->exists()) {
            return response()->json([
                'message' => 'Annonce introuvable.'
            ], 404);
        }

        return response()->json([
            'message' => 'Commentaire enregistré.'
        ]);
    }
}
