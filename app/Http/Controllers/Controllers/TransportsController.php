<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransportsController extends Controller
{
    /**
     * GET /api/transports
     * Public list of carriers
     */
    public function index(Request $request)
    {
        $onlyActive = filter_var(
            $request->query('onlyActive', false),
            FILTER_VALIDATE_BOOLEAN
        );

        $query = DB::table('Transports');

        if ($onlyActive) {
            $query->where('Active', 1);
        }

        $transports = $query
            ->orderBy('Name')
            ->get();

        return response()->json($transports);
    }

    /**
     * GET /api/transports/{id}
     */
    public function show($id)
    {
        $transport = DB::table('Transports')
            ->where('IdTransport', $id)
            ->first();

        if (!$transport) {
            return response()->json([
                'message' => 'Transporteur introuvable.'
            ], 404);
        }

        return response()->json($transport);
    }

    /**
     * POST /api/transports
     * Admin only
     */
    public function store(Request $request)
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $validated = $request->validate([
            'Name'        => 'required|string|max:255',
            'Logo'        => 'nullable|string',
            'Phone'       => 'nullable|string|max:50',
            'Email'       => 'nullable|email|max:255',
            'DeliveryFee' => 'nullable|numeric',
            'FreeFrom'    => 'nullable|numeric',
            'Zones'       => 'nullable|string',
            'Active'      => 'nullable|boolean'
        ]);

        $id = DB::table('Transports')->insertGetId([
            'Name'        => $validated['Name'],
            'Logo'        => $validated['Logo'] ?? null,
            'Phone'       => $validated['Phone'] ?? null,
            'Email'       => $validated['Email'] ?? null,
            'DeliveryFee' => $validated['DeliveryFee'] ?? 0,
            'FreeFrom'    => $validated['FreeFrom'] ?? 0,
            'Zones'       => $validated['Zones'] ?? null,
            'Active'      => $validated['Active'] ?? 1,
        ]);

        return response()->json([
            'idTransport' => $id
        ], 201);
    }

    /**
     * PUT /api/transports/{id}
     * Admin only
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $transport = DB::table('Transports')
            ->where('IdTransport', $id)
            ->first();

        if (!$transport) {
            return response()->json([
                'message' => 'Transporteur introuvable.'
            ], 404);
        }

        $validated = $request->validate([
            'Name'        => 'required|string|max:255',
            'Logo'        => 'nullable|string',
            'Phone'       => 'nullable|string|max:50',
            'Email'       => 'nullable|email|max:255',
            'DeliveryFee' => 'nullable|numeric',
            'FreeFrom'    => 'nullable|numeric',
            'Zones'       => 'nullable|string',
            'Active'      => 'nullable|boolean'
        ]);

        DB::table('Transports')
            ->where('IdTransport', $id)
            ->update([
                'Name'        => $validated['Name'],
                'Logo'        => $validated['Logo'] ?? null,
                'Phone'       => $validated['Phone'] ?? null,
                'Email'       => $validated['Email'] ?? null,
                'DeliveryFee' => $validated['DeliveryFee'] ?? 0,
                'FreeFrom'    => $validated['FreeFrom'] ?? 0,
                'Zones'       => $validated['Zones'] ?? null,
                'Active'      => $validated['Active'] ?? 1,
            ]);

        return response()->json([
            'message' => 'Transporteur mis à jour.'
        ]);
    }

    /**
     * PATCH /api/transports/{id}/toggle
     * Admin only
     */
    public function toggle($id)
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $transport = DB::table('Transports')
            ->where('IdTransport', $id)
            ->first();

        if (!$transport) {
            return response()->json([
                'message' => 'Transporteur introuvable.'
            ], 404);
        }

        DB::table('Transports')
            ->where('IdTransport', $id)
            ->update([
                'Active' => $transport->Active ? 0 : 1
            ]);

        return response()->json([
            'message' => 'Statut modifié.'
        ]);
    }

    /**
     * DELETE /api/transports/{id}
     * Admin only
     */
    public function destroy($id)
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $deleted = DB::table('Transports')
            ->where('IdTransport', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Transporteur introuvable.'
            ], 404);
        }

        return response()->json([
            'message' => 'Transporteur supprimé.'
        ]);
    }
}
