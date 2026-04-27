<?php
declare(strict_types=1);

namespace App\Application\Actions\Affiliate;

use App\Application\Actions\Action;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class GetEarningsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private AffiliateRepository $affiliates,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $affiliate = $this->affiliates->findByUserId($user->getId());

        if (!$affiliate) {
            throw new HttpNotFoundException($this->request, 'Affiliate profile not found');
        }

        $earnings = $this->affiliates->getEarnings($affiliate->getId());

        return $this->respondWithData([
            'affiliate'    => $affiliate->jsonSerialize(),
            'earnings'     => $earnings,
        ]);
    }
}