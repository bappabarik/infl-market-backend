<?php
declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Settings\SettingsInterface;
use App\Domain\User\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secret;
    private int $accessTtl;
    private int $refreshTtl;

    public function __construct(SettingsInterface $settings)
    {
        $jwt = $settings->get('jwt');
        $this->secret = $jwt['secret'];
        $this->accessTtl = $jwt['access_ttl'];
        $this->refreshTtl = $jwt['refresh_ttl'];
    }

    public function issueAccess(User $user): string
    {
        $now = time();
        return JWT::encode([
            'sub'   => $user->getId(),
            'uuid'  => $user->getUuid(),
            'email' => $user->getEmail(),
            'role'  => $user->getRole(),
            'iat'   => $now,
            'exp'   => $now + $this->accessTtl,
        ], $this->secret, 'HS256');
    }

    public function issueRefresh(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function decode(string $token): array
    {
        return (array)JWT::decode($token, new Key($this->secret, 'HS256'));
    }

    public function getRefreshTtl(): int
    {
        return $this->refreshTtl;
    }
}