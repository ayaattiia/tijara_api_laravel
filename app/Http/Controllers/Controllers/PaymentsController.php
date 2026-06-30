<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{
    private function currentUserId()
    {
        return Auth::id();
    }

    private function isAdmin()
    {
        return Auth::user()?->role === 'admin';
    }

    /**
     * POST /api/payments
     * Mock payment (no real gateway)
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.001',
            'method' => 'required|string|max:50',
            'id_order' => 'nullable|integer'
        ]);

        $userId = $this->currentUserId();

        if (!$userId) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $txId = 'TIJ-' . substr((string) now()->timestamp . rand(100000, 999999), -10);
        $reference = 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $id = DB::table('Payments')->insertGetId([
            'IdUser'        => $userId,
            'IdOrder'       => $request->input('id_order'),
            'Amount'        => $request->input('amount'),
            'Method'        => $request->input('method'),
            'Status'        => 'paid',
            'Reference'     => $reference,
            'TransactionId' => $txId,
            'PaidAt'        => now(),
        ]);

        if ($request->filled('id_order')) {

            DB::table('Orders')
                ->where('IdOrder', $request->input('id_order'))
                ->update([
                    'PaymentStatus' => 'paid'
                ]);
        }

        return response()->json([
            'id_payment'     => $id,
            'reference'      => $reference,
            'transaction_id' => $txId,
            'status'         => 'paid',
            'message'        => 'Paiement confirmé.'
        ]);
    }

    /**
     * GET /api/payments/mine
     */
    public function mine()
    {
        $payments = DB::table('Payments')
            ->where('IdUser', $this->currentUserId())
            ->orderByDesc('IdPayment')
            ->get();

        return response()->json($payments);
    }

    /**
     * GET /api/payments
     * Admin only
     */
    public function index(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $query = DB::table('Payments as p')
            ->join('Users as u', 'p.IdUser', '=', 'u.IdUser')
            ->selectRaw("
                p.*,
                u.Email,
                CONCAT(u.FirstName,' ',u.LastName) as UserName
            ");

        if ($request->filled('status')) {
            $query->where('p.Status', $request->status);
        }

        $payments = $query
            ->orderByDesc('p.IdPayment')
            ->get();

        return response()->json($payments);
    }

    /**
     * POST /api/payments/{id}/refund
     * Admin only
     */
    public function refund($id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $updated = DB::table('Payments')
            ->where('IdPayment', $id)
            ->update([
                'Status' => 'refunded'
            ]);

        if (!$updated) {
            return response()->json([
                'message' => 'Paiement introuvable.'
            ], 404);
        }

        return response()->json([
            'message' => 'Paiement remboursé.'
        ]);
    }
}
