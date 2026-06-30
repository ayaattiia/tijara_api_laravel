<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoicesController extends Controller
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
     * GET /api/invoices
     */
    public function index()
    {
        $query = DB::table('Invoices as i')
            ->leftJoin('Users as u', 'i.IdUser', '=', 'u.IdUser')
            ->leftJoin('Users as uv', 'i.IdVendor', '=', 'uv.IdUser')
            ->leftJoin('Orders as o', 'i.IdOrder', '=', 'o.IdOrder')
            ->leftJoin('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
            ->selectRaw("
                i.IdInvoice,
                i.Number,
                i.IdOrder,
                i.IdUser,
                i.IdVendor,
                i.Subtotal,
                i.Tax,
                i.DeliveryFee,
                i.Total,
                i.Status,
                i.IssuedAt,
                i.PaidAt,

                CONCAT(u.FirstName,' ',u.LastName) as ClientName,
                u.Email as ClientEmail,

                CONCAT(uv.FirstName,' ',uv.LastName) as VendorName,

                d.titleDeal as DealTitle
            ");

        if ($this->isVendor()) {
            $query->where('i.IdVendor', $this->currentUserId());
        } elseif (!$this->isAdmin()) {
            $query->where('i.IdUser', $this->currentUserId());
        }

        $rows = $query
            ->orderByDesc('i.IdInvoice')
            ->get();

        $result = $rows->map(function ($r) {
            return [
                'id_invoice'   => $r->IdInvoice,
                'number'       => $r->Number,
                'id_order'     => $r->IdOrder,
                'id_user'      => $r->IdUser,
                'id_vendor'    => $r->IdVendor,
                'subtotal'     => (float) $r->Subtotal,
                'tax'          => (float) $r->Tax,
                'delivery_fee' => (float) $r->DeliveryFee,
                'total'        => (float) $r->Total,
                'status'       => $r->Status,
                'issued_at'    => $r->IssuedAt,
                'paid_at'      => $r->PaidAt,
                'client_name'  => $r->ClientName,
                'client_email' => $r->ClientEmail,
                'vendor_name'  => $r->VendorName,
                'deal_title'   => $r->DealTitle,
            ];
        });

        return response()->json($result);
    }

    /**
     * GET /api/invoices/{id}
     */
    public function show($id)
    {
        $invoice = DB::table('Invoices')
            ->where('IdInvoice', $id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }

        if (
            !$this->isAdmin() &&
            $invoice->IdUser != $this->currentUserId() &&
            $invoice->IdVendor != $this->currentUserId()
        ) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return response()->json($invoice);
    }

    /**
     * POST /api/invoices/from-order/{idOrder}
     */
    public function fromOrder($idOrder)
    {
        $existing = DB::table('Invoices')
            ->where('IdOrder', $idOrder)
            ->first();

        if ($existing) {
            return response()->json($existing);
        }

        $order = DB::table('Orders as o')
            ->leftJoin('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
            ->where('o.IdOrder', $idOrder)
            ->selectRaw("
                o.IdOrder,
                o.IdUser as IdBuyer,
                d.IdDeal,
                COALESCE(
                    CAST(REPLACE(d.PriceDeal, ',', '.') AS DECIMAL(18,3)),
                    0
                ) as Total,
                d.idUser as IdVendor
            ")
            ->first();

        if (!$order) {
            return response()->json([
                'message' => "Commande #{$idOrder} introuvable."
            ], 404);
        }

        $total = (float) $order->Total;

        $deliveryFee = 0;
        $subtotal = $total - $deliveryFee;
        $tax = round($subtotal * 0.07, 3);

        $idUser = $order->IdBuyer;
        $idVendor = $order->IdVendor;

        if (
            !$this->isAdmin() &&
            $this->currentUserId() != $idUser &&
            $this->currentUserId() != $idVendor
        ) {
            return response()->json([
                'message' => 'Vous ne pouvez générer la facture que pour vos propres commandes.'
            ], 403);
        }

        try {

            $number = 'INV-' .
                now()->year .
                '-' .
                substr(time() . rand(100000, 999999), -6);

            $id = DB::table('Invoices')->insertGetId([
                'Number'      => $number,
                'IdOrder'     => $idOrder,
                'IdUser'      => $idUser,
                'IdVendor'    => $idVendor,
                'Subtotal'    => $subtotal,
                'Tax'         => $tax,
                'DeliveryFee' => $deliveryFee,
                'Total'       => $total,
                'IssuedAt'    => now(),
            ]);

            return response()->json([
                'id_invoice' => $id,
                'number'     => $number,
                'total'      => $total,
                'subtotal'   => $subtotal,
                'tax'        => $tax,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Erreur génération facture : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /api/invoices/{id}/paid
     */
    public function markPaid($id)
    {
        $invoice = DB::table('Invoices')
            ->where('IdInvoice', $id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }

        if (
            !$this->isAdmin() &&
            $invoice->IdVendor != $this->currentUserId()
        ) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        DB::table('Invoices')
            ->where('IdInvoice', $id)
            ->update([
                'Status' => 'paid',
                'PaidAt' => now(),
            ]);

        return response()->json([
            'success' => true
        ]);
    }
}
