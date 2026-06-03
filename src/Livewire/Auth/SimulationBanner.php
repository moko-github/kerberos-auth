<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use MokoGithub\KerberosAuth\Services\KerberosAuthService;
use MokoGithub\KerberosAuth\Support\Kerberos;

class SimulationBanner extends Component
{
    public ?string $currentSimulation = null;

    public function mount(): void
    {
        $this->loadSimulation();
    }

    public function loadSimulation(): void
    {
        $kerberosService = app(KerberosAuthService::class);
        $this->currentSimulation = $kerberosService->getSimulatedKerberos();
    }

    public function disable(): void
    {
        $kerberosService = app(KerberosAuthService::class);
        $kerberosService->disableSimulation();
        $this->currentSimulation = null;

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        redirect()->route(Kerberos::loginRoute())
            ->with('success', 'Simulation désactivée. Veuillez vous reconnecter.');
    }

    public function getIsActiveProperty(): bool
    {
        return config('kerberos.simulation_mode', false)
            && ! app()->environment('production')
            && ! empty($this->currentSimulation);
    }

    public function render(): mixed
    {
        return view('kerberos-auth::livewire.auth.simulation-banner');
    }
}
