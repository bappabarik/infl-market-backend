<?php
declare(strict_types=1);

namespace App\Application\Actions\Affiliate;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpNotFoundException;

class UpdateAffiliateAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private AffiliateRepository $affiliates,
        private RequestValidator $validator,
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

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'agency_name'                => v::optional(v::stringType()->length(2, 191)),
                'default_commission_percent' => v::optional(v::numericVal()->between(0, 100)),
            ]
        );

        $updated = $this->affiliates->update($affiliate->getId(), $data);

        return $this->respondWithData($updated->jsonSerialize());
    }
}