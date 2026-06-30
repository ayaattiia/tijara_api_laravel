<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    // GET /api/categories (public)
    public function index()
    {
        return Category::select('categories.*', 'type_categorie.Title as TypeTitle')
            ->leftJoin('type_categorie', 'categories.idtypecat', '=', 'type_categorie.Idtypecat')
            ->where('categories.Active', 1)
            ->orderBy('categories.TitleFr')
            ->get()
            ->map(fn($c) => $this->mapCategory($c));
            
    }

    // GET /api/categories/all (admin)
    public function all()
    {
        return Category::select('categories.*', 'type_categorie.Title as TypeTitle')
            ->leftJoin('type_categorie', 'categories.idtypecat', '=', 'type_categorie.Idtypecat')
            ->orderBy('categories.IdCateg')
            ->get()
            ->map(fn($c) => $this->mapCategory($c));
    }

    // GET /api/categories/types (public)
    public function types()
    {
        return DB::table('type_categorie')
            ->orderBy('Idtypecat')
            ->get()
            ->map(fn($t) => [
                'id'   => $t->Idtypecat,
                'name' => $t->Title
            ]);
    }

    // GET /api/categories/{id} (public)
    public function show($id)
    {
        $cat = Category::select('categories.*', 'type_categorie.Title as TypeTitle')
            ->leftJoin('type_categorie', 'categories.idtypecat', '=', 'type_categorie.Idtypecat')
            ->where('categories.IdCateg', $id)
            ->first();

        if (!$cat) {
            return response()->json(['message' => 'Catégorie introuvable'], 404);
        }

        return $this->mapCategory($cat);
    }

    // POST /api/categories
    public function store(Request $req)
    {
        if (!$req->NameFr && !$req->Name) {
            return response()->json(['message' => 'Nom requis'], 400);
        }

        $cat = Category::create([
            'TitleFr'     => trim($req->NameFr ?? $req->Name),
            'TitleEn'     => trim($req->NameEn ?? $req->NameFr ?? $req->Name),
            'TitleAr'     => trim($req->NameAr),
            'Description' => trim($req->Description),
            'Image'       => trim($req->Image),
            'idtypecat'   => $req->TypeId ?? $req->IdTypecat,
            'Active'      => 1
        ]);

        return response()->json($this->mapCategory($cat), 201);
    }

    // PUT /api/categories/{id}
    public function update(Request $req, $id)
    {
        $cat = Category::findOrFail($id);

        $cat->update([
            'TitleFr'     => $req->NameFr ?? $req->Name ?? $cat->TitleFr,
            'TitleEn'     => $req->NameEn ?? $cat->TitleEn,
            'TitleAr'     => $req->NameAr ?? $cat->TitleAr,
            'Description' => $req->Description ?? $cat->Description,
            'Image'       => $req->Image ?? $cat->Image,
            'idtypecat'   => $req->TypeId ?? $req->IdTypecat ?? $cat->idtypecat
        ]);

        return $this->mapCategory($cat);
    }

    public function toggle($id)
    {
        $cat = Category::findOrFail($id);

        $cat->Active = $cat->Active == 1 ? 0 : 1;
        $cat->save();

        return response()->json([
            'message' => 'Statut modifié',
            'active'  => $cat->Active == 1
        ]);
    }

    public function destroy($id)
    {
        $cat = Category::findOrFail($id);

        $cat->Active = 0;
        $cat->save();

        return response()->json(['message' => 'Catégorie désactivée']);
    }

    private function mapCategory($c)
    {
        return [
            'id'          => $c->IdCateg,
            'name'        => $c->TitleFr ?? $c->TitleEn ?? $c->TitleAr,
            'name_fr'     => $c->TitleFr,
            'name_en'     => $c->TitleEn,
            'name_ar'     => $c->TitleAr,
            'slug'        => strtolower(str_replace(' ', '-', $c->TitleFr ?? $c->TitleEn)),
            'description' => $c->Description,
            'image'       => $c->Image,
            'type_id'     => $c->idtypecat,
            'type_title'  => $c->TypeTitle ?? null,
            'active'      => $c->Active == 1
        ];
    }
}