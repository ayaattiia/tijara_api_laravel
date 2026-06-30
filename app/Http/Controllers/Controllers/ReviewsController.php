<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewsController extends Controller
{
    private function currentUserId()
    {
        return Auth::id();
    }

    /**
     * GET /api/reviews?type=deal&targetId=5
     */
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'targetId' => 'required|integer'
        ]);

        $reviews = DB::table('Reviews as r')
            ->join('Users as u', 'r.IdUser', '=', 'u.IdUser')
            ->selectRaw("
                r.*,
                CONCAT(u.FirstName,' ',u.LastName) AS AuthorName
            ")
            ->where('r.TargetType', $request->type)
            ->where('r.TargetId', $request->targetId)
            ->where('r.Active', 1)
            ->orderByDesc('r.IdReview')
            ->get();

        return response()->json($reviews);
    }

    /**
     * GET /api/reviews/summary?type=deal&targetId=5
     */
    public function summary(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'targetId' => 'required|integer'
        ]);

        $summary = DB::table('Reviews')
            ->selectRaw("
                COUNT(*) as total,
                AVG(CAST(Rating AS FLOAT)) as average,
                SUM(CASE WHEN Rating=5 THEN 1 ELSE 0 END) as r5,
                SUM(CASE WHEN Rating=4 THEN 1 ELSE 0 END) as r4,
                SUM(CASE WHEN Rating=3 THEN 1 ELSE 0 END) as r3,
                SUM(CASE WHEN Rating=2 THEN 1 ELSE 0 END) as r2,
                SUM(CASE WHEN Rating=1 THEN 1 ELSE 0 END) as r1
            ")
            ->where('TargetType', $request->type)
            ->where('TargetId', $request->targetId)
            ->where('Active', 1)
            ->first();

        return response()->json($summary);
    }

    /**
     * POST /api/reviews?type=deal&targetId=5
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'targetId' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $userId = $this->currentUserId();

        if (!$userId) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        try {

            $id = DB::table('Reviews')->insertGetId([
                'IdUser'     => $userId,
                'TargetType' => $request->type,
                'TargetId'   => $request->targetId,
                'Rating'     => $request->rating,
                'Comment'    => $request->comment,
                'CreatedAt'  => now(),
                'Active'     => 1
            ]);

            $review = DB::table('Reviews as r')
                ->join('Users as u', 'r.IdUser', '=', 'u.IdUser')
                ->selectRaw("
                    r.*,
                    CONCAT(u.FirstName,' ',u.LastName) AS AuthorName
                ")
                ->where('r.IdReview', $id)
                ->first();

            return response()->json($review);
        } catch (\Exception $e) {

            DB::table('Reviews')
                ->where('IdUser', $userId)
                ->where('TargetType', $request->type)
                ->where('TargetId', $request->targetId)
                ->update([
                    'Rating'    => $request->rating,
                    'Comment'   => $request->comment,
                    'CreatedAt' => now()
                ]);

            return response()->json([
                'message' => 'Avis mis à jour.'
            ]);
        }
    }

    /**
     * DELETE /api/reviews/{id}
     */
    public function destroy($id)
    {
        $deleted = DB::table('Reviews')
            ->where('IdReview', $id)
            ->where('IdUser', $this->currentUserId())
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Review not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Avis supprimé.'
        ]);
    }

    /**
     * GET /api/reviews/my
     */
    public function myReviews()
    {
        $reviews = DB::table('Reviews')
            ->where('IdUser', $this->currentUserId())
            ->where('Active', 1)
            ->orderByDesc('CreatedAt')
            ->get();

        return response()->json($reviews);
    }
}
