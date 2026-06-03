<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Livewire\Auth;

use Livewire\Component;
use MokoGithub\KerberosAuth\Support\Kerberos;

class AccessDenied extends Component
{
    public string $kerberos = '';

    public function mount(): void
    {
        $this->kerberos = session('unknown_kerberos', '');

        if (empty($this->kerberos)) {
            $this->redirect(route(Kerberos::loginRoute()), navigate: true);
        }
    }

    public function backToLogin(): void
    {
        session()->forget('unknown_kerberos');

        $this->redirect(route(Kerberos::loginRoute()), navigate: true);
    }

    public function render(): mixed
    {
        return view('kerberos-auth::livewire.auth.access-denied')
            ->layout(config('kerberos.layout') ?? 'kerberos-auth::layouts.guest');
    }
}
