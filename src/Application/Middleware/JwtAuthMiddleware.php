<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Services\JwtService;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Throwable;

class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private JwtService $jwt,
        private UserRepository $users,
    ) {}

    public function process(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            throw new HttpUnauthorizedException($request, 'Missing or malformed Authorization header');
        }

        $token = $matches[1];

        try {
            $payload = $this->jwt->decode($token);
        } catch (Throwable $e) {
            throw new HttpUnauthorizedException($request, 'Invalid or expired token');
        }

        $userId = $payload['sub'] ?? null;
        if (!$userId) {
            throw new HttpUnauthorizedException($request, 'Invalid token payload');
        }

        // Load fresh user from DB — catches suspended/deleted accounts mid-session
        $user = $this->users->findById((int)$userId);
        if (!$user) {
            throw new HttpUnauthorizedException($request, 'User not found');
        }

        if (!$user->isActive()) {
            throw new HttpForbiddenException($request, 'Account not active');
        }

        // Attach the full user entity to the request for downstream actions
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}