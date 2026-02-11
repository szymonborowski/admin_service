<?php

namespace App\Filament\Pages\Auth;

use App\Services\SsoService;
use Filament\Pages\SimplePage;
use Illuminate\Support\Str;

class SsoLogin extends SimplePage
{
    protected static string $view = 'filament-panels::pages.auth.login';

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirect('/admin');
            return;
        }

        $state = Str::random(40);
        session(['sso_state' => $state]);

        $ssoService = app(SsoService::class);

        $this->redirect($ssoService->getAuthorizeUrl($state));
    }
}
