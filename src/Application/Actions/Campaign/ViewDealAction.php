<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Domain\Campaign\DealRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ViewDealAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private DealRepository $deals,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $dealId = (int)$this->resolveArg('id');
        $deal = $this->deals->findById($dealId);

        if (!$deal) {
            throw new HttpNotFoundException($this->request, 'Deal not found');
        }

        $deliverables = $this->deals->getDeliverables($dealId);

        return $this->respondWithData([
            'deal'         => $deal,
            'deliverables' => $deliverables,
        ]);
    }
}