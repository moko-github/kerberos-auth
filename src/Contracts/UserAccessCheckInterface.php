<?php

namespace MokoGithub\KerberosAuth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface UserAccessCheckInterface
{
    /**
     * Determine whether the given user is allowed to access the application.
     *
     * Return true  → user is authenticated (SUCCESS).
     * Return false → user has no role / access (NO_ROLE) and is redirected
     *                to the access-request form.
     */
    public function check(Authenticatable $user): bool;
}
