<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileAuthService;
use App\Services\Mobile\MobileDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobilePortalController extends Controller
{
    protected $authService;
    protected $dashboardService;

    public function __construct(MobileAuthService $authService, MobileDashboardService $dashboardService)
    {
        $this->authService = $authService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Render the mobile login page.
     */
    public function showLogin()
    {
        if (Auth::guard('web')->check() && strtoupper((string) (Auth::user()->Status ?? '')) === 'ACTIVE') {
            return redirect()->route('mobile.dashboard');
        }

        return view('humanresources.mobile.login');
    }

    /**
     * Process mobile web login and redirect to the mobile landing dashboard.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
            'remember' => 'nullable|boolean',
        ]);

        $result = $this->authService->attemptWebLogin(
            trim($validated['username']),
            $validated['password'],
            (bool) ($validated['remember'] ?? false),
            $request
        );

        if (!$result['success']) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => $result['message']]);
        }

        return redirect()->intended(route('mobile.dashboard'));
    }

    /**
     * Logout mobile web session.
     */
    public function logout(Request $request)
    {
        $this->authService->logoutWeb($request);

        return redirect()->route('mobile.login')->with('status', 'Anda berhasil keluar.');
    }

    /**
     * Render the main Mobile Landing Page & Unified Dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $dashboard = $this->dashboardService->getDashboardData($user);

        return view('humanresources.mobile.dashboard', compact('user', 'dashboard'));
    }

    /**
     * Render the ESS Hub (7 self-service request modules).
     */
    public function ess(Request $request)
    {
        $user = Auth::user();
        $ess = $this->dashboardService->getEssContext($user);

        return view('humanresources.mobile.ess', compact('user', 'ess'));
    }

    /**
     * Render ESS Request Kehadiran / Koreksi Absensi (aligned with attendant/portal.blade.php).
     */
    public function essCorrection(Request $request)
    {
        $user = Auth::user();

        return view('humanresources.mobile.ess-correction', compact('user'));
    }

    /**
     * Render ESS Pengajuan Lembur (aligned with attendant/portal.blade.php).
     */
    public function essOvertime(Request $request)
    {
        $user = Auth::user();
        $ess = $this->dashboardService->getEssContext($user);

        return view('humanresources.mobile.ess-overtime', [
            'user' => $user,
            'locations' => $ess['locations'],
        ]);
    }

    /**
     * Render ESS Pengajuan Pertukaran Shift (aligned with attendant/portal.blade.php).
     */
    public function essShiftExchange(Request $request)
    {
        $user = Auth::user();
        $ess = $this->dashboardService->getEssContext($user);

        return view('humanresources.mobile.ess-shift-exchange', [
            'user' => $user,
            'availableShifts' => $ess['availableShifts'],
            'colleagues' => $ess['colleagues'],
            'schedule' => $ess['schedule'],
        ]);
    }

    /**
     * Render the Sanctum Active Device / Token Management page.
     */
    public function sessions(Request $request)
    {
        $user = Auth::user();
        $tokens = $this->authService->getUserActiveTokens($user);

        return view('humanresources.mobile.sessions', compact('user', 'tokens'));
    }

    /**
     * Revoke a specific Sanctum API token from the mobile web portal.
     */
    public function revokeSession(Request $request, int $id)
    {
        $user = Auth::user();
        $revoked = $this->authService->revokeToken($user, $id, $request);

        if (!$revoked) {
            return redirect()->route('mobile.sessions')->with('error', 'Token sesi tidak ditemukan.');
        }

        return redirect()->route('mobile.sessions')->with('status', 'Sesi perangkat berhasil dicabut.');
    }

    /**
     * Revoke all Sanctum API tokens for the authenticated user.
     */
    public function revokeAllSessions(Request $request)
    {
        $user = Auth::user();
        $count = $this->authService->revokeAllTokens($user, $request);

        return redirect()->route('mobile.sessions')->with('status', "{$count} sesi perangkat API berhasil dicabut.");
    }
}
