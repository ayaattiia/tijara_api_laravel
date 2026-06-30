<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeliveriesController extends Controller
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
     * GET /api/deliveries
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = DB::table('Deliveries as d')
            ->leftJoin('Transports as t', 'd.IdTransport', '=', 't.IdTransport')
            ->leftJoin('Orders as o', 'd.IdOrder', '=', 'o.IdOrder')
            ->leftJoin('Deals as dl', 'o.IdDeal', '=', 'dl.IdDeal')
            ->leftJoin('Users as u', 'o.IdUser', '=', 'u.IdUser')
            ->selectRaw("
                d.*,
                t.Name as TransportName,
                t.Phone as TransportPhone,
                CONCAT(u.FirstName,' ',u.LastName) as UserClientName,
                u.Email as UserEmail,
                u.Telephone as UserPhone,
                dl.titleDeal as DealTitle,
                CAST(REPLACE(dl.priceDeal, ',', '.') AS DECIMAL(18,3)) as DealPrice
            ");

        if ($this->isVendor()) {
            $query->where('dl.idUser', $this->currentUserId());
        } elseif (!$this->isAdmin()) {
            $query->where('o.IdUser', $this->currentUserId());
        }

        if ($status) {
            $query->where('d.Status', $status);
        }

        $rows = $query
            ->orderByDesc('d.IdDelivery')
            ->get();

        $result = $rows->map(function ($r) {
            return [
                'id_delivery'     => $r->IdDelivery,
                'id_order'        => $r->IdOrder,
                'id_transport'    => $r->IdTransport,
                'transport_name'  => $r->TransportName,
                'transport_phone' => $r->TransportPhone,
                'tracking_number' => $r->TrackingNumber,
                'status'          => $r->Status,
                'address_line'    => $r->AddressLine,
                'city'            => $r->City,
                'postal_code'     => $r->PostalCode,
                'phone'           => $r->Phone,
                'client_name'     => $r->UserClientName,
                'client_email'    => $r->UserEmail,
                'deal_title'      => $r->DealTitle,
                'deal_price'      => $r->DealPrice ?? 0,
                'delivery_fee'    => $r->DeliveryFee,
                'estimated_at'    => $r->EstimatedAt,
                'delivered_at'    => $r->DeliveredAt,
                'note'            => $r->Note,
                'created_at'      => $r->CreatedAt,
                'updated_at'      => $r->UpdatedAt,
            ];
        });

        return response()->json($result);
    }

    /**
     * POST /api/deliveries
     */
    public function store(Request $request)
    {
        $idOrder = $request->input('id_order', $request->input('idOrder'));

        if (!$idOrder) {
            return response()->json([
                'message' => 'id_order requis.'
            ], 400);
        }

        if ($this->isVendor()) {

            $owns = DB::table('Orders as o')
                ->join('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
                ->where('o.IdOrder', $idOrder)
                ->where('d.idUser', $this->currentUserId())
                ->count();

            if (!$owns) {
                return response()->json([
                    'message' => 'Commande non autorisée.'
                ], 403);
            }
        }

        $existing = DB::table('Deliveries')
            ->where('IdOrder', $idOrder)
            ->value('IdDelivery');

        $data = [
            'IdTransport'    => $request->input('id_transport', $request->input('idTransport')),
            'TrackingNumber' => $request->input('tracking_number', $request->input('trackingNumber')),
            'Status'         => $request->input('status', 'pending'),
            'AddressLine'    => $request->input('address_line', $request->input('addressLine')),
            'City'           => $request->input('city'),
            'PostalCode'     => $request->input('postal_code', $request->input('postalCode')),
            'Phone'          => $request->input('phone'),
            'DeliveryFee'    => $request->input('delivery_fee', $request->input('deliveryFee', 0)),
            'Note'           => $request->input('note'),
            'UpdatedAt'      => now(),
        ];

        try {

            if ($existing) {

                DB::table('Deliveries')
                    ->where('IdDelivery', $existing)
                    ->update($data);

                return response()->json([
                    'id_delivery' => $existing,
                    'updated' => true
                ]);
            }

            if (
                empty($data['DeliveryFee']) &&
                !empty($data['IdTransport'])
            ) {
                $fee = DB::table('Transports')
                    ->where('IdTransport', $data['IdTransport'])
                    ->value('DeliveryFee');

                $data['DeliveryFee'] = $fee ?? 0;
            }

            $data['IdOrder'] = $idOrder;
            $data['CreatedAt'] = now();

            $id = DB::table('Deliveries')
                ->insertGetId($data);

            return response()->json([
                'id_delivery' => $id,
                'created' => true
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Création impossible : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/deliveries/{id}
     */
    public function update(Request $request, $id)
    {
        if ($this->isVendor()) {

            $owns = DB::table('Deliveries as dv')
                ->join('Orders as o', 'dv.IdOrder', '=', 'o.IdOrder')
                ->join('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
                ->where('dv.IdDelivery', $id)
                ->where('d.idUser', $this->currentUserId())
                ->count();

            if (!$owns) {
                return response()->json([
                    'message' => 'Livraison non autorisée.'
                ], 403);
            }
        }

        DB::table('Deliveries')
            ->where('IdDelivery', $id)
            ->update([
                'IdTransport'    => $request->input('id_transport'),
                'TrackingNumber' => $request->input('tracking_number'),
                'AddressLine'    => $request->input('address_line'),
                'City'           => $request->input('city'),
                'PostalCode'     => $request->input('postal_code'),
                'Phone'          => $request->input('phone'),
                'DeliveryFee'    => $request->input('delivery_fee'),
                'Note'           => $request->input('note'),
                'UpdatedAt'      => now(),
            ]);

        return response()->json([
            'id_delivery' => $id,
            'updated' => true
        ]);
    }

    /**
     * PATCH /api/deliveries/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');

        if (!$status) {
            return response()->json([
                'message' => 'status requis.'
            ], 400);
        }

        if ($this->isVendor()) {

            $owns = DB::table('Deliveries as dv')
                ->join('Orders as o', 'dv.IdOrder', '=', 'o.IdOrder')
                ->join('Deals as d', 'o.IdDeal', '=', 'd.IdDeal')
                ->where('dv.IdDelivery', $id)
                ->where('d.idUser', $this->currentUserId())
                ->count();

            if (!$owns) {
                return response()->json([
                    'message' => 'Livraison non autorisée.'
                ], 403);
            }
        }

        DB::table('Deliveries')
            ->where('IdDelivery', $id)
            ->update([
                'Status'      => $status,
                'DeliveredAt' => $status === 'delivered'
                    ? now()
                    : DB::raw('DeliveredAt'),
                'UpdatedAt'   => now()
            ]);

        return response()->json([
            'id_delivery' => $id,
            'status' => $status
        ]);
    }
}
