<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PreInvoiceController extends Controller
{
    private function currentUserId() { return Auth::id(); }
    private function currentRole()   { return Auth::user()?->Role ?? 'user'; }
    private function isAdmin()       { return $this->currentRole() === 'admin'; }
    private function isVendor()      { return $this->currentRole() === 'vendor'; }

    private function generateNumber(): string
    {
        return 'PF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * GET /api/pre-invoices
     */
    public function index(Request $request)
    {
        $status  = $request->query('status');
        $perPage = min((int) $request->query('per_page', 20), 100);

        $query = DB::table('PreInvoices as pi')
            ->leftJoin('Users as buyer', 'pi.IdUser', '=', 'buyer.IdUser')
            ->leftJoin('Users as vendor', 'pi.IdVendor', '=', 'vendor.IdUser')
            ->selectRaw("
                pi.*,
                CONCAT(buyer.FirstName,' ',buyer.LastName) as BuyerName,
                CONCAT(vendor.FirstName,' ',vendor.LastName) as VendorFullName,
                vendor.EntrepriseName as VendorEntreprise,
                vendor.PlatformName as VendorPlatform
            ");

        if ($this->isVendor()) {
            $query->where('pi.IdVendor', $this->currentUserId());
        } elseif (!$this->isAdmin()) {
            $query->where('pi.IdUser', $this->currentUserId());
        }

        if ($status) {
            $query->where('pi.Status', $status);
        }

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('pi.IdPreInvoice')
            ->forPage($request->query('page', 1), $perPage)
            ->get();

        return response()->json([
            'data' => $rows,
            'meta' => ['total' => $total, 'per_page' => $perPage],
        ]);
    }

    /**
     * GET /api/pre-invoices/{id}
     */
    public function show($id)
    {
        $pi = DB::table('PreInvoices')->where('IdPreInvoice', $id)->first();

        if (!$pi) {
            return response()->json(['message' => 'Pré-facture introuvable.'], 404);
        }

        if (!$this->isAdmin() && $pi->IdUser != $this->currentUserId() && $pi->IdVendor != $this->currentUserId()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($pi);
    }

    /**
     * POST /api/pre-invoices
     * Creates a draft pre-invoice from an order, pulling vendor company + platform name.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_order' => 'required|integer|exists:Orders,IdOrder',
            'discount' => 'nullable|numeric|min:0',
            'notes'    => 'nullable|string',
        ]);

        $order = DB::table('Orders as o')
            ->join('Deals as dl', 'o.IdDeal', '=', 'dl.IdDeal')
            ->join('Users as buyer', 'o.IdUser', '=', 'buyer.IdUser')
            ->join('Users as vendor', 'dl.IdUser', '=', 'vendor.IdUser')
            ->where('o.IdOrder', $validated['id_order'])
            ->selectRaw("
                o.*,
                dl.priceDeal,
                dl.titleDeal,
                dl.IdUser as VendorId,
                vendor.EntrepriseName,
                vendor.PlatformName,
                CONCAT(buyer.FirstName,' ',buyer.LastName) as ClientName,
                buyer.Email as ClientEmail,
                buyer.Telephone as ClientPhone,
                buyer.Address as ClientAddress
            ")
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Commande introuvable.'], 404);
        }

        if ($this->isVendor() && $order->VendorId != $this->currentUserId()) {
            return response()->json(['message' => 'Commande non autorisée.'], 403);
        }

        $existing = DB::table('PreInvoices')->where('IdOrder', $order->IdOrder)->first();
        if ($existing) {
            return response()->json(['message' => 'Une pré-facture existe déjà pour cette commande.', 'id_pre_invoice' => $existing->IdPreInvoice], 409);
        }

        $subtotal = (float) str_replace(',', '.', $order->priceDeal ?? 0);
        $discount = $validated['discount'] ?? 0;
        $tax      = round(($subtotal - $discount) * 0.07, 3);
        $deliveryFee = DB::table('Deliveries')->where('IdOrder', $order->IdOrder)->value('DeliveryFee') ?? 0;
        $total    = round($subtotal - $discount + $tax + $deliveryFee, 3);

        $id = DB::table('PreInvoices')->insertGetId([
            'Number'         => $this->generateNumber(),
            'IdOrder'        => $order->IdOrder,
            'IdUser'         => $order->IdUser,
            'IdVendor'       => $order->VendorId,
            'EntrepriseName' => $order->EntrepriseName,
            'PlatformName'   => $order->PlatformName,
            'ClientName'     => $order->ClientName,
            'ClientEmail'    => $order->ClientEmail,
            'ClientPhone'    => $order->ClientPhone,
            'ClientAddress'  => $order->ClientAddress,
            'Subtotal'       => $subtotal,
            'Tax'            => $tax,
            'DeliveryFee'    => $deliveryFee,
            'Discount'       => $discount,
            'Total'          => $total,
            'Status'         => 'draft',
            'Notes'          => $validated['notes'] ?? null,
            'IssuedAt'       => now(),
        ]);

        return response()->json(['id_pre_invoice' => $id, 'created' => true], 201);
    }

    /**
     * PUT /api/pre-invoices/{id}
     */
    public function update(Request $request, $id)
    {
        $pi = DB::table('PreInvoices')->where('IdPreInvoice', $id)->first();

        if (!$pi) {
            return response()->json(['message' => 'Pré-facture introuvable.'], 404);
        }

        if ($pi->Status !== 'draft') {
            return response()->json(['message' => 'Seules les pré-factures en brouillon sont modifiables.'], 422);
        }

        if ($this->isVendor() && $pi->IdVendor != $this->currentUserId()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validated = $request->validate([
            'discount'        => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
            'client_name'     => 'nullable|string|max:255',
            'client_email'    => 'nullable|email',
            'client_phone'    => 'nullable|string|max:50',
            'client_address'  => 'nullable|string|max:255',
        ]);

        $discount = $validated['discount'] ?? $pi->Discount;
        $tax      = round(($pi->Subtotal - $discount) * 0.07, 3);
        $total    = round($pi->Subtotal - $discount + $tax + $pi->DeliveryFee, 3);

        DB::table('PreInvoices')->where('IdPreInvoice', $id)->update([
            'Discount'      => $discount,
            'Tax'           => $tax,
            'Total'         => $total,
            'Notes'         => $validated['notes'] ?? $pi->Notes,
            'ClientName'    => $validated['client_name'] ?? $pi->ClientName,
            'ClientEmail'   => $validated['client_email'] ?? $pi->ClientEmail,
            'ClientPhone'   => $validated['client_phone'] ?? $pi->ClientPhone,
            'ClientAddress' => $validated['client_address'] ?? $pi->ClientAddress,
            'UpdatedAt'     => now(),
        ]);

        return response()->json(['id_pre_invoice' => $id, 'updated' => true]);
    }

    /**
     * POST /api/pre-invoices/{id}/submit
     * Moves draft -> pending (ready for approval)
     */
    public function submit($id)
    {
        $pi = DB::table('PreInvoices')->where('IdPreInvoice', $id)->first();
        if (!$pi) return response()->json(['message' => 'Pré-facture introuvable.'], 404);

        if ($pi->Status !== 'draft') {
            return response()->json(['message' => 'Statut invalide pour soumission.'], 422);
        }

        DB::table('PreInvoices')->where('IdPreInvoice', $id)->update([
            'Status' => 'pending', 'UpdatedAt' => now(),
        ]);

        return response()->json(['id_pre_invoice' => $id, 'status' => 'pending']);
    }

    /**
     * POST /api/pre-invoices/{id}/approve (admin only)
     */
    public function approve($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Action réservée aux administrateurs.'], 403);
        }

        $pi = DB::table('PreInvoices')->where('IdPreInvoice', $id)->first();
        if (!$pi) return response()->json(['message' => 'Pré-facture introuvable.'], 404);

        if (!in_array($pi->Status, ['pending', 'draft'])) {
            return response()->json(['message' => 'Statut invalide pour approbation.'], 422);
        }

        DB::table('PreInvoices')->where('IdPreInvoice', $id)->update([
            'Status' => 'approved', 'ApprovedAt' => now(), 'UpdatedAt' => now(),
        ]);

        DB::table('Notifications')->insert([
            'IdUser'    => $pi->IdVendor,
            'Type'      => 'pre_invoice_approved',
            'Title'     => 'Pré-facture approuvée',
            'Message'   => "Votre pré-facture {$pi->Number} a été approuvée.",
            'IsRead'    => 0,
            'CreatedAt' => now(),
        ]);

        return response()->json(['id_pre_invoice' => $id, 'status' => 'approved']);
    }

    /**
     * POST /api/pre-invoices/{id}/reject (admin only)
     */
    public function reject(Request $request, $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Action réservée aux administrateurs.'], 403);
        }

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        $pi = DB::table('PreInvoices')->where('IdPreInvoice', $id)->first();
        if (!$pi) return response()->json(['message' => 'Pré-facture introuvable.'], 404);

        DB::table('PreInvoices')->where('IdPreInvoice', $id)->update([
            'Status' => 'rejected',
            'RejectionReason' => $validated['reason'],
            'RejectedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        DB::table('Notifications')->insert([
            'IdUser'    => $pi->IdVendor,
            'Type'      => 'pre_invoice_rejected',
            'Title'     => 'Pré-facture rejetée',
            'Message'   => "Votre pré-facture {$pi->Number} a été rejetée : {$validated['reason']}",
            'IsRead'    => 0,
            'CreatedAt' => now(),
        ]);

        return response()->json(['id_pre_invoice' => $id, 'status' => 'rejected']);
    }

    /**
     * POST /api/pre-invoices/{id}/convert
     * Converts an approved pre-invoice into a real Invoice.
     */
    public function convert($id)
    {
        $pi = DB::table('PreInvoices')->where('IdPreInvoice', $id)->first();
        if (!$pi) return response()->json(['message' => 'Pré-facture introuvable.'], 404);

        if ($pi->Status !== 'approved') {
            return response()->json(['message' => 'Seules les pré-factures approuvées peuvent être converties.'], 422);
        }

        if ($this->isVendor() && $pi->IdVendor != $this->currentUserId()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        try {
            DB::beginTransaction();

            $invoiceId = DB::table('Invoices')->insertGetId([
                'IdOrder'      => $pi->IdOrder,
                'IdUser'       => $pi->IdUser,
                'IdVendor'     => $pi->IdVendor,
                'InvoiceNumber'=> 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'ClientName'   => $pi->ClientName,
                'ClientEmail'  => $pi->ClientEmail,
                'Subtotal'     => $pi->Subtotal,
                'Tax'          => $pi->Tax,
                'DeliveryFee'  => $pi->DeliveryFee,
                'Total'        => $pi->Total,
                'Paid'         => 0,
                'CreatedAt'    => now(),
            ]);

            DB::table('PreInvoices')->where('IdPreInvoice', $id)->update([
                'Status'              => 'converted',
                'ConvertedToInvoice'  => $invoiceId,
                'ConvertedAt'         => now(),
                'UpdatedAt'           => now(),
            ]);

            DB::commit();

            return response()->json([
                'id_pre_invoice' => $id,
                'id_invoice'     => $invoiceId,
                'converted'      => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Conversion impossible : ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/pre-invoices/{id}/pdf
     * Returns structured data ready for PDF rendering
     * (actual binary PDF requires barryvdh/laravel-dompdf — see note below).
     */
    public function pdf($id)
    {
        $pi = DB::table('PreInvoices')->where('IdPreInvoice', $id)->first();
        if (!$pi) return response()->json(['message' => 'Pré-facture introuvable.'], 404);

        // composer require barryvdh/laravel-dompdf
        // $pdf = \PDF::loadView('pdf.pre-invoice', ['data' => $pi]);
        // return $pdf->download("{$pi->Number}.pdf");

        return response()->json([
            'message' => 'Installer barryvdh/laravel-dompdf pour générer le PDF binaire.',
            'data'    => $pi,
        ]);
    }
}
