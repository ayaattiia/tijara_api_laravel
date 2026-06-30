<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private function currentUserId()
    {
        return Auth::id();
    }

    private function currentRole()
    {
        return Auth::user()?->role ?? 'user';
    }

    private function isAdmin()
    {
        return $this->currentRole() === 'admin';
    }

    private function isVendor()
    {
        return $this->currentRole() === 'vendor';
    }

    /**
     * GET /api/products
     */
    public function index(Request $request)
    {
        $status   = $request->query('status');
        $category = $request->query('id_category');

        $query = DB::table('Products as p')
            ->leftJoin('Categories as c', 'p.IdCategory', '=', 'c.IdCategory')
            ->leftJoin('Users as u', 'p.IdUser', '=', 'u.IdUser')
            ->selectRaw("
                p.*,
                c.Name as CategoryName,
                CONCAT(u.FirstName,' ',u.LastName) as VendorName,
                u.Email as VendorEmail,
                u.Telephone as VendorPhone
            ");

        if ($this->isVendor()) {
            $query->where('p.IdUser', $this->currentUserId());
        } elseif (!$this->isAdmin()) {
            $query->where('p.Active', 1);
        }

        if ($status) {
            $query->where('p.Active', $status === 'active' ? 1 : 0);
        }

        if ($category) {
            $query->where('p.IdCategory', $category);
        }

        $rows = $query
            ->orderByDesc('p.IdProduct')
            ->get();

        $result = $rows->map(function ($r) {
            return [
                'id_product'    => $r->IdProduct,
                'id_user'       => $r->IdUser,
                'id_category'   => $r->IdCategory,
                'category_name' => $r->CategoryName,
                'vendor_name'   => $r->VendorName,
                'vendor_email'  => $r->VendorEmail,
                'vendor_phone'  => $r->VendorPhone,
                'name'          => $r->Name,
                'description'   => $r->Description,
                'price'         => $r->Price ?? 0,
                'stock'         => $r->Stock ?? 0,
                'image_url'     => $r->ImageUrl,
                'active'        => (bool) $r->Active,
                'created_at'    => $r->CreatedAt,
                'updated_at'    => $r->UpdatedAt,
            ];
        });

        return response()->json($result);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request)
    {
        $name = $request->input('name');

        if (!$name) {
            return response()->json([
                'message' => 'name requis.'
            ], 400);
        }

        if (!$this->isAdmin() && !$this->isVendor()) {
            return response()->json([
                'message' => 'Action non autorisée.'
            ], 403);
        }

        $data = [
            'IdUser'      => $this->currentUserId(),
            'IdCategory'  => $request->input('id_category', $request->input('idCategory')),
            'Name'        => $name,
            'Description' => $request->input('description'),
            'Price'       => $request->input('price', 0),
            'Stock'       => $request->input('stock', 0),
            'ImageUrl'    => $request->input('image_url', $request->input('imageUrl')),
            'Active'      => $request->input('active', 1),
            'CreatedAt'   => now(),
            'UpdatedAt'   => now(),
        ];

        try {
            $id = DB::table('Products')->insertGetId($data);

            return response()->json([
                'id_product' => $id,
                'created'    => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Création impossible : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/products/{id}
     */
    public function update(Request $request, $id)
    {
        if ($this->isVendor()) {

            $owns = DB::table('Products')
                ->where('IdProduct', $id)
                ->where('IdUser', $this->currentUserId())
                ->count();

            if (!$owns) {
                return response()->json([
                    'message' => 'Produit non autorisé.'
                ], 403);
            }
        }

        DB::table('Products')
            ->where('IdProduct', $id)
            ->update([
                'IdCategory'  => $request->input('id_category'),
                'Name'        => $request->input('name'),
                'Description' => $request->input('description'),
                'Price'       => $request->input('price'),
                'Stock'       => $request->input('stock'),
                'ImageUrl'    => $request->input('image_url'),
                'UpdatedAt'   => now(),
            ]);

        return response()->json([
            'id_product' => $id,
            'updated'    => true
        ]);
    }

    /**
     * PATCH /api/products/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $active = $request->input('active');

        if (is_null($active)) {
            return response()->json([
                'message' => 'active requis.'
            ], 400);
        }

        if ($this->isVendor()) {

            $owns = DB::table('Products')
                ->where('IdProduct', $id)
                ->where('IdUser', $this->currentUserId())
                ->count();

            if (!$owns) {
                return response()->json([
                    'message' => 'Produit non autorisé.'
                ], 403);
            }
        }

        DB::table('Products')
            ->where('IdProduct', $id)
            ->update([
                'Active'    => (bool) $active,
                'UpdatedAt' => now(),
            ]);

        return response()->json([
            'id_product' => $id,
            'active'     => (bool) $active
        ]);
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy($id)
    {
        if ($this->isVendor()) {

            $owns = DB::table('Products')
                ->where('IdProduct', $id)
                ->where('IdUser', $this->currentUserId())
                ->count();

            if (!$owns) {
                return response()->json([
                    'message' => 'Produit non autorisé.'
                ], 403);
            }
        }

        try {
            DB::table('Products')->where('IdProduct', $id)->delete();

            return response()->json([
                'id_product' => $id,
                'deleted'    => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Suppression impossible : ' . $e->getMessage()
            ], 500);
        }
    }
}
