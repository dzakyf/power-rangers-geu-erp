<?php

namespace App\Services\Mobile;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MobileAuthService
{
    /**
     * Authenticate user for Mobile Web (session cookie + Sanctum stateful).
     *
     * @param  string  $username
     * @param  string  $password
     * @param  bool    $remember
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function attemptWebLogin(string $username, string $password, bool $remember, Request $request): array
    {
        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, (string) $user->password)) {
            $this->logAuthEvent($user ? (int) $user->id : null, $username, 'LOGIN_FAILED', 'WEB', 'Browser', $request, 'Invalid credentials');

            return [
                'success' => false,
                'message' => 'Username atau password salah.',
            ];
        }

        if (strtoupper((string) ($user->Status ?? '')) !== 'ACTIVE') {
            $this->logAuthEvent((int) $user->id, $username, 'LOGIN_FAILED', 'WEB', 'Browser', $request, 'Inactive user status');

            return [
                'success' => false,
                'message' => 'Akun Anda berstatus non-aktif. Silakan hubungi administrator HCGA.',
            ];
        }

        Auth::guard('web')->login($user, $remember);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $this->logAuthEvent((int) $user->id, $user->username, 'LOGIN_SUCCESS', 'WEB', 'Browser', $request);

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    /**
     * Logout Mobile Web session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function logoutWeb(Request $request): void
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            $this->logAuthEvent((int) $user->id, $user->username, 'LOGOUT', 'WEB', 'Browser', $request);
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    /**
     * Authenticate user for Mobile REST API and issue a Sanctum Personal Access Token.
     *
     * @param  string  $username
     * @param  string  $password
     * @param  string  $deviceName
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function attemptApiLogin(string $username, string $password, string $deviceName, Request $request): array
    {
        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, (string) $user->password)) {
            $this->logAuthEvent($user ? (int) $user->id : null, $username, 'LOGIN_FAILED', 'API', $deviceName, $request, 'Invalid credentials');

            return [
                'success' => false,
                'status' => 401,
                'message' => 'Username atau password salah.',
            ];
        }

        if (strtoupper((string) ($user->Status ?? '')) !== 'ACTIVE') {
            $this->logAuthEvent((int) $user->id, $username, 'LOGIN_FAILED', 'API', $deviceName, $request, 'Inactive user status');

            return [
                'success' => false,
                'status' => 403,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator HCGA.',
            ];
        }

        $abilities = $this->resolveTokenAbilities($user);
        $newToken = $user->createToken($deviceName, $abilities);

        // Record IP and User-Agent on the created personal_access_tokens row
        if ($newToken->accessToken) {
            $newToken->accessToken->ip_address = $request->ip();
            $newToken->accessToken->user_agent = substr((string) $request->userAgent(), 0, 500);
            $newToken->accessToken->save();
        }

        $expirationMinutes = (int) config('sanctum.expiration', 10080);
        $expiresAt = $expirationMinutes > 0
            ? now()->addMinutes($expirationMinutes)->toIso8601String()
            : null;

        $this->logAuthEvent((int) $user->id, $user->username, 'TOKEN_ISSUED', 'API', $deviceName, $request);

        return [
            'success' => true,
            'status' => 200,
            'token' => $newToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'abilities' => $abilities,
            'user' => $this->formatUserPayload($user),
        ];
    }

    /**
     * Resolve Sanctum token abilities for the user.
     *
     * @param  \App\User  $user
     * @return array
     */
    public function resolveTokenAbilities(User $user): array
    {
        $abilities = ['mobile:read', 'mobile:write'];

        $hasApprovalAccess = false;
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Admin', 'Super Admin', 'HCGA', 'Manager', 'VP'])) {
            $hasApprovalAccess = true;
        }

        if (!$hasApprovalAccess) {
            $hasApprovalAccess = DB::table('approval_task_candidate as tc')
                ->where('tc.User_ID', $user->id)
                ->exists();
        }

        if (!$hasApprovalAccess) {
            $hasApprovalAccess = DB::table('master_hr_approval')
                ->where('username', $user->username ?? '')
                ->where('Status', 'ACTIVE')
                ->exists();
        }

        if ($hasApprovalAccess) {
            $abilities[] = 'mobile:approve';
        }

        return $abilities;
    }

    /**
     * List active Sanctum tokens for the given user.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Support\Collection
     */
    public function getUserActiveTokens(User $user)
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            return collect([]);
        }

        $expirationMinutes = (int) config('sanctum.expiration', 10080);

        return $user->tokens()
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($token) use ($expirationMinutes) {
                $expiresAt = ($expirationMinutes > 0 && $token->created_at)
                    ? $token->created_at->copy()->addMinutes($expirationMinutes)
                    : null;

                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities ?? [],
                    'ip_address' => $token->ip_address,
                    'user_agent' => $token->user_agent,
                    'last_used_at' => $token->last_used_at ? $token->last_used_at->toIso8601String() : null,
                    'created_at' => $token->created_at ? $token->created_at->toIso8601String() : null,
                    'expires_at' => $expiresAt ? $expiresAt->toIso8601String() : null,
                    'is_expired' => $expiresAt ? $expiresAt->isPast() : false,
                ];
            });
    }

    /**
     * Revoke a specific token owned by the user.
     *
     * @param  \App\User  $user
     * @param  int  $tokenId
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function revokeToken(User $user, int $tokenId, Request $request): bool
    {
        $token = $user->tokens()->where('id', $tokenId)->first();
        if (!$token) {
            return false;
        }

        $deviceName = $token->name;
        $token->delete();
        $this->logAuthEvent((int) $user->id, $user->username, 'TOKEN_REVOKED', 'API', $deviceName, $request, "Revoked token #{$tokenId}");

        return true;
    }

    /**
     * Revoke all tokens owned by the user.
     *
     * @param  \App\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    public function revokeAllTokens(User $user, Request $request): int
    {
        $count = $user->tokens()->count();
        $user->tokens()->delete();
        $this->logAuthEvent((int) $user->id, $user->username, 'TOKEN_REVOKED', 'API', 'ALL_DEVICES', $request, "Revoked all {$count} tokens");

        return $count;
    }

    /**
     * Format user payload safely without exposing sensitive fields.
     *
     * @param  \App\User  $user
     * @return array
     */
    public function formatUserPayload(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'username' => $user->username,
            'name' => $user->Name ?? $user->username,
            'emp_id' => $user->Emp_ID ?? null,
            'department' => $user->Department ?? null,
            'position' => $user->Position ?? null,
            'location' => $user->Location ?? null,
            'status' => $user->Status ?? null,
        ];
    }

    /**
     * Write an audit log row to mobile_auth_logs if table exists.
     *
     * @param  int|null  $userId
     * @param  string|null  $username
     * @param  string  $event
     * @param  string  $channel
     * @param  string|null  $deviceName
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $notes
     * @return void
     */
    protected function logAuthEvent(
        ?int $userId,
        ?string $username,
        string $event,
        string $channel,
        ?string $deviceName,
        Request $request,
        ?string $notes = null
    ): void {
        try {
            if (!Schema::hasTable('mobile_auth_logs')) {
                return;
            }

            DB::table('mobile_auth_logs')->insert([
                'user_id' => $userId,
                'username' => $username,
                'event' => $event,
                'channel' => $channel,
                'device_name' => substr((string) $deviceName, 0, 150),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'notes' => $notes,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Do not block auth flow if audit log insert fails
        }
    }
}
