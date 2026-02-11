<?php

namespace App\Http\Responses;

use App\Services\SsoService;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class SsoLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $ssoService = app(SsoService::class);

        return redirect($ssoService->getLogoutUrl('https://admin.microservices.local/admin/login'));
    }
}
