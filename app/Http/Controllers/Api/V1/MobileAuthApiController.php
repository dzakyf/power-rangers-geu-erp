<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileAuthService;
use Illuminate\Http\Request;

class MobileAuthApiController extends Controller
{
    protected $authService;

    public function __construct(MobileAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * POST /api/v1/mobile/auth/login
     * Authenticate credentials and issue a Sanctum Bearer Token.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:120',
        ]);

        $deviceName = trim((string) ($validated['device_name'] ?? 'HCGA-Mobile-Client'));
        if ($deviceName === '') {
            $deviceName = 'HCGA-Mobile-Client';
        }

        $result = $this->authService->attemptApiLogin(
            trim($validated['username']),
            $validated['password'],
            $deviceName,
            $request
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_at' => $result['expires_at'],
                'abilities' => $result['abilities'],
                'user' => $result['user'],
            ],
        ], 200);
    }

    /**
     * GET /api/v1/mobile/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->authService->formatUserPayload($user),
                'abilities' => $this->authService->resolveTokenAbilities($user),
            ],
        ]);
    }

    /**
     * GET /api/v1/mobile/auth/tokens
     */
    public function tokens(Request $request)
    {
        $tokens = $this->authService->getUserActiveTokens($request->user());

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }

    /**
     * DELETE /api/v1/mobile/auth/tokens/{id}
     */
    public function revokeToken(Request $request, int $id)
    {
        $revoked = $this->authService->revokeToken($request->user(), $id, $request);

        if (!$revoked) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil dicabut.',
        ]);
    }

    /**
     * POST /api/v1/mobile/auth/logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user ? $user->currentAccessToken() : null;

        if ($token && method_exists($token, 'delete')) {
            $this->authService->revokeToken($user, (int) $token->id, $request);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * POST /api/v1/mobile/auth/logout-all
     */
    public function logoutAll(Request $request)
    {
        $count = $this->authService->revokeAllTokens($request->user(), $request);

        return response()->json([
            'success' => true,
            'message' => "{$count} token perangkat berhasil dicabut.",
        ]);
    }
}
