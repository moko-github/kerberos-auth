<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Livewire\Auth;

use Livewire\Component;
use MokoGithub\KerberosAuth\Services\KerberosAuthService;
use MokoGithub\KerberosAuth\Support\Kerberos;

class RequestAccess extends Component
{
    public string $kerberos = '';

    public ?int $user_id = null;

    public string $justification = '';

    public bool $submitted = false;

    public function mount(): void
    {
        $this->kerberos = session('pending_kerberos', '');
        $this->user_id = session('pending_user_id');

        if (empty($this->kerberos)) {
            $this->redirect(route(Kerberos::loginRoute()), navigate: true);
        }
    }

    protected function rules(): array
    {
        return [
            'justification' => ['required', 'string', 'min:20', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'justification.required' => __('kerberos-auth::kerberos.validation.justification_required'),
            'justification.min' => __('kerberos-auth::kerberos.validation.justification_min'),
            'justification.max' => __('kerberos-auth::kerberos.validation.justification_max'),
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $userModel = Kerberos::userModel();

        $user = $userModel::find($this->user_id);

        if (! $user) {
            session()->flash('error', __('kerberos-auth::kerberos.flash.error_generic'));

            return;
        }

        $kerberosService = app(KerberosAuthService::class);
        $kerberosService->createAccessRequest($user, $this->kerberos, $this->justification);

        session()->forget(['pending_kerberos', 'pending_user_id']);

        $this->submitted = true;
    }

    public function render(): mixed
    {
        return view('kerberos-auth::livewire.auth.request-access')
            ->layout(config('kerberos.layout') ?? 'kerberos-auth::layouts.guest');
    }
}
