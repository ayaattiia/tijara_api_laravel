<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\NotificationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
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
     * GET /api/orders
     */
    public function index()
    {
        $role = $this->currentRole();
        $userId = $this->currentUserId();

        if ($role === 'admin') {

            $orders = DB::table('Orders as o')
                ->leftJoin('Users as u', 'o.IdUser', '=', 'u.IdUser')
                ->leftJoin('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
                ->leftJoin('Users as uv', 'd.idUser', '=', 'uv.IdUser')
                ->selectRaw("
                    o.*,
                    CONCAT(u.FirstName,' ',u.LastName) as ClientName,
                    u.Email as ClientEmail,
                    d.titleDeal as DealTitle,
                    d.priceDeal as DealPrice,
                    CONCAT(uv.FirstName,' ',uv.LastName) as VendorName
                ")
                ->orderByDesc('o.IdOrder')
                ->get();

            return response()->json(
                $orders->map(fn($o) => $this->mapOrder($o))
            );
        }

        if ($role === 'vendor') {

            $orders = DB::table('Orders as o')
                ->leftJoin('Users as u', 'o.IdUser', '=', 'u.IdUser')
                ->join('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
                ->selectRaw("
                    DISTINCT
                    o.*,
                    CONCAT(u.FirstName,' ',u.LastName) as ClientName,
                    u.Email as ClientEmail,
                    d.titleDeal as DealTitle,
                    d.priceDeal as DealPrice
                ")
                ->where('d.idUser', $userId)
                ->orderByDesc('o.IdOrder')
                ->get();

            return response()->json(
                $orders->map(fn($o) => $this->mapOrder($o))
            );
        }

        $orders = DB::table('Orders as o')
            ->leftJoin('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
            ->leftJoin('Users as uv', 'd.idUser', '=', 'uv.IdUser')
            ->selectRaw("
                o.*,
                d.titleDeal as DealTitle,
                d.priceDeal as DealPrice,
                CONCAT(uv.FirstName,' ',uv.LastName) as VendorName
            ")
            ->where('o.IdUser', $userId)
            ->orderByDesc('o.IdOrder')
            ->get();

        return response()->json(
            $orders->map(fn($o) => $this->mapOrder($o))
        );
    }

    /**
     * GET /api/orders/{id}
     */
    public function show($id)
    {
        $role = $this->currentRole();
        $userId = $this->currentUserId();

        $order = DB::table('Orders as o')
            ->leftJoin('Users as u', 'o.IdUser', '=', 'u.IdUser')
            ->leftJoin('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
            ->selectRaw("
                o.*,
                CONCAT(u.FirstName,' ',u.LastName) as ClientName,
                u.Email as ClientEmail,
                d.titleDeal as DealTitle,
                d.priceDeal as DealPrice
            ")
            ->where('o.IdOrder', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Commande introuvable.'
            ], 404);
        }

        if ($role === 'user' && $order->IdUser != $userId) {
            return response()->json([
                'message' => 'Accès refusé.'
            ], 403);
        }

        $details = DB::table('OrderDetails')
            ->where('IdOrder', $id)
            ->get();

        return response()->json([
            'order' => $this->mapOrder($order),
            'details' => $details
        ]);
    }

    /**
     * POST /api/orders
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_deal' => 'required|integer'
        ]);

        $userId = $this->currentUserId();

        $idDeal = $request->input('id_deal', $request->input('idDeal'));

        $deal = DB::table('Deals')
            ->where('IdDeal', $idDeal)
            ->where('active', 1)
            ->first();

        if (!$deal) {
            return response()->json([
                'message' => 'Produit introuvable.'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $orderId = DB::table('Orders')->insertGetId([
                'IdUser' => $userId,
                'IdDeal' => $idDeal,
                'DateTimeCommand' => now(),
                'Active' => 1
            ]);

            $detail = $request->input('detail');

            if ($detail) {

                DB::table('OrderDetails')->insert([
                    'IdUser'          => $userId,
                    'IdOrder'         => $orderId,
                    'Address'         => $detail['address'] ?? null,
                    'Email'           => $detail['email'] ?? null,
                    'Telephone'       => $detail['telephone'] ?? null,
                    'FirstName'       => $detail['first_name'] ?? null,
                    'LastName'        => $detail['last_name'] ?? null,
                    'Quantity'        => $detail['quantity'] ?? 1,
                    'TotalAmount'     => $deal->priceDeal,
                    'DateTimeCommand' => now(),
                    'Active'          => 1,
                ]);
            }

            DB::commit();

            try {

                $vendorId = DB::table('Deals')
                    ->where('IdDeal', $idDeal)
                    ->value('idUser');

                if ($vendorId && $vendorId != $userId) {

                    $clientName = DB::table('Users')
                        ->where('IdUser', $userId)
                        ->selectRaw("CONCAT(FirstName,' ',LastName) as name")
                        ->value('name');

                    NotificationsController::createNotification(
                        $vendorId,
                        'new_order',
                        'Nouvelle commande reçue !',
                        ($clientName ?: 'Un client') . ' vient de commander votre produit.',
                        '/ent/orders',
                        $orderId
                    );
                }
            } catch (\Exception $e) {
                // notification non bloquante
            }

            return response()->json([
                'id' => $orderId,
                'message' => 'Commande créée.'
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * PATCH /api/orders/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        if (!in_array($this->currentRole(), ['admin', 'vendor'])) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $status = $request->input('status');

        $map = [
            'pending'   => 1,
            'confirmed' => 3,
            'delivered' => 2,
            'cancelled' => 0,
        ];

        if (!isset($map[$status])) {
            return response()->json([
                'message' => 'Statut invalide.'
            ], 400);
        }

        $updated = DB::table('Orders')
            ->where('IdOrder', $id)
            ->update([
                'Active' => $map[$status]
            ]);

        if (!$updated) {
            return response()->json([
                'message' => 'Commande introuvable.'
            ], 404);
        }

        return response()->json([
            'message' => 'Statut mis à jour.',
            'id' => $id,
            'status' => $status
        ]);
    }

    /**
     * Helper
     */
    private function mapOrder($o)
    {
        return [
            'id'               => $o->IdOrder,
            'user_id'          => $o->IdUser,
            'deal_id'          => $o->IdDeal,
            'client_name'      => $o->ClientName ?? null,
            'client_email'     => $o->ClientEmail ?? null,
            'deal_title'       => $o->DealTitle ?? null,
            'total_amount'     => $o->DealPrice ?? null,
            'vendor_name'      => $o->VendorName ?? null,
            'status' => match ((int)$o->Active) {
                2 => 'delivered',
                3 => 'confirmed',
                0 => 'cancelled',
                default => 'pending',
            },
            'shipping_address' => null,
            'created_at' => $o->DateTimeCommand
                ? date('Y-m-d H:i:s', strtotime($o->DateTimeCommand))
                : null,
        ];
    }
}
