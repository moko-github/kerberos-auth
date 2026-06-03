<?php

declare(strict_types=1);

use MokoGithub\KerberosAuth\DTOs\AuthResult;
use MokoGithub\KerberosAuth\Tests\Fixtures\User;

it('builds a success result', function () {
    $user = new User(['name' => 'A']);
    $result = AuthResult::success($user, 'a@krb');

    expect($result->status)->toBe(AuthResult::SUCCESS)
        ->and($result->isSuccess())->toBeTrue()
        ->and($result->user)->toBe($user)
        ->and($result->kerberos)->toBe('a@krb');
});

it('builds a no-kerberos result needing fallback', function () {
    $result = AuthResult::noKerberos();

    expect($result->status)->toBe(AuthResult::NO_KERBEROS)
        ->and($result->needsFallbackAuth())->toBeTrue()
        ->and($result->user)->toBeNull();
});

it('builds a no-role result needing access request', function () {
    $result = AuthResult::noRole(new User, 'a@krb');

    expect($result->needsAccessRequest())->toBeTrue()
        ->and($result->status)->toBe(AuthResult::NO_ROLE);
});

it('builds an unknown-user result that is blocked', function () {
    $result = AuthResult::unknownUser('ghost@krb');

    expect($result->isBlocked())->toBeTrue()
        ->and($result->kerberos)->toBe('ghost@krb');
});

it('rejects an invalid status', function () {
    new AuthResult(status: 'bogus');
})->throws(InvalidArgumentException::class);

it('returns a default message per status', function () {
    expect(AuthResult::noKerberos()->getMessage())->toContain('login form');
});
