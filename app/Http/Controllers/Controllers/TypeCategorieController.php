<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeCategorie;

class TypeCategorieController extends Controller
{
    // GET /api/typecategories
    public function index()
    {
        return TypeCategorie::where('Active', 1)
            ->orderBy('Title')
            ->get()
            ->map(fn($t) => $this->mapTypeCategory($t));
    }

    // GET /api/typecategories/all
    public function all()
    {
        return TypeCategorie::orderBy('Idtypecat')
            ->get()
            ->map(fn($t) => $this->mapTypeCategory($t));
    }

    // GET /api/typecategories/{id}
    public function show($id)
    {
        // $type = TypeCategorie::find($id);
        $type = TypeCategorie::findOrFail($id);

        // if (!$type) {
        //     return response()->json([
        //         'message' => 'Type catégorie introuvable'
        //     ], 404);
        // }

        return $this->mapTypeCategory($type);
    }

    // POST /api/typecategories
    public function store(Request $req)
    {
        if (!$req->Title) {
            return response()->json([
                'message' => 'Titre requis'
            ], 400);
        }

        $type = TypeCategorie::create([
            'Title'       => trim($req->Title),
            'Description' => trim($req->Description),
            'Active'      => 1
        ]);

        return response()->json(
            $this->mapTypeCategory($type),
            201
        );
    }

    // PUT /api/typecategories/{id}
    public function update(Request $req, $id)
    {
        // $type = TypeCategorie::find($id);
        $type = TypeCategorie::findOrFail($id);
        // if (!$type) {
        //     return response()->json([
        //         'message' => 'Type catégorie introuvable'
        //     ], 404);
        // }

        $type->update([
            'Title'       => $req->Title ?? $type->Title,
            'Description' => $req->Description ?? $type->Description
        ]);

        return $this->mapTypeCategory($type);
    }

    // PATCH /api/typecategories/{id}/toggle
    public function toggle($id)
    {
        // $type = TypeCategorie::find($id);
        $type = TypeCategorie::findOrFail($id);
        // if (!$type) {
        //     return response()->json([
        //         'message' => 'Type catégorie introuvable'
        //     ], 404);
        // }

        $type->Active = $type->Active == 1 ? 0 : 1;
        $type->save();

        return response()->json([
            'message' => 'Statut modifié',
            'Active'  => $type->Active == 1
        ]);
    }

    // DELETE /api/typecategories/{id}
    public function destroy($id)
    {
        // $type = TypeCategorie::find($id);    
        // if (!$type) {
        //     return response()->json([
        //         'message' => 'Type catégorie introuvable'
        //     ], 404);
        // }
        $type = TypeCategorie::findOrFail($id);

        // Soft delete
        $type->Active = 0;
        $type->save();

        return response()->json([
            'message' => 'Type catégorie désactivé'
        ]);
    }

    private function mapTypeCategory($type)
    {
        return [
            'id'          => $type->Idtypecat,
            'title'       => $type->Title,
            'description' => $type->Description,
            'Active'      => $type->Active == 1
        ];
    }
}