<?php
declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Services\JwtService;
use App\Application\Validation\RequestValidator;
use App\Domain\Auth\RefreshTokenRepository;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;

class RefreshTokenAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private UserRepository $users,
        private RefreshTokenRepository $refreshTokens,
        private JwtService $jwt,
        private RequestValidator $validator,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'refresh_token' => v::notEmpty()->stringType(),
            ]
        );

        $oldHash = hash('sha256', $data['refresh_token']);

        // Look up the token — must exist, not be revoked, not be expired
        $row = $this->refreshTokens->findValid($oldHash);
        if (!$row) {
            throw new HttpUnauthorizedException($this->request, 'Invalid or expired refresh token');
        }

        $user = $this->users->findById((int)$row['user_id']);
        if (!$user) {
            // Token references a user that no longer exists — clean up
            $this->refreshTokens->revoke($oldHash);
            throw new HttpUnauthorizedException($this->request, 'User not found');
        }

        if (!$user->isActive()) {
            $this->refreshTokens->revoke($oldHash);
            throw new HttpForbiddenException($this->request, 'Account not active');
        }

        // Rotate: revoke the old token and issue a new pair
        $this->refreshTokens->revoke($oldHash);

        $newAccessToken  = $this->jwt->issueAccess($user);
        $newRefreshToken = $this->jwt->issueRefresh();

        $this->refreshTokens->store(
            $user->getId(),
            hash('sha256', $newRefreshToken),
            $this->jwt->getRefreshTtl(),
            $this->request->getHeaderLine('User-Agent') ?: null,
            $this->request->getServerParams()['REMOTE_ADDR'] ?? null,
        );

        $this->logger->info("Token refreshed for user: {$user->getEmail()}");

        return $this->respondWithData([
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'user' => $user->jsonSerialize(),
        ]);
    }
}