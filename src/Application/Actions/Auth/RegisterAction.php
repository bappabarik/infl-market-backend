<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Brand\BrandRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;

class RegisterAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private UserRepository $users,
        private BrandRepository $brands,
        private InfluencerRepository $influencers,
        private AffiliateRepository $affiliates,
        private RequestValidator $validator,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'email'     => v::notEmpty()->email()->length(null, 191),
                'password'  => v::notEmpty()->stringType()->length(8, 100)->regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/')
                    ->setTemplate('Password must contain uppercase, lowercase, number, and special character'),
                'full_name' => v::notEmpty()->stringType()->length(2, 150),
                'role'      => v::notEmpty()->in(['influencer', 'brand', 'affiliate']),
            ]
        );

        if ($this->users->emailExists($data['email'])) {
            throw new HttpBadRequestException($this->request, 'Email already registered');
        }

        $user = $this->users->create(
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
            $data['role'],
        );

        // ← CHANGE: auto-create role-specific profile
        if ($data['role'] === 'brand') {
            $this->brands->create(
                $user->getId(),
                $data['full_name'],  // use their name as brand name initially
                null,                // logo_url
                null,                // description
                null,                // website
                null,                // industry_id
            );
        }

        // ← CHANGE: auto-create role-specific profile
        if ($data['role'] === 'influencer') {
            $this->influencers->create($user->getId(), $data['full_name']);
        }

        if ($data['role'] === 'affiliate') {
            $this->affiliates->create($user->getId(), $data['full_name']);
        }

        $this->logger->info("User registered: {$user->getEmail()}");

        return $this->respondWithData([
            'message' => 'Account created',
            'user' => $user->jsonSerialize(),
        ], 201);
    }
}
