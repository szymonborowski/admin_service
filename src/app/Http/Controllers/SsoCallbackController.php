<?php

namespace App\Http\Controllers;

use App\Models\ApiUser;
use App\Services\SsoService;
use App\Services\UsersApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoCallbackController extends Controller
{
    public function __construct(
        private SsoService $ssoService,
        private UsersApiService $usersApiService,
    ) {}

    public function callback(Request $request)
    {
        if ($request->input('state') !== session('sso_state')) {
            return redirect('/admin/login')->with('error', 'Invalid state parameter.');
        }

        session()->forget('sso_state');

        if ($request->has('error')) {
            return redirect('/admin/login');
        }

        $tokenData = $this->ssoService->exchangeCodeForToken($request->input('code'));

        if (!$tokenData || !isset($tokenData['access_token'])) {
            return redirect('/admin/login')->with('error', 'Failed to obtain access token.');
        }

        $ssoUser = $this->ssoService->getUser($tokenData['access_token']);

        if (!$ssoUser || !isset($ssoUser['id'])) {
            return redirect('/admin/login')->with('error', 'Failed to fetch user data.');
        }

        // Fetch full user data with roles from Users service
        $userData = $this->usersApiService->getUser((int) $ssoUser['id']);

        if (!$userData) {
            return redirect('/admin/login')->with('error', 'User not found.');
        }

        $apiUser = new ApiUser($userData);

        if (!$apiUser->hasRole('admin')) {
            return redirect('/admin/login')->with('error', 'Access denied. Admin role required.');
        }

        Auth::login($apiUser);
        $request->session()->regenerate();

        return redirect('/admin');
    }
}
