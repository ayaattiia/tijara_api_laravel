<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * PRIORITÉ 1 — CORRIGÉ
 *
 * Ancien register() utilisait 'name' (colonne inexistante dans Users)
 * et validait unique:users (mauvaise table → table s'appelle 'Users').
 * Corrigé pour correspondre au vrai schéma : FirstName, LastName, Role.
 */
class AuthController extends Controller
{
    // ── POST /api/login ──────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('Active', 1)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    // ── POST /api/register ───────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName'  => 'required|string|max:100',
            'email'     => 'required|email|unique:Users,email',   // table Users (majuscule)
            'password'  => 'required|string|min:8|confirmed',
            'Telephone' => 'nullable|string|max:50',
            'Role'      => 'nullable|in:admin,vendor,user',
        ]);

        $user = User::create([
            'FirstName' => $request->FirstName,
            'LastName'  => $request->LastName,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'Telephone' => $request->Telephone,
            'Role'      => $request->Role ?? 'user',
            'Active'    => 1,
            'CreatedAt' => now(),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    // ── GET /api/me ──────────────────────────────────────────────
    public function me(): JsonResponse
    {
        return response()->json($this->formatUser(Auth::user()));
    }

    // ── POST /api/logout ─────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    // ── Private ──────────────────────────────────────────────────
    private function formatUser(User $user): array
    {
        return [
            'IdUser'    => $user->IdUser,
            'FirstName' => $user->FirstName,
            'LastName'  => $user->LastName,
            'FullName'  => $user->name,           // accessor FirstName + LastName
            'email'     => $user->email,
            'Role'      => $user->Role,
            'Telephone' => $user->Telephone,
            'Active'    => $user->Active,
        ];
    }
}
