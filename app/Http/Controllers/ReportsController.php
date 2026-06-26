<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
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

    /**
     * GET /api/reports/overview
     * Admin => Global statistics
     * Vendor => Own statistics only
     */
    public function overview()
    {
        $userId = $this->currentUserId();

        $vendorFilter = $this->isAdmin()
            ? ''
            : ' AND dl.idUser = ?';

        $bindings = $this->isAdmin()
            ? []
            : [$userId];

        $stats = DB::selectOne("
            WITH OrdersView AS
            (
                SELECT
                    o.IdOrder,
                    o.IdUser,
                    o.IdDeal,
                    o.DateTimeCommand,
                    o.Active,

                    TRY_CAST(REPLACE(dl.priceDeal, ',', '.') AS DECIMAL(18,3)) AS Total,
                    dl.idUser AS IdVendor,

                    ISNULL(dv.Status,'pending') AS Status,
                    ISNULL(iv.Status,'unpaid') AS PaymentStatus

                FROM Orders o
                LEFT JOIN Deals dl ON o.IdDeal = dl.IdDeal
                LEFT JOIN Deliveries dv ON dv.IdOrder = o.IdOrder
                LEFT JOIN Invoices iv ON iv.IdOrder = o.IdOrder

                WHERE 1=1 {$vendorFilter}
            )

            SELECT
                (SELECT COUNT(*) FROM OrdersView) AS TotalOrders,

                (SELECT COUNT(*)
                 FROM OrdersView
                 WHERE Status='delivered') AS DeliveredOrders,

                (SELECT COUNT(*)
                 FROM OrdersView
                 WHERE Status='pending') AS PendingOrders,

                (SELECT ISNULL(SUM(Total),0)
                 FROM OrdersView) AS TotalRevenue,

                (SELECT ISNULL(SUM(Total),0)
                 FROM OrdersView
                 WHERE PaymentStatus='paid') AS PaidRevenue,

                (SELECT COUNT(*) FROM Deals) AS TotalProducts,

                (SELECT COUNT(*)
                 FROM Users
                 WHERE Active=1) AS ActiveUsers
        ", $bindings);

        return response()->json($stats);
    }

    /**
     * GET /api/reports/sales-by-month
     */
    public function salesByMonth()
    {
        $query = DB::table('Orders as o')
            ->leftJoin('Deals as dl', 'o.IdDeal', '=', 'dl.IdDeal')
            ->selectRaw("
                FORMAT(o.DateTimeCommand,'yyyy-MM') AS Month,
                COUNT(*) AS Orders,
                ISNULL(
                    SUM(
                        TRY_CAST(REPLACE(dl.priceDeal, ',', '.') AS DECIMAL(18,3))
                    ),
                0) AS Revenue
            ")
            ->whereRaw("
                o.DateTimeCommand >= DATEADD(MONTH,-12,GETDATE())
            ");

        if (!$this->isAdmin()) {
            $query->where('dl.idUser', $this->currentUserId());
        }

        $data = $query
            ->groupByRaw("FORMAT(o.DateTimeCommand,'yyyy-MM')")
            ->orderBy('Month')
            ->limit(12)
            ->get();

        return response()->json($data);
    }

    /**
     * GET /api/reports/top-products
     */
    public function topProducts(Request $request)
    {
        $limit = (int)$request->get('limit', 10);

        $query = DB::table('Deals as d')
            ->selectRaw("
                d.IdDeal AS Id,
                d.titleDeal AS Title,
                d.priceDeal AS Price,
                d.imageDeal AS Image,

                (
                    SELECT COUNT(*)
                    FROM Orders o
                    WHERE o.IdDeal = d.IdDeal
                ) AS Sold
            ");

        if (!$this->isAdmin()) {
            $query->where('d.idUser', $this->currentUserId());
        }

        $products = $query
            ->orderByDesc('Sold')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }

    /**
     * GET /api/reports/top-customers
     * Admin only
     */
    public function topCustomers(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $limit = (int)$request->get('limit', 10);

        $customers = DB::table('Users as u')
            ->leftJoin('Orders as o', 'o.IdUser', '=', 'u.IdUser')
            ->leftJoin('Deals as dl', 'o.IdDeal', '=', 'dl.IdDeal')
            ->selectRaw("
                u.IdUser,
                u.Email,
                CONCAT(u.FirstName,' ',u.LastName) AS Name,

                COUNT(o.IdOrder) AS Orders,

                ISNULL(
                    SUM(
                        TRY_CAST(REPLACE(dl.priceDeal, ',', '.') AS DECIMAL(18,3))
                    ),
                0) AS TotalSpent
            ")
            ->groupBy(
                'u.IdUser',
                'u.Email',
                'u.FirstName',
                'u.LastName'
            )
            ->orderByDesc('TotalSpent')
            ->limit($limit)
            ->get();

        return response()->json($customers);
    }
}
