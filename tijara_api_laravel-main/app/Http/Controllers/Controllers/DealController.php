<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\NotificationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;

class DealController extends Controller
{
    private function currentUserId()
    {
        return Auth::id();
    }

    private function currentRole()
    {
        return Auth::user()?->role ?? 'user';
    }

    /**
     * GET /api/deals
     */
    public function index()
    {
        $role = $this->currentRole();
        $userId = $this->currentUserId();

        $query = DB::table('Deals as d')
            ->leftJoin('Users as u', 'd.IdUser', '=', 'u.IdUser')
            ->leftJoin('Categories as c', 'd.IdCategory', '=', 'c.IdCateg')
            ->selectRaw("
                d.*,
                CONCAT(u.FirstName,' ',u.LastName) as VendorName,
                c.NameCategory as CategoryName
            ");

        if ($role === 'vendor') {
            $query->where('d.IdUser', $userId);
        }

        $deals = $query
            ->orderByDesc('d.IdDeal')
            ->get();

        return response()->json(
            $deals->map(fn($deal) => $this->mapDeal($deal))
        );
    }

    /**
     * GET /api/deals/{id}
     */
    public function show($id)
    {
        $deal = DB::table('Deals as d')
            ->leftJoin('Users as u', 'd.IdUser', '=', 'u.IdUser')
            ->leftJoin('Categories as c', 'd.IdCategory', '=', 'c.IdCateg')
            ->selectRaw("
                d.*,
                CONCAT(u.FirstName,' ',u.LastName) as VendorName,
                c.NameCategory as CategoryName
            ")
            ->where('d.IdDeal', $id)
            ->first();

        if (!$deal) {
            return response()->json([
                'message' => 'Deal not found.'
            ], 404);
        }

        return response()->json(
            $this->mapDeal($deal)
        );
    }

    /**
     * POST /api/deals
     */
    public function store(Request $request)
    {
        $request->validate([
            'IdCategory' => 'required|integer',
            'titleDeal' => 'required|string|max:255',
            'descriptionDeal' => 'nullable|string',
            'priceDeal' => 'required|numeric',
            'Stock' => 'nullable|integer',
            'SKU' => 'nullable|string|max:100',
            'Barcode' => 'nullable|string|max:100',
            'EntrepriseName' => 'nullable|string|max:255',
            'imageDeal' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {

            $deal = Deal::create([
                'IdUser' => $this->currentUserId(),
                'IdCategory' => $request->IdCategory,
                'titleDeal' => $request->titleDeal,
                'descriptionDeal' => $request->descriptionDeal,
                'priceDeal' => $request->priceDeal,
                'imageDeal' => $request->imageDeal,
                'EntrepriseName' => $request->EntrepriseName,
                'Stock' => $request->Stock ?? 0,
                'SKU' => $request->SKU,
                'Barcode' => $request->Barcode,
                'active' => 1,
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Deal created successfully.',
                'deal' => $deal
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * PUT /api/deals/{id}
     */
    public function update(Request $request, $id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return response()->json([
                'message' => 'Deal not found.'
            ], 404);
        }

        if (
            $this->currentRole() !== 'admin' &&
            $deal->IdUser != $this->currentUserId()
        ) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $deal->update([
            'IdCategory' => $request->IdCategory ?? $deal->IdCategory,
            'titleDeal' => $request->titleDeal ?? $deal->titleDeal,
            'descriptionDeal' => $request->descriptionDeal ?? $deal->descriptionDeal,
            'priceDeal' => $request->priceDeal ?? $deal->priceDeal,
            'imageDeal' => $request->imageDeal ?? $deal->imageDeal,
            'EntrepriseName' => $request->EntrepriseName ?? $deal->EntrepriseName,
            'Stock' => $request->Stock ?? $deal->Stock,
            'SKU' => $request->SKU ?? $deal->SKU,
            'Barcode' => $request->Barcode ?? $deal->Barcode,
            'UpdatedAt' => now()
        ]);

        return response()->json([
            'message' => 'Deal updated successfully.',
            'deal' => $deal
        ]);
    }

    /**
     * DELETE /api/deals/{id}
     */
    public function destroy($id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return response()->json([
                'message' => 'Deal not found.'
            ], 404);
        }

        if (
            $this->currentRole() !== 'admin' &&
            $deal->IdUser != $this->currentUserId()
        ) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $deal->delete();

        return response()->json([
            'message' => 'Deal deleted successfully.'
        ]);
    }

    /**
     * PATCH /api/deals/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return response()->json([
                'message' => 'Deal not found.'
            ], 404);
        }

        if (
            $this->currentRole() !== 'admin' &&
            $deal->IdUser != $this->currentUserId()
        ) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $request->validate([
            'active' => 'required|boolean'
        ]);

        $deal->active = $request->active;
        $deal->UpdatedAt = now();
        $deal->save();

        return response()->json([
            'message' => 'Status updated.',
            'active' => $deal->active
        ]);
    }

    /**
     * Helper
     */
    private function mapDeal($deal)
    {
        return [
            'id' => $deal->IdDeal,
            'vendor_id' => $deal->IdUser,
            'category_id' => $deal->IdCategory,
            'category_name' => $deal->CategoryName ?? null,
            'vendor_name' => $deal->VendorName ?? null,
            'title' => $deal->titleDeal,
            'description' => $deal->descriptionDeal,
            'price' => (float) $deal->priceDeal,
            'stock' => (int) $deal->Stock,
            'sku' => $deal->SKU,
            'barcode' => $deal->Barcode,
            'company' => $deal->EntrepriseName,
            'image' => $deal->imageDeal,
            'active' => (bool) $deal->active,
            'created_at' => $deal->CreatedAt,
            'updated_at' => $deal->UpdatedAt
        ];
    }
}
