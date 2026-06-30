<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    private function currentUserId() { return Auth::id(); }
    private function isAdmin()       { return (Auth::user()?->Role ?? 'user') === 'admin'; }

    /**
     * GET /api/suppliers
     * List all vendors/fournisseurs with their entreprise name.
     */
    public function index(Request $request)
    {
        $search  = $request->query('search');
        $perPage = min((int) $request->query('per_page', 20), 100);

        $query = DB::table('Users')
            ->where('Role', 'vendor')
            ->select(
                'IdUser',
                'FirstName',
                'LastName',
                'Email',
                'Telephone',
                'EntrepriseName',
                'PlatformName',
                'Address',
                'City',
                'Active'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('EntrepriseName', 'like', "%{$search}%")
                  ->orWhere('FirstName', 'like', "%{$search}%")
                  ->orWhere('LastName', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('EntrepriseName')
            ->forPage($request->query('page', 1), $perPage)
            ->get();

        return response()->json(['data' => $rows, 'meta' => ['total' => $total]]);
    }

    /**
     * GET /api/suppliers/{id}
     */
    public function show($id)
    {
        $supplier = DB::table('Users')->where('IdUser', $id)->where('Role', 'vendor')->first();

        if (!$supplier) {
            return response()->json(['message' => 'Fournisseur introuvable.'], 404);
        }

        unset($supplier->Password);

        $stats = DB::table('Deals')
            ->where('IdUser', $id)
            ->selectRaw('COUNT(*) as total_products, SUM(CASE WHEN active=1 THEN 1 ELSE 0 END) as active_products')
            ->first();

        $rating = DB::table('Reviews')
            ->where('TargetType', 'vendor')
            ->where('TargetId', $id)
            ->where('Active', 1)
            ->selectRaw('AVG(Rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        return response()->json([
            'supplier' => $supplier,
            'stats'    => $stats,
            'rating'   => [
                'average' => round((float) ($rating->avg_rating ?? 0), 1),
                'total'   => (int) ($rating->total_reviews ?? 0),
            ],
        ]);
    }

    /**
     * GET /api/suppliers/{id}/products
     */
    public function products($id, Request $request)
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $rows = DB::table('Deals')
            ->where('IdUser', $id)
            ->where('active', 1)
            ->orderByDesc('IdDeal')
            ->forPage($request->query('page', 1), $perPage)
            ->get();

        return response()->json($rows);
    }

    /**
     * GET /api/suppliers/{id}/reviews
     */
    public function reviews($id, Request $request)
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $rows = DB::table('Reviews as r')
            ->leftJoin('Users as u', 'r.IdUser', '=', 'u.IdUser')
            ->where('r.TargetType', 'vendor')
            ->where('r.TargetId', $id)
            ->where('r.Active', 1)
            ->selectRaw("r.*, CONCAT(u.FirstName,' ',u.LastName) as AuthorName")
            ->orderByDesc('r.CreatedAt')
            ->forPage($request->query('page', 1), $perPage)
            ->get();

        return response()->json($rows);
    }

    /**
     * GET /api/suppliers/{id}/history
     * Order history fulfilled by this supplier.
     */
    public function history($id, Request $request)
    {
        if (!$this->isAdmin() && $this->currentUserId() != $id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $perPage = min((int) $request->query('per_page', 20), 100);

        $rows = DB::table('Orders as o')
            ->join('Deals as dl', 'o.IdDeal', '=', 'dl.IdDeal')
            ->leftJoin('Users as buyer', 'o.IdUser', '=', 'buyer.IdUser')
            ->where('dl.IdUser', $id)
            ->selectRaw("
                o.*,
                dl.titleDeal,
                CONCAT(buyer.FirstName,' ',buyer.LastName) as ClientName
            ")
            ->orderByDesc('o.IdOrder')
            ->forPage($request->query('page', 1), $perPage)
            ->get();

        return response()->json($rows);
    }

    /**
     * PUT /api/suppliers/{id}
     * Update entreprise name / platform name / profile.
     */
    public function update(Request $request, $id)
    {
        if (!$this->isAdmin() && $this->currentUserId() != $id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validated = $request->validate([
            'entreprise_name' => 'nullable|string|max:255',
            'platform_name'   => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'telephone'       => 'nullable|string|max:50',
            'rib'             => 'nullable|string|max:100',
        ]);

        DB::table('Users')->where('IdUser', $id)->update([
            'EntrepriseName' => $validated['entreprise_name'] ?? DB::raw('EntrepriseName'),
            'PlatformName'   => $validated['platform_name'] ?? DB::raw('PlatformName'),
            'Address'        => $validated['address'] ?? DB::raw('Address'),
            'City'           => $validated['city'] ?? DB::raw('City'),
            'Telephone'      => $validated['telephone'] ?? DB::raw('Telephone'),
            'RIB'            => $validated['rib'] ?? DB::raw('RIB'),
            'UpdatedAt'      => now(),
        ]);

        return response()->json(['id_user' => $id, 'updated' => true]);
    }
}
