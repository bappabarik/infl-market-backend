<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Campaign\DealRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class SubmitDeliverableAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private DealRepository $deals,
        private InfluencerRepository $influencers,
        private RequestValidator $validator,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $dealId = (int)$this->resolveArg('id');

        $deal = $this->deals->findById($dealId);
        if (!$deal) {
            throw new HttpNotFoundException($this->request, 'Deal not found');
        }

        // Verify the influencer owns this deal
        $influencer = $this->influencers->findByUserId($user->getId());
        if (!$influencer || $influencer->getId() !== (int)$deal['influencer_id']) {
            throw new HttpForbiddenException($this->request, 'This deal is not assigned to you');
        }

        if (!in_array($deal['status'], ['accepted', 'in_progress'])) {
            throw new HttpBadRequestException($this->request, 'Deal must be accepted before submitting deliverables');
        }

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'post_url'  => v::optional(v::url()),
                'media_url' => v::optional(v::url()),
                'notes'     => v::optional(v::stringType()->length(1, 5000)),
            ]
        );

        $deliverable = $this->deals->addDeliverable($dealId, $data);

        // Update deal status to submitted
        $this->deals->updateStatus($dealId, 'submitted');

        return $this->respondWithData($deliverable, 201);
    }
}