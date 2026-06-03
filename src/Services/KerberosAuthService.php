<?php

namespace MokoGithub\KerberosAuth\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use MokoGithub\KerberosAuth\Contracts\UserAccessCheckInterface;
use MokoGithub\KerberosAuth\DTOs\AuthResult;
use MokoGithub\KerberosAuth\Models\AccessRequest;
use MokoGithub\KerberosAuth\Models\KerberosAttempt;
use MokoGithub\KerberosAuth\Notifications\NewAccessRequestNotification;
use MokoGithub\KerberosAuth\Notifications\UnknownKerberosAttemptNotification;
use MokoGithub\KerberosAuth\Support\Kerberos;

class KerberosAuthService
{
    public function getKerberosIdentifier(): ?string
    {
        if (config('kerberos.simulation_mode') && session()->has('simulated_kerberos')) {
            return session('simulated_kerberos');
        }

        $serverVar = config('kerberos.server_variable', 'REMOTE_USER');

        return $_SERVER[$serverVar] ?? null;
    }

    public function authenticate(): AuthResult
    {
        if (! config('kerberos.enabled') && ! $this->isSimulationActive()) {
            return AuthResult::noKerberos();
        }

        $kerberos = $this->getKerberosIdentifier();

        if (empty($kerberos)) {
            return AuthResult::noKerberos();
        }

        $userModel = Kerberos::userModel();

        $user = $userModel::where('kerberos', $kerberos)->first();

        if (! $user) {
            $this->logAttempt($kerberos, 'unknown_user');
            $this->notifyAdminsUnknownUser($kerberos);

            return AuthResult::unknownUser($kerberos);
        }

        if (! $this->userHasRole($user)) {
            $this->logAttempt($kerberos, 'no_role', $user);

            return AuthResult::noRole($user, $kerberos);
        }

        $this->logAttempt($kerberos, 'success', $user);

        return AuthResult::success($user, $kerberos);
    }

    /**
     * Determine whether the user has sufficient access to log in.
     *
     * Three strategies are available (config kerberos.role_check.strategy):
     *
     *   'column'   — checks a single column with an operator (is_not_null / is_null)
     *   'relation' — checks that a relation is not empty
     *   'callable' — delegates to a class implementing UserAccessCheckInterface
     */
    protected function userHasRole(Authenticatable $user): bool
    {
        $strategy = config('kerberos.role_check.strategy', 'column');

        return match ($strategy) {
            'relation' => $user->{config('kerberos.role_check.relation', 'roles')}()->exists(),
            'callable' => $this->resolveCallable()->check($user),
            default    => $this->checkColumn($user),
        };
    }

    protected function checkColumn(Authenticatable $user): bool
    {
        $column   = config('kerberos.role_check.column', 'role_id');
        $operator = config('kerberos.role_check.operator', 'is_not_null');

        return $operator === 'is_null'
            ? is_null($user->{$column})
            : ! is_null($user->{$column});
    }

    protected function resolveCallable(): UserAccessCheckInterface
    {
        $class = config('kerberos.role_check.callable');

        if (empty($class)) {
            throw new \RuntimeException(
                'kerberos.role_check.callable must be set when strategy is \'callable\'.'
            );
        }

        if (! class_exists($class)) {
            throw new \RuntimeException("Kerberos callable class [{$class}] not found.");
        }

        $instance = app($class);

        if (! $instance instanceof UserAccessCheckInterface) {
            throw new \RuntimeException(
                "[{$class}] must implement ".UserAccessCheckInterface::class.'.'
            );
        }

        return $instance;
    }

    public function createAccessRequest(Authenticatable $user, string $kerberos, string $justification): AccessRequest
    {
        $accessRequest = AccessRequest::create([
            'user_id'       => $user->id,
            'kerberos'      => $kerberos,
            'justification' => $justification,
            'status'        => 'pending',
        ]);

        $this->notifyAdminsNewRequest($accessRequest);

        return $accessRequest;
    }

    public function logAttempt(string $kerberos, string $result, ?Authenticatable $user = null): KerberosAttempt
    {
        return KerberosAttempt::create([
            'kerberos'     => $kerberos,
            'result'       => $result,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'attempted_at' => now(),
        ]);
    }

    public function notifyAdminsUnknownUser(string $kerberos): void
    {
        if (config('kerberos.admin_notification_mode', 'immediate') === 'disabled') {
            return;
        }

        foreach ($this->getAdminUsers() as $admin) {
            $admin->notify(new UnknownKerberosAttemptNotification(
                kerberos:    $kerberos,
                ipAddress:   request()->ip() ?? '',
                userAgent:   request()->userAgent() ?? '',
                attemptedAt: now()
            ));
        }
    }

    public function notifyAdminsNewRequest(AccessRequest $accessRequest): void
    {
        if (config('kerberos.admin_notification_mode', 'immediate') === 'disabled') {
            return;
        }

        foreach ($this->getAdminUsers() as $admin) {
            $admin->notify(new NewAccessRequestNotification(accessRequest: $accessRequest));
        }
    }

    protected function getAdminUsers(): \Illuminate\Support\Collection
    {
        $userModel = Kerberos::userModel();

        return $userModel::whereHas('role', function ($query) {
            $query->where('name', 'Admin');
        })->get();
    }

    public function enableSimulation(string $kerberos): void
    {
        if (! config('kerberos.simulation_mode')) {
            throw new \RuntimeException('Kerberos simulation mode is not enabled in config.');
        }

        if (app()->environment('production')) {
            throw new \RuntimeException('Kerberos simulation is not allowed in production.');
        }

        session(['simulated_kerberos' => $kerberos]);
    }

    public function disableSimulation(): void
    {
        session()->forget('simulated_kerberos');
    }

    public function isSimulationActive(): bool
    {
        return config('kerberos.simulation_mode') && session()->has('simulated_kerberos');
    }

    public function getSimulatedKerberos(): ?string
    {
        return $this->isSimulationActive() ? session('simulated_kerberos') : null;
    }
}
